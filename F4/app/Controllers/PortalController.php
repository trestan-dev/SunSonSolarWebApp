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
        if (!$this->canManageSchedule()) {
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
        if (!$this->canManageUsers()) {
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

    public function addCustomerStory()
    {
        if ($this->passwordChangeRequired()) {
            return redirect()->to('home');
        }
        if (!$this->canManageUsers()) {
            return $this->deny();
        }

        $customer = trim((string) $this->request->getPost('customer_display'));
        $service = trim((string) $this->request->getPost('service_name'));
        $quote = trim((string) $this->request->getPost('quote'));
        if ($customer === '' || $service === '' || $quote === '') {
            return $this->back('Add a customer name, service, and approved story.');
        }

        db_connect()->table('customer_stories')->insert([
            'customer_display' => $customer,
            'service_name' => $service,
            'quote' => $quote,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->back('Customer story saved.');
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
        if (session()->get('user_role') !== 'admin') {
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