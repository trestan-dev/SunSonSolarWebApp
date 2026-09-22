<?php
declare(strict_types=1);

require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request.', 405);
}

$accountType = trim((string)($_POST['account_type'] ?? 'customer'));
$fields = ['first_name', 'last_name', 'middle_name', 'birthdate', 'gender', 'email', 'phone_number', 'address', 'username', 'password'];
$values = [];

foreach ($fields as $field) {
    $values[$field] = trim((string)($_POST[$field] ?? ''));
    if ($values[$field] === '') {
        jsonResponse(false, 'Please complete all fields.', 422);
    }
}

if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Please enter a valid email address.', 422);
}

if (strlen($values['password']) < 8) {
    jsonResponse(false, 'Password must contain at least 8 characters.', 422);
}

$role = 'customer';
$department = null;
if ($accountType === 'employee') {
    $inviteCode = (string)getenv('SUNSON_EMPLOYEE_INVITE_CODE');
    $department = trim((string)($_POST['department'] ?? ''));
    $submittedCode = (string)($_POST['employee_code'] ?? '');

    if ($inviteCode === '' || !hash_equals($inviteCode, $submittedCode)) {
        jsonResponse(false, 'Employee registration requires a valid invite code.', 403);
    }
    if ($department === '') {
        jsonResponse(false, 'Please enter your department.', 422);
    }
    $role = 'employee';
} elseif ($accountType !== 'customer') {
    jsonResponse(false, 'Invalid account type.', 422);
}

$passwordHash = password_hash($values['password'], PASSWORD_DEFAULT);

try {
    $statement = $pdo->prepare(
        'INSERT INTO users (first_name, last_name, middle_name, birthdate, gender, email, phone_number, address, username, password_hash, role, department)
         VALUES (:first_name, :last_name, :middle_name, :birthdate, :gender, :email, :phone_number, :address, :username, :password_hash, :role, :department)'
    );
    $statement->execute([
        ':first_name' => $values['first_name'],
        ':last_name' => $values['last_name'],
        ':middle_name' => $values['middle_name'],
        ':birthdate' => $values['birthdate'],
        ':gender' => $values['gender'],
        ':email' => $values['email'],
        ':phone_number' => $values['phone_number'],
        ':address' => $values['address'],
        ':username' => $values['username'],
        ':password_hash' => $passwordHash,
        ':role' => $role,
        ':department' => $department
    ]);
} catch (PDOException $error) {
    if ($error->errorInfo[1] === 1062) {
        jsonResponse(false, 'That email or username is already registered.', 409);
    }
    jsonResponse(false, 'Unable to create the account.', 500);
}

jsonResponse(true, 'Account created.');