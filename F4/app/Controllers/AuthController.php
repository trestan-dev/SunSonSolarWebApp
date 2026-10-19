<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    public function employeeRegistration()
    {
        return redirect()->to('login');
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

        $user = (new UserModel())->findByLogin($login);

        $storedPassword = (string) ($user['password_hash'] ?? '');
        $isValidPassword = $storedPassword !== '' && password_verify($password, $storedPassword);
        if ($user && !$isValidPassword && $storedPassword !== ''
            && password_get_info($storedPassword)['algoName'] === 'unknown'
            && hash_equals($storedPassword, $password)) {
            $storedPassword = password_hash($password, PASSWORD_DEFAULT);
            (new UserModel())->update($user['id'], ['password_hash' => $storedPassword]);
            $isValidPassword = true;
        }

        if (!$user || !$isValidPassword || !(bool) ($user['is_active'] ?? true)) {
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
            'user_department' => $user['department'] ?? null,
            'must_change_password' => (bool) ($user['must_change_password'] ?? false),
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

        $accountType = trim((string) ($this->request->getPost('account_type') ?? 'customer'));
        if ($accountType !== 'customer') {
            return $this->response
                ->setJSON(['message' => 'Only customer accounts can register here.'])
                ->setStatusCode(403);
        }

        $fields = ['first_name', 'last_name', 'middle_name', 'birthdate', 'gender', 'email', 'phone_number', 'address', 'username', 'password'];
        $values = [];

        foreach ($fields as $field) {
            $rawValue = $this->request->getPost($field);
            $values[$field] = $field === 'password'
                ? (string) ($rawValue ?? '')
                : trim((string) ($rawValue ?? ''));
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

        $userId = (new UserModel())->createUser([
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
            'role' => 'customer',
            'department' => null,
            'is_active' => true,
            'must_change_password' => false,
        ]);

        if ($userId === false) {
            return $this->response
                ->setJSON(['message' => 'Unable to create the account.'])
                ->setStatusCode(500);
        }

        session()->regenerate();
        session()->set([
            'user_id' => $userId,
            'user_name' => trim($values['first_name'] . ' ' . $values['last_name']),
            'user_role' => 'customer',
            'must_change_password' => false,
        ]);

        return $this->response->setJSON([
            'message' => 'Account created.',
            'redirect' => site_url('home'),
        ]);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('login');
    }
}
