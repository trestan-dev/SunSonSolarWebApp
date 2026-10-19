<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(?string $portalSection = null)
    {
        $db = db_connect();
        if (!session()->get('user_id')) {
            return view('landing', [
                'services' => $db->table('services')->where('is_active', 1)->get()->getResultArray(),
                'products' => $db->table('products')->where('is_active', 1)->get()->getResultArray(),
                'stories' => $db->table('customer_stories')->where('is_active', 1)->orderBy('id', 'DESC')->limit(3)->get()->getResultArray(),
                'news' => $db->table('notifications')->where('audience', 'public')->orderBy('id', 'DESC')->limit(3)->get()->getResultArray(),
            ]);
        }

        $userId = (int) session()->get('user_id');
        $user = $db->table('users')->where('id', $userId)->get()->getRowArray() ?? [];
        if (!$user || !(bool) ($user['is_active'] ?? true)) {
            session()->destroy();
            return redirect()->to('login');
        }

        $role = (string) ($user['role'] ?? 'customer');
        session()->set([
            'user_role' => $role,
            'user_department' => $user['department'] ?? null,
            'must_change_password' => (bool) ($user['must_change_password'] ?? false),
        ]);
        if (!empty($user['must_change_password'])) {
            return view('auth/force_password_change', [
                'name' => session()->get('user_name') ?? 'Employee',
                'message' => session()->getFlashdata('message'),
                'error' => session()->getFlashdata('error'),
            ]);
        }

        $data = [
            'name' => session()->get('user_name') ?? 'User',
            'role' => $role,
            'department' => (string) session()->get('user_department'),
            'job_title' => (string) ($user['job_title'] ?? ''),
            'profile' => $user,
            'message' => session()->getFlashdata('message'),
            'error' => session()->getFlashdata('error'),
            'services' => $db->table('services')->where('is_active', 1)->get()->getResultArray(),
        ];
        $requestedMonth = (string) $this->request->getGet('month');
        $data['calendar_month'] = preg_match('/^\d{4}-\d{2}$/', $requestedMonth)
            && checkdate((int) substr($requestedMonth, 5, 2), 1, (int) substr($requestedMonth, 0, 4))
                ? $requestedMonth
                : date('Y-m');

        if ($role === 'customer') {
            $customerSections = ['overview', 'schedule', 'products', 'notifications', 'history', 'profile', 'support'];
            if ($portalSection === null) {
                return redirect()->to('customer/overview');
            }
            if (!in_array($portalSection, $customerSections, true)) {
                return redirect()->to('customer/overview');
            }
            $data['customer_section'] = $portalSection;
            $data['support_subject'] = trim((string) $this->request->getGet('subject'));
            if ($portalSection === 'products') {
                $categories = $db->table('products')->distinct()->select('category')->where('is_active', 1)->orderBy('category')->get()->getResultArray();
                $data['product_categories'] = array_column($categories, 'category');
                $data['product_search'] = trim((string) $this->request->getGet('q'));
                $data['product_category'] = trim((string) $this->request->getGet('category'));
                $productQuery = $db->table('products')->where('is_active', 1);
                if ($data['product_search'] !== '') {
                    $productQuery->groupStart()
                        ->like('name', $data['product_search'])
                        ->orLike('description', $data['product_search'])
                        ->orLike('category', $data['product_search'])
                        ->groupEnd();
                }
                if (in_array($data['product_category'], $data['product_categories'], true)) {
                    $productQuery->where('category', $data['product_category']);
                } else {
                    $data['product_category'] = '';
                }
                $data['products'] = $productQuery->orderBy('category')->orderBy('name')->get()->getResultArray();
            }
            $data['bookings'] = $db->table('bookings b')
                ->select('b.*, s.name AS service_name, t.first_name AS technician_first, t.last_name AS technician_last')
                ->join('services s', 's.id = b.service_id', 'left')
                ->join('users t', 't.id = b.technician_id', 'left')
                ->where('b.customer_id', $userId)
                ->orderBy('b.scheduled_at', 'DESC')
                ->get()->getResultArray();
            $data['payments'] = $db->table('payments')->where('customer_id', $userId)->orderBy('id', 'DESC')->get()->getResultArray();
            $data['tickets'] = $db->table('support_tickets')->where('customer_id', $userId)->orderBy('id', 'DESC')->get()->getResultArray();
            $data['preferences'] = $db->table('customer_preferences')->where('user_id', $userId)->get()->getRowArray() ?? [];
            $notificationQuery = $db->table('notifications')
                ->groupStart()->where('user_id', $userId)->orGroupStart()->where('audience', 'customers')->orWhere('audience', 'all')->groupEnd()->groupEnd()
                ->orderBy('id', 'DESC');
            if (!empty($data['preferences']) && empty($data['preferences']['promotional_notifications'])) {
                $notificationQuery->where('type !=', 'promotion');
            }
            if (!empty($data['preferences']) && empty($data['preferences']['service_notifications'])) {
                $notificationQuery->where('type !=', 'reminder');
            }
            $data['notifications'] = $notificationQuery->limit(12)->get()->getResultArray();

            return view('dashboards/customer', $data);
        }

        if ($role === 'admin') {
            $adminSections = ['overview', 'schedule', 'users', 'services', 'reports', 'payments', 'attendance', 'stories', 'announcements', 'support', 'field-reports'];
            if ($portalSection === null || !in_array($portalSection, $adminSections, true)) {
                return redirect()->to('admin/overview');
            }
            $data['admin_section'] = $portalSection;
            $data['users'] = $db->table('users')->select('id, first_name, last_name, middle_name, birthdate, gender, email, phone_number, address, username, role, department, job_title, is_active, created_at')->orderBy('id', 'DESC')->get()->getResultArray();
            $data['technicians'] = $db->table('users')->where('role', 'employee')->whereIn('job_title', ['Technician', 'Head Technician'])->where('is_active', 1)->get()->getResultArray();
            $data['bookings'] = $this->bookingRows($db, null);
            $data['tickets'] = $db->table('support_tickets t')->select('t.*, u.first_name, u.last_name, u.email')->join('users u', 'u.id = t.customer_id')->orderBy('t.id', 'DESC')->get()->getResultArray();
            $data['reports'] = $db->table('service_reports r')->select('r.*, u.first_name, u.last_name, b.scheduled_at')->join('users u', 'u.id = r.employee_id')->join('bookings b', 'b.id = r.booking_id')->orderBy('r.id', 'DESC')->get()->getResultArray();
            $data['checkins'] = $db->table('attendance_checkins a')->select('a.*, u.first_name, u.last_name, b.scheduled_at, s.name AS service_name')->join('users u', 'u.id = a.employee_id')->join('bookings b', 'b.id = a.booking_id', 'left')->join('services s', 's.id = b.service_id', 'left')->orderBy('a.id', 'DESC')->limit(50)->get()->getResultArray();
            $data['services'] = $db->table('services')->orderBy('name')->get()->getResultArray();
            $data['monthly'] = $db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS total FROM bookings WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY month ORDER BY month")->getResultArray();
            $data['revenue'] = $db->table('payments')->selectSum('amount')->where('status', 'paid')->get()->getRowArray()['amount'] ?? 0;
            $data['service_usage'] = $db->query('SELECT s.name, COUNT(b.id) AS total FROM services s LEFT JOIN bookings b ON b.service_id = s.id GROUP BY s.id, s.name ORDER BY total DESC, s.name')->getResultArray();
            $data['payments'] = $db->table('payments p')->select('p.*, u.first_name, u.last_name, b.scheduled_at, s.name AS service_name')->join('users u', 'u.id = p.customer_id')->join('bookings b', 'b.id = p.booking_id')->join('services s', 's.id = b.service_id', 'left')->orderBy('p.id', 'DESC')->get()->getResultArray();
            $data['open_tickets'] = $db->table('support_tickets')->where('status', 'open')->countAllResults();
            $data['announcements'] = $db->table('notifications')->whereIn('audience', ['employees', 'all'])->orderBy('id', 'DESC')->limit(8)->get()->getResultArray();
            $data['stories'] = $db->table('customer_stories')->orderBy('id', 'DESC')->get()->getResultArray();

            return view('dashboards/admin', $data);
        }

        $department = strtolower((string) session()->get('user_department'));
        $isFieldTechnician = in_array(strtolower((string) ($user['job_title'] ?? '')), ['technician', 'head technician'], true);
        if ($portalSection === null) {
            return redirect()->to('employee/overview');
        }
        $employeeSections = ['overview', 'announcements', 'profile'];
        if ($department === 'hr') {
            $employeeSections[] = 'employees';
        }
        if ($department === 'dispatch') {
            $employeeSections[] = 'schedule';
        }
        if ($isFieldTechnician) {
            $employeeSections = array_merge($employeeSections, ['jobs', 'checkins', 'reports']);
        }
        if (!in_array($portalSection, $employeeSections, true)) {
            return redirect()->to('employee/overview');
        }
        $data['employee_section'] = $portalSection;
        $data['bookings'] = $department === 'dispatch'
            ? $this->bookingRows($db, null)
            : ($isFieldTechnician ? $this->bookingRows($db, $userId, false) : []);
        if ($department === 'dispatch') {
            $data['technicians'] = $db->table('users')->where('role', 'employee')->whereIn('job_title', ['Technician', 'Head Technician'])->where('is_active', 1)->get()->getResultArray();
        }
        if ($department === 'hr') {
            $data['employees'] = $db->table('users')->select('id, first_name, last_name, middle_name, birthdate, gender, email, phone_number, address, username, department, job_title, is_active')->where('role', 'employee')->orderBy('id', 'DESC')->get()->getResultArray();
        }
        $data['announcements'] = $db->table('notifications')
            ->groupStart()->where('user_id', $userId)->orGroupStart()->where('audience', 'employees')->orWhere('audience', 'all')->groupEnd()->groupEnd()
            ->orderBy('id', 'DESC')->limit(12)->get()->getResultArray();
        $data['checkins'] = $isFieldTechnician ? $db->table('attendance_checkins')->where('employee_id', $userId)->orderBy('id', 'DESC')->limit(10)->get()->getResultArray() : [];
        $data['reports'] = $isFieldTechnician ? $db->table('service_reports')->where('employee_id', $userId)->orderBy('id', 'DESC')->limit(10)->get()->getResultArray() : [];

        return view('dashboards/employee', $data);
    }

    private function bookingRows($db, ?int $technicianId, bool $includeCustomerPhone = true): array
    {
        $customerContact = $includeCustomerPhone ? ', c.phone_number AS customer_phone' : '';
        $bookingNotes = $includeCustomerPhone ? ', b.customer_notes' : '';
        $query = $db->table('bookings b')
            ->select('b.id, b.service_id, b.technician_id, b.scheduled_at, b.status, s.name AS service_name, c.first_name AS customer_first, c.last_name AS customer_last, c.address AS customer_address' . $customerContact . $bookingNotes . ', t.first_name AS technician_first, t.last_name AS technician_last')
            ->join('services s', 's.id = b.service_id', 'left')
            ->join('users c', 'c.id = b.customer_id')
            ->join('users t', 't.id = b.technician_id', 'left');

        if ($technicianId !== null) {
            $query->where('b.technician_id', $technicianId);
        }

        return $query->orderBy('b.scheduled_at', 'ASC')->get()->getResultArray();
    }
}
