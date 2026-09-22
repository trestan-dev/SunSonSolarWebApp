<?php
declare(strict_types=1);

require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request.', 405);
}

$login = trim((string)($_POST['login'] ?? ''));
$password = (string)($_POST['password'] ?? '');

if ($login === '' || $password === '') {
    jsonResponse(false, 'Enter your email or username and password.', 422);
}

$statement = $pdo->prepare(
    'SELECT id, email, password_hash, first_name, role
     FROM users
     WHERE email = :email OR username = :username
     LIMIT 1'
);
$statement->execute([':email' => $login, ':username' => $login]);
$user = $statement->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    jsonResponse(false, 'Email, username, or password is incorrect.', 401);
}

session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['first_name'];
$_SESSION['user_role'] = $user['role'];

jsonResponse(true, 'Login successful.');