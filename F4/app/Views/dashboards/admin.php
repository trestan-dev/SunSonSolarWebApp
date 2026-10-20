<?php
$employeeCount = count(array_filter($users, static fn ($user) => $user['role'] === 'employee'));
$customerCount = count(array_filter($users, static fn ($user) => $user['role'] === 'customer'));
$activeUsers = count(array_filter($users, static fn ($user) => (bool) $user['is_active']));
$maxMonthly = max([1, ...array_map(static fn ($row) => (int) $row['total'], $monthly)]);
$section = (string) ($admin_section ?? 'overview');
$sectionTitles = [
    'overview' => 'Overview',
    'schedule' => 'Schedule',
    'users' => 'User management',
    'services' => 'Services & pricing',
    'reports' => 'Reports',
    'payments' => 'Payments',
    'orders' => 'Product orders',
    'attendance' => 'Attendance',
    'stories' => 'Customer stories',
    'announcements' => 'Announcements',
    'support' => 'Support',
    'field-reports' => 'Field reports',
];
$calendarDate = new DateTimeImmutable($calendar_month . '-01');
$calendarStart = $calendarDate->modify('monday this week');
$calendarEvents = [];
foreach ($bookings as $booking) {
    $calendarEvents[date('Y-m-d', strtotime($booking['scheduled_at']))][] = $booking;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operations | Sun Son Solar</title><link rel="stylesheet" href="<?= base_url('assets/css/portal.css?v=20261020-gender') ?>">
</head>
<body class="portal-page">
    <header class="portal-header"><a class="brand" href="<?= site_url('/') ?>"><img src="<?= base_url('assets/logo-sss.png') ?>" alt=""><span>Sun Son <b>Solar</b></span></a><div class="portal-header-actions"><span>Administration</span><div class="portal-user"><span class="avatar">SS</span><span><?= esc($name) ?></span></div><a href="<?= site_url('logout') ?>">Log out</a></div></header>
    <div class="portal-layout">
        <aside class="portal-sidebar"><p>Operations</p><nav><?php foreach ($sectionTitles as $sectionKey => $sectionTitle): ?><a class="<?= $section === $sectionKey ? 'active' : '' ?>" href="<?= site_url('admin/' . $sectionKey) ?>"><?= esc($sectionTitle) ?></a><?php endforeach ?></nav></aside>
        <main class="portal-main">
            <?php if (!empty($message)): ?><div class="flash-message"><?= esc($message) ?></div><?php endif ?>
            <?php if (!empty($error)): ?><div class="flash-message flash-error"><?= esc($error) ?></div><?php endif ?>
            <div class="portal-heading"><div><p class="eyebrow">Sun Son Solar · Operations / <?= esc($sectionTitles[$section]) ?></p><h1><?= $section === 'overview' ? 'Good day, ' . esc($name) . '.' : esc($sectionTitles[$section]) ?></h1><p><?= $section === 'overview' ? 'Bookings, people, and service operations at a glance.' : 'Manage Sun Son Solar operations from this screen.' ?></p></div><?php if ($section === 'overview'): ?><a class="button button-dark" href="<?= site_url('admin/schedule') ?>">Open schedule <span aria-hidden="true">↗</span></a><?php endif ?></div>
            <?php if ($section === 'overview'): ?>
            <div class="stat-grid"><div class="stat-item"><span>Customers</span><strong><?= $customerCount ?></strong></div><div class="stat-item"><span>Employees</span><strong><?= $employeeCount ?></strong></div><div class="stat-item"><span>Active accounts</span><strong><?= $activeUsers ?></strong></div><div class="stat-item"><span>Paid revenue</span><strong class="currency">₱<?= number_format((float) $revenue, 0) ?></strong></div></div>
            <?php endif ?>

            <div class="portal-grid">
                <?php if ($section === 'overview'): ?>
                    <section class="portal-panel full-panel"><div class="panel-heading"><h2>Operations at a glance</h2><span><?= count($bookings) ?> service bookings · <?= (int) $open_tickets ?> open support requests</span></div><div class="portal-shortcuts"><a href="<?= site_url('admin/schedule') ?>">Review service schedule <span aria-hidden="true">↗</span></a><a href="<?= site_url('admin/orders') ?>">Manage product requests & deliveries <span aria-hidden="true">↗</span></a><a href="<?= site_url('admin/users') ?>">Manage user accounts <span aria-hidden="true">↗</span></a><a href="<?= site_url('admin/reports') ?>">View reports <span aria-hidden="true">↗</span></a><a href="<?= site_url('admin/support') ?>">Open customer support <span aria-hidden="true">↗</span></a></div></section>
                <?php endif ?>
                <?php if ($section === 'schedule'): ?>
                <section class="portal-panel full-panel" id="schedule">
                    <div class="panel-heading"><h2>Schedule overview</h2><span><?= count($bookings) ?> bookings</span></div>
                    <div class="calendar-toolbar"><a href="<?= site_url('admin/schedule?month=' . $calendarDate->modify('-1 month')->format('Y-m')) ?>" aria-label="Previous month">←</a><strong><?= esc($calendarDate->format('F Y')) ?></strong><a href="<?= site_url('admin/schedule?month=' . $calendarDate->modify('+1 month')->format('Y-m')) ?>" aria-label="Next month">→</a></div>
                    <div class="calendar-grid"><div class="calendar-weekday">Mon</div><div class="calendar-weekday">Tue</div><div class="calendar-weekday">Wed</div><div class="calendar-weekday">Thu</div><div class="calendar-weekday">Fri</div><div class="calendar-weekday">Sat</div><div class="calendar-weekday">Sun</div>
                        <?php for ($day = 0; $day < 42; $day++): ?><?php $calendarDay = $calendarStart->modify('+' . $day . ' days'); $dayKey = $calendarDay->format('Y-m-d'); ?>
                            <div class="calendar-day <?= $calendarDay->format('Y-m') !== $calendar_month ? 'outside-month' : '' ?>"><span class="calendar-date"><?= $calendarDay->format('j') ?></span><?php foreach (array_slice($calendarEvents[$dayKey] ?? [], 0, 2) as $event): ?><span class="calendar-event" title="<?= esc(($event['service_name'] ?? 'Service') . ' · ' . $event['customer_first'] . ' ' . $event['customer_last']) ?>"><?= esc(date('g:i A', strtotime($event['scheduled_at']))) ?> · <?= esc($event['customer_first']) ?></span><?php endforeach ?><?php if (count($calendarEvents[$dayKey] ?? []) > 2): ?><span class="calendar-more">+<?= count($calendarEvents[$dayKey]) - 2 ?> more</span><?php endif ?></div>
                        <?php endfor ?>
                    </div>
                    <?php if ($bookings): ?><div class="table-wrap"><table class="portal-table"><thead><tr><th>Customer & site</th><th>Service</th><th>Appointment</th><th>Technician</th><th>Status & actions</th></tr></thead><tbody>
                        <?php foreach ($bookings as $booking): ?><tr><td><?= esc(trim($booking['customer_first'] . ' ' . $booking['customer_last'])) ?><small><?= esc($booking['customer_phone']) ?> · <?= esc($booking['customer_address']) ?></small></td><td><?= esc($booking['service_name'] ?? 'Service') ?><small><?= esc($booking['customer_notes'] ?? '') ?></small></td><td><?= esc(date('M j, Y · g:i A', strtotime($booking['scheduled_at']))) ?></td><td><?= esc(trim(($booking['technician_first'] ?? '') . ' ' . ($booking['technician_last'] ?? '')) ?: 'Unassigned') ?></td><td><form class="portal-form" method="post" action="<?= site_url('portal/bookings/' . $booking['id'] . '/update') ?>" style="min-width:190px;gap:6px"><?= csrf_field() ?><select name="status" aria-label="Booking status"><option value="pending" <?= $booking['status'] === 'pending' ? 'selected' : '' ?>>Pending</option><option value="approved" <?= $booking['status'] === 'approved' ? 'selected' : '' ?>>Approve</option><option value="declined" <?= $booking['status'] === 'declined' ? 'selected' : '' ?>>Decline</option><option value="scheduled" <?= $booking['status'] === 'scheduled' ? 'selected' : '' ?>>Scheduled</option><option value="in_progress" <?= $booking['status'] === 'in_progress' ? 'selected' : '' ?>>In progress</option><option value="completed" <?= $booking['status'] === 'completed' ? 'selected' : '' ?>>Completed</option><option value="cancelled" <?= $booking['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option></select><select name="technician_id" aria-label="Assign technician"><option value="0">Unassigned</option><?php foreach ($technicians as $technician): ?><option value="<?= (int) $technician['id'] ?>" <?= (int) $booking['technician_id'] === (int) $technician['id'] ? 'selected' : '' ?>><?= esc($technician['first_name'] . ' ' . $technician['last_name']) ?></option><?php endforeach ?></select><input type="datetime-local" name="scheduled_at" value="<?= esc(date('Y-m-d\TH:i', strtotime($booking['scheduled_at']))) ?>" aria-label="Appointment time"><button type="submit">Save booking</button></form></td></tr><?php endforeach ?>
                    </tbody></table></div><?php else: ?><p class="empty-state">Customer service requests will appear here.</p><?php endif ?>
                </section>

                <?php endif ?>
                <?php if ($section === 'users'): ?>
                <section class="portal-panel full-panel" id="employee-roles">
                    <div class="panel-heading"><h2>Employee roles & responsibilities</h2><span>Access is determined by account role, department, and job title</span></div>
                    <div class="notice-list">
                        <article class="notice-item"><h3>HR</h3><p>Create, edit, activate, or deactivate employee accounts; publish staff or customer announcements.</p></article>
                        <article class="notice-item"><h3>Dispatch</h3><p>Coordinate all service bookings, appointment status, and technician assignment. Dispatch also handles confirmed product deliveries: assign a courier, track the shipment, and update it as out for delivery or delivered.</p></article>
                        <article class="notice-item"><h3>Technician / Head Technician</h3><p>Only these job titles get assigned service jobs, GPS site check-ins, and field-report tools. Dispatch must assign a booking before the technician can act on it.</p></article>
                        <article class="notice-item"><h3>Sales</h3><p>Respond to product questions sent as customer support requests. Sales cannot independently confirm or fulfill delivery orders; admin confirms product requests and Dispatch handles delivery.</p></article>
                        <article class="notice-item"><h3>Accounting</h3><p>Record and review service booking payments and issue receipts. Product requests are not an online checkout or payment gateway.</p></article>
                        <article class="notice-item"><h3>Customer Service</h3><p>Review customer support tickets and reply to customer questions.</p></article>
                        <article class="notice-item"><h3>IT, Marketing, Administration, and other staff</h3><p>Can sign in, read announcements, and manage their own account. Marketing and HR can publish announcements. Other department tools should be added when those workflows are defined.</p></article>
                    </div>
                </section>
                <section class="portal-panel full-panel" id="users">
                    <div class="panel-heading"><h2>User management</h2><span>Customer self-registration is enabled. Employee accounts are admin-created.</span></div>
                    <form class="portal-form" method="post" action="<?= site_url('portal/employees') ?>">
                        <?= csrf_field() ?>
                        <label>First name<input name="first_name" required></label><label>Last name<input name="last_name" required></label><label>Middle name<input name="middle_name" required></label><label>Birthdate<input type="date" name="birthdate" required></label>
                        <fieldset class="gender-group form-wide"><legend>Gender</legend><div class="gender-options"><label class="gender-option"><input type="radio" name="gender" value="male" required><span>Male</span></label><label class="gender-option"><input type="radio" name="gender" value="female" required><span>Female</span></label></div></fieldset><label>Email<input type="email" name="email" required></label><label>Phone<input name="phone_number" required></label><label>Address<input name="address" required></label>
                        <label>Username<input name="username" autocomplete="off" required></label><label>Department<select name="department" required><option value="">Select department</option><?php foreach (['Administration', 'IT', 'Dispatch', 'Accounting', 'HR', 'Marketing', 'Sales', 'Customer Service'] as $department): ?><option><?= esc($department) ?></option><?php endforeach ?></select></label><label>Job title<input name="job_title" required placeholder="Technician, Dispatcher, HR staff"></label>
                        <label class="form-wide">Temporary password<input type="password" name="password" minlength="12" required><small>At least 12 characters; employee must change it after first login.</small></label><button type="submit">Create employee account</button>
                    </form>
                    <div class="table-wrap" style="margin-top:20px"><table class="portal-table"><thead><tr><th>Name</th><th>Username</th><th>Role / department</th><th>Status</th><th>Sign-in</th><th>Account action</th></tr></thead><tbody><?php foreach ($users as $user): ?><tr><td><?= esc($user['first_name'] . ' ' . $user['last_name']) ?><small><?= esc($user['email']) ?></small></td><td><?= esc($user['username']) ?></td><td><?= esc(ucfirst($user['role'])) ?><small><?= esc(($user['department'] ?? '') . ' ' . ($user['job_title'] ?? '')) ?></small></td><td><span class="status <?= $user['is_active'] ? '' : 'cancelled' ?>"><?= $user['is_active'] ? 'Active' : 'Inactive' ?></span></td><td><?= !empty($user['must_change_password']) ? 'Temporary password change required' : 'Ready' ?></td><td><?php if ($user['role'] === 'employee' && (int) $user['id'] !== (int) session()->get('user_id')): ?><form method="post" action="<?= site_url('portal/users/' . $user['id'] . '/toggle') ?>"><?= csrf_field() ?><button class="small-button <?= $user['is_active'] ? 'danger-button' : '' ?>" type="submit"><?= $user['is_active'] ? 'Deactivate' : 'Activate' ?></button></form><?php endif ?></td></tr><?php endforeach ?></tbody></table></div>
                </section>

                <section class="portal-panel full-panel" id="employee-edit">
                    <div class="panel-heading"><h2>Edit employee details</h2><span>Changes apply immediately</span></div>
                    <?php foreach ($users as $user): ?><?php if ($user['role'] === 'employee'): ?>
                        <form class="portal-form employee-edit-form" method="post" action="<?= site_url('portal/users/' . $user['id'] . '/update') ?>">
                            <?= csrf_field() ?>
                            <label>First name<input name="first_name" value="<?= esc($user['first_name']) ?>" required></label><label>Last name<input name="last_name" value="<?= esc($user['last_name']) ?>" required></label><label>Middle name<input name="middle_name" value="<?= esc($user['middle_name'] ?? '') ?>" required></label><label>Birthdate<input type="date" name="birthdate" value="<?= esc($user['birthdate'] ?? '') ?>" required></label>
                            <label>Gender<input name="gender" value="<?= esc($user['gender'] ?? '') ?>" required></label><label>Email<input type="email" name="email" value="<?= esc($user['email']) ?>" required></label><label>Phone<input name="phone_number" value="<?= esc($user['phone_number'] ?? '') ?>" required></label><label>Address<input name="address" value="<?= esc($user['address'] ?? '') ?>" required></label>
                            <label>Username<input name="username" value="<?= esc($user['username']) ?>" required></label><?php if ($user['role'] === 'employee'): ?><label>Department<select name="department" required><?php foreach (['Administration', 'IT', 'Dispatch', 'Accounting', 'HR', 'Marketing', 'Sales', 'Customer Service'] as $department): ?><option <?= $user['department'] === $department ? 'selected' : '' ?>><?= esc($department) ?></option><?php endforeach ?></select></label><label>Job title<input name="job_title" value="<?= esc($user['job_title'] ?? '') ?>" required></label><?php endif ?><button type="submit">Save <?= esc($user['first_name']) ?></button>
                        </form>
                    <?php endif ?><?php endforeach ?>
                </section>

                <?php endif ?>
                <?php if ($section === 'services'): ?>
                <section class="portal-panel full-panel" id="services">
                    <div class="panel-heading"><h2>Service categories, pricing & availability</h2><span>Manage without a developer</span></div>
                    <div class="table-wrap"><table class="portal-table"><thead><tr><th>Service</th><th>Category</th><th>Price</th><th>Availability</th><th>Active</th><th></th></tr></thead><tbody><?php foreach ($services as $service): ?><tr><td colspan="6"><form class="portal-form" method="post" action="<?= site_url('portal/services/' . $service['id']) ?>"><?= csrf_field() ?><label>Service name<input name="name" value="<?= esc($service['name']) ?>" required></label><label>Category<input name="category" value="<?= esc($service['category']) ?>" required></label><label>Price (PHP)<input type="number" name="price" min="0" step="0.01" value="<?= esc($service['price']) ?>" required></label><label>Availability<input name="availability" value="<?= esc($service['availability']) ?>" required></label><label class="toggle-row"><input type="checkbox" name="is_active" value="1" <?= $service['is_active'] ? 'checked' : '' ?>> Active</label><button type="submit">Save</button></form></td></tr><?php endforeach ?></tbody></table></div>
                </section>
                <?php endif ?>

                <?php if ($section === 'reports'): ?>
                <section class="portal-panel full-panel" id="reports">
                    <div class="panel-heading"><h2>Bookings by month</h2><span>Recent six months</span></div>
                    <?php if ($monthly): ?><div class="chart-list"><?php foreach ($monthly as $month): ?><div class="chart-row"><span><?= esc(date('M Y', strtotime($month['month'] . '-01'))) ?></span><div class="chart-track"><div class="chart-fill" style="width:<?= round((int) $month['total'] / $maxMonthly * 100) ?>%"></div></div><strong><?= (int) $month['total'] ?></strong></div><?php endforeach ?></div><?php else: ?><p class="empty-state">Booking activity will appear as requests come in.</p><?php endif ?>
                    <div class="panel-heading" style="margin-top:22px"><h2>Service usage</h2><span>Bookings by category</span></div>
                    <?php $maxUsage = max([1, ...array_map(static fn ($row) => (int) $row['total'], $service_usage)]); ?>
                    <div class="chart-list"><?php foreach ($service_usage as $usage): ?><div class="chart-row"><span><?= esc($usage['name']) ?></span><div class="chart-track"><div class="chart-fill" style="width:<?= round((int) $usage['total'] / $maxUsage * 100) ?>%;background:var(--coral)"></div></div><strong><?= (int) $usage['total'] ?></strong></div><?php endforeach ?></div>
                    <p class="legal-note">Paid revenue reflects payment records entered by the admin team.</p>
                </section>

                <?php endif ?>
                <?php if ($section === 'payments'): ?>
                <section class="portal-panel full-panel" id="payments">
                    <div class="panel-heading"><h2>Payments & receipts</h2><span>Record offline payments</span></div>
                    <form class="portal-form" method="post" action="<?= site_url('portal/payments') ?>"><?= csrf_field() ?><label class="form-wide">Booking<select name="booking_id" required><option value="">Choose booking</option><?php foreach ($bookings as $booking): ?><option value="<?= (int) $booking['id'] ?>">#<?= (int) $booking['id'] ?> · <?= esc(($booking['customer_first'] . ' ' . $booking['customer_last']) . ' · ' . ($booking['service_name'] ?? 'Service')) ?></option><?php endforeach ?></select></label><label>Amount (PHP)<input type="number" name="amount" min="0" step="0.01" required></label><label>Status<select name="status"><option value="pending">Pending</option><option value="paid">Paid</option></select></label><button type="submit">Save payment</button></form>
                    <?php if ($payments): ?><div class="table-wrap" style="margin-top:16px"><table class="portal-table"><thead><tr><th>Customer / service</th><th>Amount</th><th>Receipt</th><th>Status</th></tr></thead><tbody><?php foreach ($payments as $payment): ?><tr><td><?= esc($payment['first_name'] . ' ' . $payment['last_name']) ?><small><?= esc($payment['service_name'] ?? 'Service') ?></small></td><td>₱<?= number_format((float) $payment['amount'], 2) ?></td><td><?= esc($payment['receipt_number'] ?? '—') ?></td><td><span class="status <?= esc($payment['status']) ?>"><?= esc($payment['status']) ?></span></td></tr><?php endforeach ?></tbody></table></div><?php else: ?><p class="empty-state">Payment records and receipts will appear here.</p><?php endif ?>
                </section>

                <?php endif ?>
                <?php if ($section === 'orders'): ?>
                <section class="portal-panel full-panel" id="orders">
                    <div class="panel-heading"><h2>Product requests & delivery</h2><span>Confirm orders, coordinate Dispatch, and update customers</span></div>
                    <p class="legal-note">Requests are not paid online. Confirm product availability, final price, and payment arrangements with the customer. Dispatch employees handle delivery updates after assignment.</p>
                    <?php if ($product_orders): ?><div class="table-wrap"><table class="portal-table"><thead><tr><th>Customer & items</th><th>Delivery details</th><th>Order status</th><th>Payment</th><th>Dispatch / tracking</th></tr></thead><tbody><?php foreach ($product_orders as $order): ?><tr><td><?= esc(trim($order['customer_first'] . ' ' . $order['customer_last'])) ?><small><?= esc($order['customer_email']) ?></small><?= esc($order['items'] ?? 'Product request') ?><small>Order #<?= (int) $order['id'] ?> · <?= (float) $order['total_amount'] > 0 ? '₱' . number_format((float) $order['total_amount'], 2) : 'Price to confirm' ?></small></td><td><?= esc($order['recipient_name']) ?><small><?= esc($order['recipient_phone']) ?></small><small><?= esc($order['delivery_address']) ?></small><small><?= esc($order['delivery_notes'] ?? '') ?></small></td><td colspan="3"><form class="portal-form" method="post" action="<?= site_url('portal/orders/' . $order['id'] . '/update') ?>" style="min-width:220px;gap:8px"><?= csrf_field() ?><label>Status<select name="status"><?php foreach (['pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'] as $orderStatus): ?><option value="<?= esc($orderStatus) ?>" <?= $order['status'] === $orderStatus ? 'selected' : '' ?>><?= esc(ucfirst(str_replace('_', ' ', $orderStatus))) ?></option><?php endforeach ?></select></label><label>Payment status<select name="payment_status"><?php foreach (['to_be_confirmed', 'unpaid', 'paid'] as $paymentStatus): ?><option value="<?= esc($paymentStatus) ?>" <?= $order['payment_status'] === $paymentStatus ? 'selected' : '' ?>><?= esc(ucfirst(str_replace('_', ' ', $paymentStatus))) ?></option><?php endforeach ?></select></label><label>Assign Dispatch employee<select name="delivery_staff_id"><option value="0">Unassigned</option><?php foreach ($delivery_staff as $staff): ?><option value="<?= (int) $staff['id'] ?>" <?= (int) $order['delivery_staff_id'] === (int) $staff['id'] ? 'selected' : '' ?>><?= esc($staff['first_name'] . ' ' . $staff['last_name'] . ' · ' . $staff['job_title']) ?></option><?php endforeach ?></select></label><label>Tracking code<input name="tracking_code" maxlength="100" value="<?= esc($order['tracking_code'] ?? '') ?>"></label><button type="submit">Save order</button></form></td></tr><?php endforeach ?></tbody></table></div><?php else: ?><p class="empty-state">Customer product requests will appear here.</p><?php endif ?>
                </section>
                <?php endif ?>
                <?php if ($section === 'stories'): ?>
                <section class="portal-panel full-panel" id="stories">
                    <div class="panel-heading"><h2>Customer-submitted feedback</h2><span>Customers write the feedback; admins review publication</span></div>
                    <?php if ($stories): ?><div class="notice-list" style="margin-top:16px"><?php foreach ($stories as $story): ?><article class="notice-item"><div class="notice-meta"><span><?= esc($story['customer_display']) ?> · <?= esc($story['service_name']) ?></span><span class="status <?= esc($story['moderation_status']) ?>"><?= esc(ucfirst($story['moderation_status'])) ?><?= $story['moderation_status'] === 'approved' ? ($story['is_active'] ? ' · Published' : ' · Hidden') : '' ?></span></div><?php if (!empty($story['submitter_email'])): ?><p>Submitted by <?= esc(trim($story['submitter_first'] . ' ' . $story['submitter_last'])) ?> (<?= esc($story['submitter_email']) ?>)</p><?php endif ?><blockquote><?= esc($story['quote']) ?></blockquote><?php if ($story['moderation_status'] === 'pending' && !empty($story['customer_id'])): ?><form class="inline-form" method="post" action="<?= site_url('portal/stories/' . $story['id'] . '/moderate') ?>"><?= csrf_field() ?><button class="small-button" name="decision" value="approve" type="submit">Approve & publish</button><button class="small-button danger-button" name="decision" value="reject" type="submit">Reject</button></form><?php elseif ($story['moderation_status'] === 'approved'): ?><form method="post" action="<?= site_url('portal/stories/' . $story['id'] . '/toggle') ?>"><?= csrf_field() ?><button class="small-button" type="submit"><?= $story['is_active'] ? 'Hide story' : 'Publish story' ?></button></form><?php endif ?></article><?php endforeach ?></div><?php else: ?><p class="empty-state">Customer feedback submissions will appear here when customers share a review of a completed service.</p><?php endif ?>
                </section>

                <?php endif ?>
                <?php if ($section === 'announcements'): ?>
                <section class="portal-panel full-panel" id="announcements">
                    <div class="panel-heading"><h2>Send an announcement</h2></div>
                    <form class="portal-form" method="post" action="<?= site_url('portal/announcements') ?>"><?= csrf_field() ?><label>Audience<select name="audience"><option value="customers">Customers</option><option value="employees">Employees</option><option value="all">Everyone</option><option value="public">Public news</option></select></label><label>Type<select name="type"><option value="reminder">Reminder</option><option value="promotion">Promotion</option><option value="update">System update</option><option value="safety">Safety notice</option></select></label><label class="form-wide">Title<input name="title" maxlength="160" required></label><label class="form-wide">Message<textarea name="message" required></textarea></label><button type="submit">Publish announcement</button></form>
                </section>

                <?php endif ?>
                <?php if ($section === 'support'): ?>
                <section class="portal-panel full-panel" id="support">
                    <div class="panel-heading"><h2>Customer support</h2><span><?= (int) $open_tickets ?> open</span></div>
                    <?php if ($tickets): ?><div class="table-wrap"><table class="portal-table"><thead><tr><th>Customer</th><th>Request</th><th>Status</th><th>Reply</th></tr></thead><tbody><?php foreach ($tickets as $ticket): ?><tr><td><?= esc($ticket['first_name'] . ' ' . $ticket['last_name']) ?><small><?= esc($ticket['email']) ?></small></td><td><?= esc($ticket['subject']) ?><small><?= esc($ticket['message']) ?></small></td><td><span class="status <?= esc($ticket['status']) ?>"><?= esc($ticket['status']) ?></span></td><td><form class="inline-form" method="post" action="<?= site_url('portal/tickets/' . $ticket['id'] . '/reply') ?>"><?= csrf_field() ?><label><span class="sr-only">Reply to ticket</span><input name="admin_reply" placeholder="Write a reply" required></label><button class="small-button" type="submit">Send</button></form></td></tr><?php endforeach ?></tbody></table></div><?php else: ?><p class="empty-state">Customer requests will appear here.</p><?php endif ?>
                </section>

                <?php endif ?>
                <?php if ($section === 'field-reports'): ?>
                <section class="portal-panel full-panel" id="field-reports">
                    <div class="panel-heading"><h2>Field reports</h2><span>Technician submissions</span></div>
                    <?php if ($reports): ?><div class="table-wrap"><table class="portal-table"><thead><tr><th>Technician</th><th>Issue</th><th>Details</th><th>Submitted</th></tr></thead><tbody><?php foreach ($reports as $report): ?><tr><td><?= esc($report['first_name'] . ' ' . $report['last_name']) ?></td><td><?= esc($report['issue']) ?></td><td><?= esc($report['details']) ?></td><td><?= esc(date('M j, Y g:i A', strtotime($report['created_at']))) ?></td></tr><?php endforeach ?></tbody></table></div><?php else: ?><p class="empty-state">Technician reports will appear here.</p><?php endif ?>
                </section>

                <?php endif ?>
                <?php if ($section === 'attendance'): ?>
                <section class="portal-panel full-panel" id="attendance">
                    <div class="panel-heading"><h2>Field attendance</h2><span>Site check-ins with coordinates</span></div>
                    <?php if ($checkins): ?><div class="table-wrap"><table class="portal-table"><thead><tr><th>Employee</th><th>Assigned service</th><th>Checked in</th><th>Coordinates</th></tr></thead><tbody><?php foreach ($checkins as $checkin): ?><tr><td><?= esc($checkin['first_name'] . ' ' . $checkin['last_name']) ?></td><td><?= esc($checkin['service_name'] ?? 'Assigned field work') ?></td><td><?= esc(date('M j, Y · g:i A', strtotime($checkin['checked_in_at']))) ?></td><td><?= esc($checkin['latitude']) ?>, <?= esc($checkin['longitude']) ?></td></tr><?php endforeach ?></tbody></table></div><?php else: ?><p class="empty-state">Technician check-ins will appear here.</p><?php endif ?>
                </section>
                <?php endif ?>
            </div>
        </main>
    </div>
</body>
</html>