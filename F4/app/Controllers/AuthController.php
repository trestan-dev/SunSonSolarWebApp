<?php

namespace App\Controllers;

class AuthController extends BaseController
{
    public function employeeRegistration(): string
    {
        return view('auth/employee_registration');
    }

    public function loginPage(): string
    {
        return view('auth/login');
    }

    public function registrationPage(): string
    {
        return view('auth/registration');
    }

    public function handleLogin()
    {
        if (!$this->request->is('post')) {
            return $this->response
                ->setJSON(['message' => 'Invalid request.'])
                ->setStatusCode(405);
        }

        $login = trim((string) $this->request->getPost('login'));
        $password = (string) $this->request->getPost('password');

        if ($login === '' || $password === '') {
            return $this->response
                ->setJSON(['message' => 'Enter your email or username and password.'])
                ->setStatusCode(422);
        }

        $db = db_connect();
        $user = $db->table('users')
            ->where('email', $login)
            ->orWhere('username', $login)
            ->get()
            ->getRowArray();

        $storedPassword = (string) ($user['password_hash'] ?? $user['password'] ?? '');
        $isValidPassword = $storedPassword !== '' && (
            password_verify($password, $storedPassword)
            || hash_equals($storedPassword, $password)
        );

        if (!$user || !$isValidPassword) {
            return $this->response
                ->setJSON(['message' => 'Email, username, or password is incorrect.'])
                ->setStatusCode(401);
        }

        $fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        if ($fullName === '') {
            $fullName = $user['username'] ?? $login;
        }

        session()->regenerate();
        session()->set([
            'user_id' => $user['id'],
            'user_name' => $fullName,
            'user_role' => $user['role'] ?? 'customer',
        ]);

        return $this->response->setJSON([
            'message' => 'Login successful.',
            'redirect' => site_url('home'),
        ]);
    }

    public function handleRegister()
    {
        if (!$this->request->is('post')) {
            return $this->response
                ->setJSON(['message' => 'Invalid request.'])
                ->setStatusCode(405);
        }

        $accountType = trim((string) $this->request->getPost('account_type', 'customer'));
        $fields = ['first_name', 'last_name', 'middle_name', 'birthdate', 'gender', 'email', 'phone_number', 'address', 'username', 'password'];
        $values = [];

        foreach ($fields as $field) {
            $values[$field] = trim((string) $this->request->getPost($field, ''));
            if ($values[$field] === '') {
                return $this->response
                    ->setJSON(['message' => 'Please complete all fields.'])
                    ->setStatusCode(422);
            }
        }

        if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->response
                ->setJSON(['message' => 'Please enter a valid email address.'])
                ->setStatusCode(422);
        }

        if (strlen($values['password']) < 8) {
            return $this->response
                ->setJSON(['message' => 'Password must contain at least 8 characters.'])
                ->setStatusCode(422);
        }

        $role = 'customer';
        $department = null;

        if ($accountType === 'employee') {
            $inviteCode = trim((string) getenv('SUNSON_EMPLOYEE_INVITE_CODE'));
            $submittedCode = trim((string) $this->request->getPost('employee_code', ''));
            $department = trim((string) $this->request->getPost('department', ''));

            if ($inviteCode === '' || !hash_equals($inviteCode, $submittedCode)) {
                return $this->response
                    ->setJSON(['message' => 'Employee registration requires a valid invite code.'])
                    ->setStatusCode(403);
            }

            if ($department === '') {
                return $this->response
                    ->setJSON(['message' => 'Please enter your department.'])
                    ->setStatusCode(422);
            }

            $role = 'employee';
        } elseif ($accountType !== 'customer') {
            return $this->response
                ->setJSON(['message' => 'Invalid account type.'])
                ->setStatusCode(422);
        }

        $db = db_connect();
        $inserted = $db->table('users')->insert([
            'first_name' => $values['first_name'],
            'last_name' => $values['last_name'],
            'middle_name' => $values['middle_name'],
            'birthdate' => $values['birthdate'],
            'gender' => $values['gender'],
            'email' => $values['email'],
            'phone_number' => $values['phone_number'],
            'address' => $values['address'],
            'username' => $values['username'],
            'password_hash' => password_hash($values['password'], PASSWORD_DEFAULT),
            'role' => $role,
            'department' => $department,
        ]);

        if (!$inserted) {
            return $this->response
                ->setJSON(['message' => 'Unable to create the account.'])
                ->setStatusCode(500);
        }

        session()->set([
            'user_id' => $db->insertID(),
            'user_name' => trim($values['first_name'] . ' ' . $values['last_name']),
            'user_role' => $role,
        ]);

        return $this->response->setJSON([
            'message' => 'Account created.',
            'redirect' => site_url('login'),
        ]);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('login');
    }
}
