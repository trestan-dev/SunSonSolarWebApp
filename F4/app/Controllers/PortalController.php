<?php

namespace App\Controllers;

class PortalController extends BaseController
{
    private array $departments = [
        'Administration', 'IT', 'Dispatch', 'Accounting', 'HR', 'Marketing', 'Sales', 'Customer Service',
    ];

    public function createEmployee()
    {
        if ($this->passwordChangeRequired()) {
            return redirect()->to('home');
        }
        if (!$this->canManageUsers()) {
            return $this->deny();
        }

        $data = $this->request->getPost();
        foreach (['first_name', 'last_name', 'middle_name', 'birthdate', 'gender', 'email', 'phone_number', 'address', 'username', 'department', 'job_title', 'password'] as $field) {
            $data[$field] = trim((string) ($data[$field] ?? ''));
            if ($data[$field] === '') {
                return $this->back('Complete every employee field.');
            }
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || strlen($data['password']) < 12 || !in_array($data['department'], $this->departments, true)) {
            return $this->back('Use a valid email, department, and temporary password of at least 12 characters.');
        }

        $db = db_connect();
        $db->table('users')->insert([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'middle_name' => $data['middle_name'],
            'birthdate' => $data['birthdate'],
            'gender' => $data['gender'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'address' => $data['address'],
            'username' => $data['username'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => 'employee',
            'department' => $data['department'],
            'job_title' => $data['job_title'],
            'is_active' => true,
            'must_change_password' => true,
        ]);

        return $this->back('Employee account created. Give the employee their temporary password and ask them to change it at first login.');
    }

    public function toggleUser(int $userId)
    {
        if ($this->passwordChangeRequired()) {
            return redirect()->to('home');
        }
        if (!$this->canManageUsers()) {
            return $this->deny();
        }

        $db = db_connect();
        $user = $db->table('users')->where('id', $userId)->get()->getRowArray();
        if (!$user || ($user['role'] ?? '') !== 'employee' || $userId === (int) session()->get('user_id')) {
            return $this->back('Only other employee accounts can be activated or deactivated here.');
        }

        $active = !(bool) ($user['is_active'] ?? true);
        $db->table('users')->where('id', $userId)->update(['is_active' => $active]);

        return $this->back($active ? 'Employee account activated.' : 'Employee account deactivated.');
    }

    public function updateUser(int $userId)
    {
        if ($this->passwordChangeRequired()) {
            return redirect()->to('home');
        }
        if (!$this->canManageUsers()) {
            return $this->deny();
        }

        $db = db_connect();
        $user = $db->table('users')->where('id', $userId)->get()->getRowArray();
        if (!$user || ($user['role'] ?? '') !== 'employee') {
            return $this->back('Only employee accounts can be edited here.');
        }

        $fields = ['first_name', 'last_name', 'middle_name', 'birthdate', 'gender', 'email', 'phone_number', 'address', 'username', 'department', 'job_title'];
        $updates = [];
        foreach ($fields as $field) {
            $updates[$field] = trim((string) $this->request->getPost($field));
        }
        if (!filter_var($updates['email'], FILTER_VALIDATE_EMAIL)
            || !in_array($updates['department'], $this->departments, true)
            || $updates['job_title'] === '') {
            return $this->back('Enter a valid email address and department.');
        }

        $db->table('users')->where('id', $userId)->update($updates);
        return $this->back('User details updated.');
    }

    public function createBooking()
    {
        if ($this->passwordChangeRequired()) {
            return redirect()->to('home');
        }
        if (session()->get('user_role') !== 'customer') {
            return $this->deny();
        }

        $serviceId = (int) $this->request->getPost('service_id');
        $scheduledAt = trim((string) $this->request->getPost('scheduled_at'));
        $date = strtotime($scheduledAt);
        $db = db_connect();
        $service = $db->table('services')->where('id', $serviceId)->where('is_active', 1)->get()->getRowArray();

        if (!$service || !$date || $date <= time()) {
            return $this->back('Choose an available service and a future appointment time.');
        }

        $db->table('bookings')->insert([
            'customer_id' => session()->get('user_id'),
            'service_id' => $serviceId,
            'scheduled_at' => date('Y-m-d H:i:s', $date),
            'status' => 'pending',
            'customer_notes' => trim((string) $this->request->getPost('customer_notes')),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->notify((int) session()->get('user_id'), 'Booking request received', 'Your ' . $service['name'] . ' request is waiting for confirmation.', 'update');

        return $this->back('Your service request was sent.');
    }

    public function createProductOrder()
    {
        if ($this->passwordChangeRequired()) {
            return redirect()->to('home');
        }
        if (session()->get('user_role') !== 'customer') {
            return $this->deny();
        }

        $productId = (int) $this->request->getPost('product_id');
        $quantity = (int) $this->request->getPost('quantity');
        $recipientName = trim((string) $this->request->getPost('recipient_name'));
        $recipientPhone = trim((string) $this->request->getPost('recipient_phone'));
        $deliveryAddress = trim((string) $this->request->getPost('delivery_address'));
        $deliveryNotes = trim((string) $this->request->getPost('delivery_notes'));

        if ($productId < 1 || $quantity < 1 || $quantity > 50 || $recipientName === '' || $recipientPhone === '' || $deliveryAddress === '') {
            return $this->back('Enter a valid quantity and complete the delivery contact and address.');
        }

        $db = db_connect();
        $product = $db->table('products')->where('id', $productId)->where('is_active', 1)->get()->getRowArray();
        if (!$product) {
            return $this->back('That product is no longer available.');
        }

        $unitPrice = (float) $product['price'];
        $db->transStart();
        $db->table('orders')->insert([
            'customer_id' => (int) session()->get('user_id'),
            'recipient_name' => $recipientName,
            'recipient_phone' => $recipientPhone,
            'delivery_address' => $deliveryAddress,
            'delivery_notes' => $deliveryNotes,
            'status' => 'pending',
            'payment_status' => 'to_be_confirmed',
            'total_amount' => $unitPrice * $quantity,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $orderId = (int) $db->insertID();
        $db->table('order_items')->insert([
            'order_id' => $orderId,
            'product_id' => (int) $product['id'],
            'product_name' => (string) $product['name'],
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $unitPrice * $quantity,
        ]);
        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->back('We could not submit the product request. Please try again.');
        }

        $this->notify((int) session()->get('user_id'), 'Product request received', 'Your request for ' . $product['name'] . ' is waiting for availability and delivery confirmation.', 'update');
        return $this->back('Product request submitted. The team will confirm availability, final pricing, payment, and delivery.');
    }

    public function updateProductOrder(int $orderId)
    {
        if ($this->passwordChangeRequired()) {
            return redirect()->to('home');
        }
        if (session()->get('user_role') !== 'admin') {
            return $this->deny();
        }

        $status = trim((string) $this->request->getPost('status'));
        $paymentStatus = trim((string) $this->request->getPost('payment_status'));
        $deliveryStaffId = (int) $this->request->getPost('delivery_staff_id');
        $trackingCode = trim((string) $this->request->getPost('tracking_code'));
        $allowedStatuses = ['pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'];
        $allowedPayments = ['to_be_confirmed', 'unpaid', 'paid'];
        if (!in_array($status, $allowedStatuses, true) || !in_array($paymentStatus, $allowedPayments, true)) {
            return $this->back('Choose a valid order and payment status.');
        }

        $db = db_connect();
        $order = $db->table('orders')->where('id', $orderId)->get()->getRowArray();
        if (!$order) {
            return $this->back('Product order not found.');
        }
        if ($deliveryStaffId > 0) {
            if (!in_array($status, ['confirmed', 'preparing', 'out_for_delivery', 'delivered'], true)) {
                return $this->back('Assign a delivery employee only after confirming the order.');
            }
            $staff = $db->table('users')->where('id', $deliveryStaffId)->where('role', 'employee')->where('department', 'Dispatch')->where('is_active', 1)->get()->getRowArray();
            if (!$staff) {
                return $this->back('Choose an active Dispatch employee for delivery.');
            }
        }

        $db->table('orders')->where('id', $orderId)->update([
            'status' => $status,
            'payment_status' => $paymentStatus,
            'delivery_staff_id' => $deliveryStaffId ?: null,
            'tracking_code' => $trackingCode !== '' ? $trackingCode : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        if ($deliveryStaffId > 0 && $deliveryStaffId !== (int) $order['delivery_staff_id']) {
            $this->notify($deliveryStaffId, 'Product delivery assigned', 'A product order has been assigned to you for delivery.', 'update');
        }
        $this->notify((int) $order['customer_id'], 'Product order updated', 'Your product order is now ' . str_replace('_', ' ', $status) . '.', 'update');

        return $this->back('Product order updated.');
    }

    public function updateDeliveryOrder(int $orderId)
    {
        if ($this->passwordChangeRequired()) {
            return redirect()->to('home');
        }
        if (session()->get('user_role') !== 'employee' || strtolower((string) session()->get('user_department')) !== 'dispatch') {
            return $this->deny();
        }

        $status = trim((string) $this->request->getPost('status'));
        $trackingCode = trim((string) $this->request->getPost('tracking_code'));
        if (!in_array($status, ['out_for_delivery', 'delivered'], true)) {
            return $this->back('Choose a valid delivery update.');
        }

        $db = db_connect();
        $order = $db->table('orders')->where('id', $orderId)->get()->getRowArray();
        $employeeId = (int) session()->get('user_id');
        if (!$order || ($order['delivery_staff_id'] !== null && (int) $order['delivery_staff_id'] !== $employeeId)) {
            return $this->back('This delivery is not available to your account.');
        }
        if (!in_array($order['status'], ['confirmed', 'preparing', 'out_for_delivery'], true)) {
            return $this->back('Only confirmed orders that are being prepared or delivered can be updated here.');
        }
        if ($order['status'] === 'preparing' && $status !== 'out_for_delivery') {
            return $this->back('Mark the order out for delivery before marking it delivered.');
        }
        if ($order['status'] === 'out_for_delivery' && $status !== 'delivered') {
            return $this->back('This order is already out for delivery; mark it delivered when complete.');
        }
        if ($status === 'delivered' && $order['status'] !== 'out_for_delivery') {
            return $this->back('Mark the order out for delivery before marking it delivered.');
        }

        $db->table('orders')->where('id', $orderId)->update([
            'delivery_staff_id' => $employeeId,
            'status' => $status,
            'tracking_code' => $trackingCode !== '' ? $trackingCode : ($order['tracking_code'] ?? null),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->notify((int) $order['customer_id'], 'Delivery update', 'Your product order is now ' . str_replace('_', ' ', $status) . '.', 'update');

        return $this->back('Delivery status updated.');
    }

    public function updateSalesOrder(int $orderId)
    {
        if ($this->passwordChangeRequired()) {
            return redirect()->to('home');
        }
        if (session()->get('user_role') !== 'employee' || strtolower((string) session()->get('user_department')) !== 'sales') {
            return $this->deny();
        }

        $decision = trim((string) $this->request->getPost('decision'));
        $amount = $this->request->getPost('total_amount');
        if (!in_array($decision, ['confirm', 'cancel'], true) || ($decision === 'confirm' && (!is_numeric($amount) || (float) $amount <= 0))) {
            return $this->back('Enter a final amount above zero and choose whether to confirm or cancel the request.');
        }

        $db = db_connect();
        $order = $db->table('orders')->where('id', $orderId)->where('status', 'pending')->get()->getRowArray();
        if (!$order) {
            return $this->back('This product request has already been handled or cannot be found.');
        }

        $confirmed = $decision === 'confirm';
        $db->table('orders')->where('id', $orderId)->update([
            'status' => $confirmed ? 'confirmed' : 'cancelled',
            'payment_status' => $confirmed ? 'unpaid' : 'to_be_confirmed',
            'total_amount' => $confirmed ? (float) $amount : (float) $order['total_amount'],
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->notify((int) $order['customer_id'], 'Product quote updated', $confirmed
            ? 'Your product request has been confirmed. The team will prepare payment and delivery arrangements.'
            : 'The team could not accept your product request. Please contact support if you have questions.', 'update');

        return $this->back($confirmed ? 'Quote confirmed; the order is ready for fulfillment.' : 'Product request cancelled.');
    }

    public function cancelBooking(int $bookingId)
    {
        if ($this->passwordChangeRequired()) {
            return redirect()->to('home');
        }
        if (session()->get('user_role') !== 'customer') {
            return $this->deny();
        }

        $db = db_connect();
        $booking = $db->table('bookings')->where('id', $bookingId)->where('customer_id', session()->get('user_id'))->get()->getRowArray();
        if (!$booking || !in_array($booking['status'], ['pending', 'approved'], true)) {
            return $this->back('This booking can no longer be cancelled online.');
        }

        $db->table('bookings')->where('id', $bookingId)->update(['status' => 'cancelled']);
        return $this->back('Booking cancelled.');
    }

    public function updateBooking(int $bookingId)
    {
        if ($this->passwordChangeRequired()) {
            return redirect()->to('home');
        }
        if (!$this->canManageSchedule()) {
            return $this->deny();
        }

        $status = trim((string) $this->request->getPost('status'));
        $allowedStatuses = ['pending', 'approved', 'declined', 'scheduled', 'in_progress', 'completed', 'cancelled'];
        if (!in_array($status, $allowedStatuses, true)) {
            return $this->back('Choose a valid booking status.');
        }

        $db = db_connect();
        $booking = $db->table('bookings')->where('id', $bookingId)->get()->getRowArray();
        if (!$booking) {
            return $this->back('Booking not found.');
        }

        $technicianId = (int) $this->request->getPost('technician_id');
        $technician = null;
        if ($technicianId > 0) {
            $technician = $db->table('users')->where('id', $technicianId)->where('role', 'employee')->whereIn('job_title', ['Technician', 'Head Technician'])->where('is_active', 1)->get()->getRowArray();
            if (!$technician) {
                return $this->back('Choose an active technician.');
            }
        }

        $db->table('bookings')->where('id', $bookingId)->update([
            'status' => $status,
            'technician_id' => $technicianId ?: null,
            'scheduled_at' => date('Y-m-d H:i:s', strtotime((string) $this->request->getPost('scheduled_at')) ?: strtotime($booking['scheduled_at'])),
            'admin_notes' => trim((string) $this->request->getPost('admin_notes')),
        ]);
        $this->notify((int) $booking['customer_id'], 'Schedule updated', 'Your service request is now ' . str_replace('_', ' ', $status) . '.', 'reminder');
        if ($technician) {
            $this->notify((int) $technician['id'], 'Job assigned', 'A service booking has been assigned to you.', 'update');
        }

        return $this->back('Booking updated.');
    }

    public function updateProfile()
    {
        if ($this->passwordChangeRequired()) {
            return redirect()->to('home');
        }
        $userId = (int) session()->get('user_id');
        if (!$userId) {
            return $this->deny();
        }

        $fields = ['first_name', 'last_name', 'middle_name', 'birthdate', 'gender', 'email', 'phone_number', 'address'];
        $updates = [];
        foreach ($fields as $field) {
            $updates[$field] = trim((string) $this->request->getPost($field));
        }
        if (!filter_var($updates['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->back('Enter a valid email address.');
        }

        $password = (string) $this->request->getPost('new_password');
        if ($password !== '') {
            if (strlen($password) < 12) {
                return $this->back('New passwords must contain at least 12 characters.');
            }
            $currentPassword = (string) $this->request->getPost('current_password');
            $currentUser = db_connect()->table('users')->where('id', $userId)->get()->getRowArray();
            if (!$currentUser || !password_verify($currentPassword, (string) $currentUser['password_hash'])) {
                return $this->back('Enter your current password to change it.');
            }
            $updates['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            $updates['must_change_password'] = false;
            session()->set('must_change_password', false);
        }

        db_connect()->table('users')->where('id', $userId)->update($updates);
        session()->set('user_name', trim($updates['first_name'] . ' ' . $updates['last_name']));
        return $this->back('Profile updated.');
    }

    public function changePassword()
    {
        $userId = (int) session()->get('user_id');
        $currentPassword = (string) $this->request->getPost('current_password');
        $newPassword = (string) $this->request->getPost('new_password');
        $db = db_connect();
        $user = $db->table('users')->where('id', $userId)->get()->getRowArray();

        if (!$user || !password_verify($currentPassword, (string) $user['password_hash'])) {
            return redirect()->to('home')->with('error', 'Current password is incorrect.');
        }
        if (strlen($newPassword) < 12) {
            return redirect()->to('home')->with('error', 'New passwords must contain at least 12 characters.');
        }

        $db->table('users')->where('id', $userId)->update([
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            'must_change_password' => false,
        ]);
        session()->set('must_change_password', false);

        return redirect()->to('home')->with('message', 'Password changed. Welcome to your Sun Son Solar workspace.');
    }

    public function updatePreferences()
    {
        if ($this->passwordChangeRequired()) {
            return redirect()->to('home');
        }
        if (session()->get('user_role') !== 'customer') {
            return $this->deny();
        }

        $userId = (int) session()->get('user_id');
        $db = db_connect();
        $preferences = [
            'service_notifications' => $this->request->getPost('service_notifications') ? 1 : 0,
            'promotional_notifications' => $this->request->getPost('promotional_notifications') ? 1 : 0,
        ];
        $existing = $db->table('customer_preferences')->where('user_id', $userId)->countAllResults();
        if ($existing) {
            $db->table('customer_preferences')->where('user_id', $userId)->update($preferences);
        } else {
            $db->table('customer_preferences')->insert(['user_id' => $userId] + $preferences);
        }

        return $this->back('Notification preferences saved.');
    }

    public function createTicket()
    {
        if ($this->passwordChangeRequired()) {
            return redirect()->to('home');
        }
        if (session()->get('user_role') !== 'customer') {
            return $this->deny();
        }

        $subject = trim((string) $this->request->getPost('subject'));
        $message = trim((string) $this->request->getPost('message'));
        if ($subject === '' || $message === '') {
            return $this->back('Add a subject and message to contact support.');
        }

        db_connect()->table('support_tickets')->insert([
            'customer_id' => session()->get('user_id'),
            'subject' => $subject,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->back('Your support request was sent.');
    }

    public function replyTicket(int $ticketId)
    {
        if ($this->passwordChangeRequired()) {
            return redirect()->to('home');
        }
        if (!$this->canReplyToSupport()) {
            return $this->deny();
        }

        $reply = trim((string) $this->request->getPost('admin_reply'));
        if ($reply === '') {
            return $this->back('Write a reply before submitting.');
        }

        $db = db_connect();
        $ticket = $db->table('support_tickets')->where('id', $ticketId)->get()->getRowArray();
        if (!$ticket) {
            return $this->back('Support request not found.');
        }
        $db->table('support_tickets')->where('id', $ticketId)->update(['admin_reply' => $reply, 'status' => 'answered']);
        $this->notify((int) $ticket['customer_id'], 'Support replied', $reply, 'update');
        return $this->back('Support reply sent.');
    }

    public function announce()
    {
        if ($this->passwordChangeRequired()) {
            return redirect()->to('home');
        }
        if (!$this->canManageAnnouncements()) {
            return $this->deny();
        }

        $audience = trim((string) $this->request->getPost('audience'));
        $type = trim((string) $this->request->getPost('type'));
        $title = trim((string) $this->request->getPost('title'));
        $message = trim((string) $this->request->getPost('message'));
        if (!in_array($audience, ['customers', 'employees', 'all', 'public'], true) || !in_array($type, ['reminder', 'promotion', 'update', 'safety'], true) || $title === '' || $message === '') {
            return $this->back('Complete the announcement details.');
        }

        db_connect()->table('notifications')->insert([
            'audience' => $audience,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->back('Announcement published.');
    }

    public function submitCustomerFeedback()
    {
        if ($this->passwordChangeRequired()) {
            return redirect()->to('home');
        }
        if (session()->get('user_role') !== 'customer') {
            return $this->deny();
        }

        $bookingId = (int) $this->request->getPost('booking_id');
        $quote = trim((string) $this->request->getPost('quote'));
        if ($bookingId < 1 || mb_strlen($quote) < 10 || mb_strlen($quote) > 2000) {
            return $this->back('Choose a completed service and write feedback between 10 and 2,000 characters.');
        }

        $db = db_connect();
        $booking = $db->table('bookings b')
            ->select('b.id, b.customer_id, b.status, s.name AS service_name')
            ->join('services s', 's.id = b.service_id', 'left')
            ->where('b.id', $bookingId)
            ->where('b.customer_id', (int) session()->get('user_id'))
            ->where('b.status', 'completed')
            ->get()->getRowArray();
        if (!$booking) {
            return $this->back('Feedback can only be submitted for your completed services.');
        }
        if ($db->table('customer_stories')->where('booking_id', $bookingId)->countAllResults() > 0) {
            return $this->back('Feedback has already been submitted for this service.');
        }

        $profile = $db->table('users')->select('first_name, last_name')->where('id', session()->get('user_id'))->get()->getRowArray();
        $displayName = trim((string) ($profile['first_name'] ?? 'Customer'));
        if (!empty($profile['last_name'])) {
            $displayName .= ' ' . mb_substr(trim((string) $profile['last_name']), 0, 1) . '.';
        }

        $db->table('customer_stories')->insert([
            'customer_id' => (int) session()->get('user_id'),
            'booking_id' => $bookingId,
            'customer_display' => $displayName,
            'service_name' => (string) ($booking['service_name'] ?? 'Solar service'),
            'quote' => $quote,
            'moderation_status' => 'pending',
            'is_active' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->back('Thank you. Your feedback was sent to the team for review before publication.');
    }

    public function moderateCustomerStory(int $storyId)
    {
        if ($this->passwordChangeRequired()) {
            return redirect()->to('home');
        }
        if (session()->get('user_role') !== 'admin') {
            return $this->deny();
        }

        $decision = trim((string) $this->request->getPost('decision'));
        if (!in_array($decision, ['approve', 'reject'], true)) {
            return $this->back('Choose approve or reject for this feedback.');
        }

        $db = db_connect();
        $story = $db->table('customer_stories')->where('id', $storyId)->get()->getRowArray();
        if (!$story || empty($story['customer_id'])) {
            return $this->back('Customer feedback submission not found.');
        }

        $approved = $decision === 'approve';
        $db->table('customer_stories')->where('id', $storyId)->update([
            'moderation_status' => $approved ? 'approved' : 'rejected',
            'is_active' => $approved ? 1 : 0,
        ]);

        return $this->back($approved ? 'Customer feedback approved and published.' : 'Customer feedback rejected.');
    }

    public function toggleCustomerStory(int $storyId)
    {
        if ($this->passwordChangeRequired()) {
            return redirect()->to('home');
        }
        if (!$this->canManageUsers()) {
            return $this->deny();
        }

        $db = db_connect();
        $story = $db->table('customer_stories')->where('id', $storyId)->get()->getRowArray();
        if (!$story) {
            return $this->back('Customer story not found.');
        }

        if (($story['moderation_status'] ?? 'approved') !== 'approved') {
            return $this->back('Only approved customer feedback can be published or hidden.');
        }
        $db->table('customer_stories')->where('id', $storyId)->update(['is_active' => !(bool) $story['is_active']]);
        return $this->back('Customer story visibility updated.');
    }

    public function updateService(int $serviceId)
    {
        if ($this->passwordChangeRequired()) {
            return redirect()->to('home');
        }
        if (!$this->canManageUsers()) {
            return $this->deny();
        }

        $name = trim((string) $this->request->getPost('name'));
        $category = trim((string) $this->request->getPost('category'));
        $availability = trim((string) $this->request->getPost('availability'));
        $price = (float) $this->request->getPost('price');
        if ($name === '' || $category === '' || $availability === '' || $price < 0) {
            return $this->back('Enter a valid name, category, price, and availability.');
        }

        db_connect()->table('services')->where('id', $serviceId)->update([
            'name' => $name,
            'category' => $category,
            'price' => $price,
            'availability' => $availability,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ]);
        return $this->back('Service settings saved.');
    }

    public function recordPayment()
    {
        if ($this->passwordChangeRequired()) {
            return redirect()->to('home');
        }
        if (session()->get('user_role') !== 'admin'
            && !(session()->get('user_role') === 'employee' && strtolower((string) session()->get('user_department')) === 'accounting')) {
            return $this->deny();
        }

        $bookingId = (int) $this->request->getPost('booking_id');
        $amount = (float) $this->request->getPost('amount');
        $status = trim((string) $this->request->getPost('status'));
        if ($amount < 0 || !in_array($status, ['pending', 'paid'], true)) {
            return $this->back('Enter a valid amount and payment status.');
        }

        $db = db_connect();
        $booking = $db->table('bookings')->where('id', $bookingId)->get()->getRowArray();
        if (!$booking) {
            return $this->back('Choose a valid booking for this payment.');
        }

        $payment = [
            'booking_id' => $bookingId,
            'customer_id' => $booking['customer_id'],
            'amount' => $amount,
            'status' => $status,
            'receipt_number' => $status === 'paid' ? 'SSS-' . strtoupper(bin2hex(random_bytes(4))) : null,
            'paid_at' => $status === 'paid' ? date('Y-m-d H:i:s') : null,
        ];
        $existing = $db->table('payments')->where('booking_id', $bookingId)->get()->getRowArray();
        if ($existing) {
            $db->table('payments')->where('id', $existing['id'])->update($payment);
        } else {
            $db->table('payments')->insert($payment);
        }

        if ($status === 'paid') {
            $this->notify((int) $booking['customer_id'], 'Payment receipt ready', 'A payment receipt has been added to your service history.', 'update');
        }

        return $this->back('Payment record saved.');
    }

    public function checkIn()
    {
        if ($this->passwordChangeRequired()) {
            return redirect()->to('home');
        }
        if (session()->get('user_role') !== 'employee') {
            return $this->deny();
        }

        $bookingId = (int) $this->request->getPost('booking_id');
        $db = db_connect();
        $booking = $db->table('bookings')->where('id', $bookingId)->where('technician_id', session()->get('user_id'))->get()->getRowArray();
        if (!$booking) {
            return $this->back('Check-in is available only for jobs assigned to you.');
        }

        $latitude = $this->request->getPost('latitude');
        $longitude = $this->request->getPost('longitude');
        if (!is_numeric($latitude) || !is_numeric($longitude) || abs((float) $latitude) > 90 || abs((float) $longitude) > 180) {
            return $this->back('Location is required to check in. Allow location access and try again.');
        }

        $db->table('attendance_checkins')->insert([
            'employee_id' => session()->get('user_id'),
            'booking_id' => $bookingId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'checked_in_at' => date('Y-m-d H:i:s'),
        ]);
        $db->table('bookings')->where('id', $bookingId)->update(['status' => 'in_progress']);
        return $this->back('Site check-in recorded.');
    }

    public function submitReport()
    {
        if ($this->passwordChangeRequired()) {
            return redirect()->to('home');
        }
        if (session()->get('user_role') !== 'employee') {
            return $this->deny();
        }

        $bookingId = (int) $this->request->getPost('booking_id');
        $issue = trim((string) $this->request->getPost('issue'));
        $details = trim((string) $this->request->getPost('details'));
        $db = db_connect();
        $booking = $db->table('bookings')->where('id', $bookingId)->where('technician_id', session()->get('user_id'))->get()->getRowArray();
        if (!$booking || $issue === '' || $details === '') {
            return $this->back('Choose an assigned job and describe the issue.');
        }

        $db->table('service_reports')->insert([
            'booking_id' => $bookingId,
            'employee_id' => session()->get('user_id'),
            'issue' => $issue,
            'details' => $details,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->back('Service report sent to the admin team.');
    }

    private function canManageUsers(): bool
    {
        return session()->get('user_role') === 'admin'
            || (session()->get('user_role') === 'employee' && strtolower((string) session()->get('user_department')) === 'hr');
    }

    private function passwordChangeRequired(): bool
    {
        return (bool) session()->get('must_change_password');
    }

    private function canManageSchedule(): bool
    {
        return session()->get('user_role') === 'admin'
            || (session()->get('user_role') === 'employee' && strtolower((string) session()->get('user_department')) === 'dispatch');
    }

    private function canReplyToSupport(): bool
    {
        return session()->get('user_role') === 'admin'
            || (session()->get('user_role') === 'employee' && in_array(strtolower((string) session()->get('user_department')), ['customer service', 'sales'], true));
    }

    private function canManageAnnouncements(): bool
    {
        return session()->get('user_role') === 'admin'
            || (session()->get('user_role') === 'employee' && in_array(strtolower((string) session()->get('user_department')), ['hr', 'marketing'], true));
    }

    private function deny()
    {
        return redirect()->to('home')->with('error', 'You do not have permission to do that.');
    }

    private function back(string $message)
    {
        return redirect()->to('home')->with('message', $message);
    }

    private function notify(int $userId, string $title, string $message, string $type): void
    {
        db_connect()->table('notifications')->insert([
            'user_id' => $userId,
            'audience' => 'direct',
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}