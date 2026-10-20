<?php
$displayName = esc((string) ($name ?? 'Customer'));
$firstInitial = strtoupper(substr((string) ($profile['first_name'] ?? 'C'), 0, 1));
$activeBookings = count(array_filter($bookings, static fn ($booking) => !in_array($booking['status'], ['completed', 'cancelled', 'declined'], true)));
$section = (string) ($customer_section ?? 'overview');
$sectionTitles = [
    'overview' => 'Overview',
    'schedule' => 'Your schedule',
    'products' => 'Products',
    'notifications' => 'Notifications',
    'history' => 'Service history',
    'profile' => 'Your profile',
    'support' => 'Customer support',
];
$upcomingBookings = array_values(array_filter($bookings, static fn ($booking) => !in_array($booking['status'], ['completed', 'cancelled', 'declined'], true)));
$nextBooking = $upcomingBookings[0] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer dashboard | Sun Son Solar</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/portal.css') ?>">
</head>
<body class="portal-page">
    <header class="portal-header">
        <a class="brand" href="<?= site_url('/') ?>"><img src="<?= base_url('assets/logo-sss.png') ?>" alt=""><span>Sun Son <b>Solar</b></span></a>
        <div class="portal-header-actions"><span>Customer portal</span><div class="portal-user"><span class="avatar"><?= esc($firstInitial) ?></span><span><?= $displayName ?></span></div><a href="<?= site_url('logout') ?>">Log out</a></div>
    </header>
    <div class="portal-layout">
        <aside class="portal-sidebar"><p>Your account</p><nav>
            <?php foreach ($sectionTitles as $sectionKey => $sectionTitle): ?><a class="<?= $section === $sectionKey ? 'active' : '' ?>" href="<?= site_url('customer/' . $sectionKey) ?>"><?= esc($sectionTitle) ?></a><?php endforeach ?>
        </nav></aside>
        <main class="portal-main">
            <?php if (!empty($message)): ?><div class="flash-message"><?= esc($message) ?></div><?php endif ?>
            <?php if (!empty($error)): ?><div class="flash-message flash-error"><?= esc($error) ?></div><?php endif ?>
            <div class="portal-heading"><div><p class="eyebrow">Customer portal / <?= esc($sectionTitles[$section]) ?></p><h1><?= $section === 'overview' ? 'Good to see you, ' . $displayName . '.' : esc($sectionTitles[$section]) ?></h1><p><?= $section === 'overview' ? 'Everything for your solar service, in one place.' : 'Your Sun Son Solar account, all in one place.' ?></p></div><?php if (in_array($section, ['overview', 'schedule'], true)): ?><a class="button button-dark" href="<?= site_url('customer/schedule') ?>">Schedule a service <span aria-hidden="true">↗</span></a><?php endif ?></div>

            <?php if ($section === 'overview'): ?>
            <div class="stat-grid">
                <div class="stat-item"><span>Upcoming services</span><strong><?= $activeBookings ?></strong></div>
                <div class="stat-item"><span>Completed services</span><strong><?= count(array_filter($bookings, static fn ($booking) => $booking['status'] === 'completed')) ?></strong></div>
                <div class="stat-item"><span>Support requests</span><strong><?= count($tickets) ?></strong></div>
                <div class="stat-item"><span>Services available</span><strong><?= count($services) ?></strong></div>
            </div>
            <?php endif ?>

            <div class="portal-grid">
                <?php if ($section === 'overview'): ?>
                    <section class="portal-panel full-panel">
                        <div class="panel-heading"><h2>Next up</h2><a class="text-link" href="<?= site_url('customer/schedule') ?>">View schedule <span aria-hidden="true">→</span></a></div>
                        <?php if ($nextBooking): ?><p><strong><?= esc($nextBooking['service_name'] ?? 'Solar service') ?></strong> · <?= esc(date('M j, Y · g:i A', strtotime($nextBooking['scheduled_at']))) ?></p><p><span class="status <?= esc($nextBooking['status']) ?>"><?= esc(str_replace('_', ' ', $nextBooking['status'])) ?></span></p><?php else: ?><p class="empty-state">No upcoming services. Book a consultation to get started.</p><?php endif ?>
                        <div class="portal-shortcuts"><a href="<?= site_url('customer/products') ?>">Browse products <span aria-hidden="true">↗</span></a><a href="<?= site_url('customer/notifications') ?>">Check notifications <span aria-hidden="true">↗</span></a><a href="<?= site_url('customer/history') ?>">View service history <span aria-hidden="true">↗</span></a></div>
                    </section>
                <?php endif ?>
                <?php if ($section === 'schedule'): ?>
                <section class="portal-panel full-panel" id="schedule">
                    <div class="panel-heading"><h2>Your schedule</h2><span><?= $activeBookings ?> active</span></div>
                    <?php if ($bookings): ?>
                        <div class="table-wrap"><table class="portal-table"><thead><tr><th>Service</th><th>Date & time</th><th>Status</th><th>Technician</th><th></th></tr></thead><tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr><td><?= esc($booking['service_name'] ?? 'Service') ?></td><td><?= esc(date('M j, Y · g:i A', strtotime($booking['scheduled_at']))) ?></td><td><span class="status <?= esc($booking['status']) ?>"><?= esc(str_replace('_', ' ', $booking['status'])) ?></span></td><td><?= esc(trim(($booking['technician_first'] ?? '') . ' ' . ($booking['technician_last'] ?? '')) ?: 'To be assigned') ?></td><td><?php if (in_array($booking['status'], ['pending', 'approved'], true)): ?><form method="post" action="<?= site_url('portal/bookings/' . $booking['id'] . '/cancel') ?>"><?= csrf_field() ?><button class="small-button danger-button" type="submit">Cancel</button></form><?php endif ?></td></tr>
                        <?php endforeach ?>
                        </tbody></table></div>
                    <?php else: ?><p class="empty-state">No service visits yet. Request a consultation to get started.</p><?php endif ?>
                    <div class="booking-request" style="margin-top:24px;padding-top:18px;border-top:1px solid var(--line)">
                        <div class="panel-heading"><h2>Request a service</h2></div>
                        <form class="portal-form" method="post" action="<?= site_url('portal/bookings') ?>">
                            <?= csrf_field() ?>
                            <label>Service<select name="service_id" required><option value="">Choose a service</option><?php foreach ($services as $service): ?><option value="<?= (int) $service['id'] ?>"><?= esc($service['name']) ?></option><?php endforeach ?></select></label>
                            <label>Preferred date and time<input type="datetime-local" name="scheduled_at" min="<?= date('Y-m-d\TH:i', strtotime('+1 hour')) ?>" required></label>
                            <label class="form-wide">Notes for our team<textarea name="customer_notes" placeholder="Tell us a little about your property or request."></textarea></label>
                            <button type="submit">Send request</button>
                        </form>
                    </div>
                </section>
                <?php endif ?>

                <?php if ($section === 'products'): ?>
                <section class="shop-page full-panel" id="products">
                    <div class="shop-promo"><div><p class="eyebrow">Sun Son Solar catalog</p><h2>Find the right pieces<br>for your solar setup.</h2></div><span class="shop-promo-mark" aria-hidden="true">☼</span></div>
                    <form class="shop-search" method="get" action="<?= site_url('customer/products') ?>">
                        <label class="shop-search-field"><span class="sr-only">Search products</span><input type="search" name="q" value="<?= esc($product_search) ?>" placeholder="Search panels, batteries, inverters..."></label>
                        <label class="shop-category-select"><span class="sr-only">Product category</span><select name="category"><option value="">All categories</option><?php foreach ($product_categories as $category): ?><option value="<?= esc($category) ?>" <?= $product_category === $category ? 'selected' : '' ?>><?= esc($category) ?></option><?php endforeach ?></select></label>
                        <button type="submit">Search products</button>
                    </form>
                    <nav class="shop-category-chips" aria-label="Product categories"><a class="<?= $product_category === '' ? 'selected' : '' ?>" href="<?= site_url('customer/products') ?><?= $product_search !== '' ? '?q=' . rawurlencode($product_search) : '' ?>">All products</a><?php foreach ($product_categories as $category): ?><a class="<?= $product_category === $category ? 'selected' : '' ?>" href="<?= site_url('customer/products') ?>?<?= http_build_query(array_filter(['q' => $product_search, 'category' => $category], static fn ($value) => $value !== '')) ?>"><?= esc($category) ?></a><?php endforeach ?></nav>
                    <div class="shop-results-heading"><h2>Products for your project</h2><span><?= count($products) ?> items</span></div>
                    <?php if ($products): ?>
                        <div class="shop-product-grid">
                            <?php foreach ($products as $product): ?>
                                <article class="shop-product-card">
                                    <a class="shop-product-image" href="<?= site_url('customer/support') ?>?subject=<?= rawurlencode('Product inquiry: ' . $product['name']) ?>" aria-label="Ask about <?= esc($product['name']) ?>">
                                        <img src="https://images.unsplash.com/photo-1509391366360-2e959784a276?auto=format&fit=crop&w=900&q=80" alt="Solar equipment for <?= esc($product['name']) ?>" loading="lazy">
                                        <span><?= esc($product['category']) ?></span>
                                    </a>
                                    <div class="shop-product-info">
                                        <p class="shop-product-category"><?= esc($product['category']) ?></p>
                                        <h3><?= esc($product['name']) ?></h3>
                                        <p class="shop-product-description"><?= esc($product['description'] ?? '') ?></p>
                                        <div class="shop-product-bottom"><strong><?= (float) $product['price'] > 0 ? '₱' . number_format((float) $product['price'], 2) : 'Request a quote' ?></strong><a href="<?= site_url('customer/support') ?>?subject=<?= rawurlencode('Product inquiry: ' . $product['name']) ?>">Ask about item <span aria-hidden="true">↗</span></a></div>
                                        <details class="product-order-details"><summary>Request order & delivery</summary>
                                            <form class="portal-form" method="post" action="<?= site_url('portal/orders') ?>">
                                                <?= csrf_field() ?><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                                <label>Quantity<input type="number" name="quantity" min="1" max="50" value="1" required></label>
                                                <label>Recipient name<input name="recipient_name" value="<?= esc(trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? ''))) ?>" maxlength="150" required></label>
                                                <label>Contact phone<input name="recipient_phone" value="<?= esc($profile['phone_number'] ?? '') ?>" maxlength="30" required></label>
                                                <label class="form-wide">Delivery address<input name="delivery_address" value="<?= esc($profile['address'] ?? '') ?>" maxlength="500" required></label>
                                                <label class="form-wide">Delivery notes<textarea name="delivery_notes" placeholder="Landmarks or preferred delivery instructions (optional)"></textarea></label>
                                                <p class="legal-note form-wide">This submits a request, not a paid checkout. The team will confirm availability, final price, payment, and delivery arrangements.</p>
                                                <button type="submit">Submit product request</button>
                                            </form>
                                        </details>
                                    </div>
                                </article>
                            <?php endforeach ?>
                        </div>
                    <?php else: ?>
                        <p class="shop-empty-state">No products match those filters. Try another search or category.</p>
                    <?php endif ?>
                    <section class="portal-panel full-panel" style="margin-top:24px" id="product-orders">
                        <div class="panel-heading"><h2>Your product requests</h2><span>Delivery updates from the team</span></div>
                        <?php if ($product_orders): ?><div class="table-wrap"><table class="portal-table"><thead><tr><th>Order</th><th>Items</th><th>Delivery address</th><th>Assigned team</th><th>Status</th><th>Tracking</th></tr></thead><tbody><?php foreach ($product_orders as $order): ?><tr><td>#<?= (int) $order['id'] ?><small><?= esc(date('M j, Y', strtotime($order['created_at']))) ?></small></td><td><?= esc($order['items'] ?? 'Product request') ?><small><?= (float) $order['total_amount'] > 0 ? '₱' . number_format((float) $order['total_amount'], 2) : 'Price to be confirmed' ?></small></td><td><?= esc($order['recipient_name']) ?><small><?= esc($order['recipient_phone']) ?> · <?= esc($order['delivery_address']) ?></small></td><td><?= esc(trim(($order['delivery_first'] ?? '') . ' ' . ($order['delivery_last'] ?? '')) ?: 'To be assigned') ?></td><td><span class="status <?= esc($order['status']) ?>"><?= esc(str_replace('_', ' ', $order['status'])) ?></span><small>Payment: <?= esc(str_replace('_', ' ', $order['payment_status'])) ?></small></td><td><?= esc($order['tracking_code'] ?: 'Not assigned') ?></td></tr><?php endforeach ?></tbody></table></div><?php else: ?><p class="empty-state">Your product requests and delivery progress will appear here.</p><?php endif ?>
                    </section>
                </section>
                <?php endif ?>

                <?php if ($section === 'notifications'): ?>
                <section class="portal-panel full-panel" id="notifications">
                    <div class="panel-heading"><h2>Notifications</h2><span>Updates & reminders</span></div>
                    <?php if ($notifications): ?><div class="notice-list"><?php foreach ($notifications as $notification): ?><article class="notice-item"><div class="notice-meta"><span><?= esc(ucfirst($notification['type'])) ?></span><span><?= esc(date('M j', strtotime($notification['created_at']))) ?></span></div><h3><?= esc($notification['title']) ?></h3><p><?= esc($notification['message']) ?></p></article><?php endforeach ?></div><?php else: ?><p class="empty-state">You’re all caught up. New reminders and updates will appear here.</p><?php endif ?>
                    <form class="portal-form" style="margin-top:20px" method="post" action="<?= site_url('portal/preferences') ?>">
                        <?= csrf_field() ?><div class="toggle-row form-wide"><label><input type="checkbox" name="service_notifications" value="1" <?= !empty($preferences['service_notifications']) || !$preferences ? 'checked' : '' ?>> Service reminders</label><label><input type="checkbox" name="promotional_notifications" value="1" <?= !empty($preferences['promotional_notifications']) || !$preferences ? 'checked' : '' ?>> Solar offers</label></div><button type="submit">Save preferences</button>
                    </form>
                </section>
                <?php endif ?>

                <?php if ($section === 'history'): ?>
                <section class="portal-panel full-panel" id="history">
                    <div class="panel-heading"><h2>Service history</h2><span>Completed visits</span></div>
                    <?php $completed = array_filter($bookings, static fn ($booking) => $booking['status'] === 'completed'); ?>
                    <?php if ($completed): ?><div class="table-wrap"><table class="portal-table"><thead><tr><th>Service</th><th>Date</th><th>Status</th></tr></thead><tbody><?php foreach ($completed as $booking): ?><tr><td><?= esc($booking['service_name'] ?? 'Service') ?></td><td><?= esc(date('M j, Y', strtotime($booking['scheduled_at']))) ?></td><td><span class="status">Completed</span></td></tr><?php endforeach ?></tbody></table></div><?php else: ?><p class="empty-state">Completed services will appear here.</p><?php endif ?>
                    <div class="panel-heading" style="margin-top:20px"><h2>Share your experience</h2><span>Feedback is reviewed before appearing publicly</span></div>
                    <?php if ($feedback_bookings): ?>
                        <form class="portal-form" method="post" action="<?= site_url('portal/feedback') ?>">
                            <?= csrf_field() ?>
                            <label class="form-wide">Completed service<select name="booking_id" required><option value="">Choose a service</option><?php foreach ($feedback_bookings as $feedbackBooking): ?><option value="<?= (int) $feedbackBooking['id'] ?>"><?= esc(($feedbackBooking['service_name'] ?? 'Solar service') . ' · ' . date('M j, Y', strtotime($feedbackBooking['scheduled_at']))) ?></option><?php endforeach ?></select></label>
                            <label class="form-wide">Your feedback<textarea name="quote" minlength="10" maxlength="2000" required placeholder="Tell us about your experience with Sun Son Solar."></textarea></label>
                            <button type="submit">Submit feedback</button>
                        </form>
                    <?php else: ?><p class="empty-state">Feedback can be submitted after a service is completed. You can leave one review per completed service.</p><?php endif ?>
                    <?php if ($customer_stories): ?><div class="panel-heading" style="margin-top:20px"><h2>Your submitted feedback</h2></div><div class="notice-list"><?php foreach ($customer_stories as $customerStory): ?><article class="notice-item"><div class="notice-meta"><span><?= esc($customerStory['service_name']) ?></span><span class="status <?= esc($customerStory['moderation_status']) ?>"><?= esc(ucfirst($customerStory['moderation_status'])) ?></span></div><p><?= esc($customerStory['quote']) ?></p></article><?php endforeach ?></div><?php endif ?>
                    <div class="panel-heading" style="margin-top:20px"><h2>Payments & receipts</h2></div>
                    <?php if ($payments): ?><div class="table-wrap"><table class="portal-table"><thead><tr><th>Receipt</th><th>Amount</th><th>Date</th><th>Status</th></tr></thead><tbody><?php foreach ($payments as $payment): ?><tr><td><?= esc($payment['receipt_number'] ?: 'Pending') ?></td><td>₱<?= number_format((float) $payment['amount'], 2) ?></td><td><?= esc($payment['paid_at'] ? date('M j, Y', strtotime($payment['paid_at'])) : '—') ?></td><td><span class="status <?= esc($payment['status']) ?>"><?= esc($payment['status']) ?></span></td></tr><?php endforeach ?></tbody></table></div><?php else: ?><p class="empty-state">No payments recorded yet.</p><?php endif ?>
                </section>
                <?php endif ?>

                <?php if ($section === 'profile'): ?>
                <section class="portal-panel full-panel" id="profile">
                    <div class="panel-heading"><h2>Your profile</h2><span>Personal details & password</span></div>
                    <form class="portal-form" method="post" action="<?= site_url('portal/profile') ?>">
                        <?= csrf_field() ?>
                        <label>First name<input name="first_name" value="<?= esc($profile['first_name'] ?? '') ?>" required></label><label>Last name<input name="last_name" value="<?= esc($profile['last_name'] ?? '') ?>" required></label>
                        <label>Middle name<input name="middle_name" value="<?= esc($profile['middle_name'] ?? '') ?>"></label><label>Birthdate<input type="date" name="birthdate" value="<?= esc($profile['birthdate'] ?? '') ?>" required></label>
                        <label>Gender<input name="gender" value="<?= esc($profile['gender'] ?? '') ?>" required></label><label>Email<input type="email" name="email" value="<?= esc($profile['email'] ?? '') ?>" required></label>
                        <label>Phone<input name="phone_number" value="<?= esc($profile['phone_number'] ?? '') ?>" required></label><label>Address<input name="address" value="<?= esc($profile['address'] ?? '') ?>" required></label>
                        <label>Current password<input type="password" name="current_password" autocomplete="current-password" placeholder="Required to change password"></label><label>New password<input type="password" name="new_password" autocomplete="new-password" minlength="12" placeholder="Leave blank to keep current"></label>
                        <button type="submit">Save profile</button>
                    </form>
                </section>
                <?php endif ?>

                <?php if ($section === 'support'): ?>
                <section class="portal-panel full-panel" id="support">
                    <div class="panel-heading"><h2>Contact support</h2><span>We’ll follow up here</span></div>
                    <form class="portal-form" method="post" action="<?= site_url('portal/tickets') ?>"><?= csrf_field() ?><label class="form-wide">Subject<input name="subject" maxlength="180" value="<?= esc($support_subject) ?>" required placeholder="What can we help with?"></label><label class="form-wide">Message<textarea name="message" required placeholder="Describe your question or concern."></textarea></label><button type="submit">Send support request</button></form>
                    <?php if ($tickets): ?><div class="notice-list" style="margin-top:20px"><?php foreach ($tickets as $ticket): ?><article class="notice-item"><div class="notice-meta"><span class="status <?= esc($ticket['status']) ?>"><?= esc($ticket['status']) ?></span><span><?= esc(date('M j, Y', strtotime($ticket['created_at']))) ?></span></div><h3><?= esc($ticket['subject']) ?></h3><p><?= esc($ticket['message']) ?></p><?php if ($ticket['admin_reply']): ?><p><strong>Sun Son Solar:</strong> <?= esc($ticket['admin_reply']) ?></p><?php endif ?></article><?php endforeach ?></div><?php endif ?>
                </section>
                <?php endif ?>
            </div>
            <?php if ($section === 'profile'): ?><p class="legal-note">Your account details are only visible to authorized Sun Son Solar staff.</p><?php endif ?>
        </main>
    </div>
    <script src="<?= base_url('assets/js/portal.js') ?>" defer></script>
</body>
</html>