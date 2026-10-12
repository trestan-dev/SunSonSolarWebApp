<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'first_name',
        'last_name',
        'middle_name',
        'birthdate',
        'gender',
        'email',
        'phone_number',
        'address',
        'username',
        'password_hash',
        'role',
        'department',
    ];

    public function findByLogin(string $login): ?array
    {
        return $this->builder()
            ->groupStart()
            ->where('email', $login)
            ->orWhere('username', $login)
            ->groupEnd()
            ->get()
            ->getRowArray();
    }

    public function createUser(array $user): int|string|false
    {
        return $this->insert($user);
    }
}
