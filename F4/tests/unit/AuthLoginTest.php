<?php

use CodeIgniter\Test\CIUnitTestCase;

final class AuthLoginTest extends CIUnitTestCase
{
    public function testAdminCredentialsMatchTheUsersTable(): void
    {
        $db = db_connect();

        $admin = $db->table('users')
            ->where('username', 'admin')
            ->get()
            ->getRowArray();

        $this->assertIsArray($admin);
        $this->assertTrue(
            password_verify('admin123', $admin['password_hash'])
            || hash_equals($admin['password_hash'], 'admin123')
        );

        $foundByEmail = $db->table('users')
            ->where('email', 'katherine.sinagaraw@sunsonsolar.com')
            ->get()
            ->getRowArray();

        $this->assertIsArray($foundByEmail);
        $this->assertTrue(
            password_verify('K@tSunShine16', $foundByEmail['password_hash'])
            || hash_equals($foundByEmail['password_hash'], 'K@tSunShine16')
        );

        $legacyUser = $db->table('users')
            ->where('username', 'tantan')
            ->get()
            ->getRowArray();

        $this->assertIsArray($legacyUser);
        $this->assertTrue(
            password_verify('tantan123', $legacyUser['password_hash'])
            || hash_equals($legacyUser['password_hash'], 'tantan123')
        );
    }
}
