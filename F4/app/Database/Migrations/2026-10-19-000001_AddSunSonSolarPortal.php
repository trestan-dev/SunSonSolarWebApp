<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSunSonSolarPortal extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('users')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'first_name' => ['type' => 'VARCHAR', 'constraint' => 100],
                'last_name' => ['type' => 'VARCHAR', 'constraint' => 100],
                'middle_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => ''],
                'birthdate' => ['type' => 'DATE', 'null' => true],
                'gender' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => ''],
                'email' => ['type' => 'VARCHAR', 'constraint' => 255],
                'phone_number' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => ''],
                'address' => ['type' => 'VARCHAR', 'constraint' => 255, 'default' => ''],
                'username' => ['type' => 'VARCHAR', 'constraint' => 100],
                'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
                'role' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'customer'],
                'department' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'job_title' => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => ''],
                'is_active' => ['type' => 'BOOLEAN', 'default' => true],
                'must_change_password' => ['type' => 'BOOLEAN', 'default' => false],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey('email');
            $this->forge->addUniqueKey('username');
            $this->forge->createTable('users');
        } else {
            if (!$this->db->fieldExists('is_active', 'users')) {
                $this->forge->addColumn('users', [
                    'is_active' => ['type' => 'BOOLEAN', 'default' => true],
                ]);
            }
            if (!$this->db->fieldExists('must_change_password', 'users')) {
                $this->forge->addColumn('users', [
                    'must_change_password' => ['type' => 'BOOLEAN', 'default' => false],
                ]);
            }
            if (!$this->db->fieldExists('job_title', 'users')) {
                $this->forge->addColumn('users', [
                    'job_title' => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => ''],
                ]);
            }
        }

        $tables = [
            'products' => [
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'name' => ['type' => 'VARCHAR', 'constraint' => 140],
                'category' => ['type' => 'VARCHAR', 'constraint' => 80],
                'description' => ['type' => 'TEXT', 'null' => true],
                'price' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'is_active' => ['type' => 'BOOLEAN', 'default' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ],
            'services' => [
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'name' => ['type' => 'VARCHAR', 'constraint' => 140],
                'category' => ['type' => 'VARCHAR', 'constraint' => 80],
                'description' => ['type' => 'TEXT', 'null' => true],
                'price' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'availability' => ['type' => 'VARCHAR', 'constraint' => 180, 'default' => 'By appointment'],
                'is_active' => ['type' => 'BOOLEAN', 'default' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ],
            'bookings' => [
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'customer_id' => ['type' => 'INT', 'unsigned' => true],
                'service_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'technician_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'scheduled_at' => ['type' => 'DATETIME'],
                'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'pending'],
                'customer_notes' => ['type' => 'TEXT', 'null' => true],
                'admin_notes' => ['type' => 'TEXT', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ],
            'notifications' => [
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'audience' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'customers'],
                'type' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'update'],
                'title' => ['type' => 'VARCHAR', 'constraint' => 160],
                'message' => ['type' => 'TEXT'],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ],
            'support_tickets' => [
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'customer_id' => ['type' => 'INT', 'unsigned' => true],
                'subject' => ['type' => 'VARCHAR', 'constraint' => 180],
                'message' => ['type' => 'TEXT'],
                'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'open'],
                'admin_reply' => ['type' => 'TEXT', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ],
            'payments' => [
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'booking_id' => ['type' => 'INT', 'unsigned' => true],
                'customer_id' => ['type' => 'INT', 'unsigned' => true],
                'amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'pending'],
                'receipt_number' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
                'paid_at' => ['type' => 'DATETIME', 'null' => true],
            ],
            'customer_preferences' => [
                'user_id' => ['type' => 'INT', 'unsigned' => true],
                'service_notifications' => ['type' => 'BOOLEAN', 'default' => true],
                'promotional_notifications' => ['type' => 'BOOLEAN', 'default' => true],
            ],
            'attendance_checkins' => [
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'employee_id' => ['type' => 'INT', 'unsigned' => true],
                'booking_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'latitude' => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
                'longitude' => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
                'checked_in_at' => ['type' => 'DATETIME'],
            ],
            'service_reports' => [
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'booking_id' => ['type' => 'INT', 'unsigned' => true],
                'employee_id' => ['type' => 'INT', 'unsigned' => true],
                'issue' => ['type' => 'VARCHAR', 'constraint' => 180],
                'details' => ['type' => 'TEXT'],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ],
        ];

        foreach ($tables as $name => $fields) {
            if ($this->db->tableExists($name)) {
                continue;
            }

            $this->forge->addField($fields);
            if ($name === 'customer_preferences') {
                $this->forge->addPrimaryKey('user_id');
            } else {
                $this->forge->addPrimaryKey('id');
            }
            $this->forge->createTable($name);
        }

        $this->seedCatalog('products', [
            ['name' => 'Solar panels', 'category' => 'Panels', 'description' => 'High-efficiency photovoltaic panels for homes and businesses.'],
            ['name' => 'Solar inverters', 'category' => 'Inverters', 'description' => 'Reliable conversion and management for solar energy systems.'],
            ['name' => 'Energy storage', 'category' => 'Batteries', 'description' => 'Battery storage options to keep essential loads powered.'],
            ['name' => 'Mounting and wiring', 'category' => 'Balance of system', 'description' => 'Racking, mounting hardware, and solar-rated wiring.'],
        ]);
        $this->seedCatalog('services', [
            ['name' => 'Solar consultation', 'category' => 'Consultation', 'description' => 'Discuss your site, energy use, and solar options.'],
            ['name' => 'System design and permitting', 'category' => 'Design', 'description' => 'Plan a system and coordinate the required permits.'],
            ['name' => 'Solar installation', 'category' => 'Installation', 'description' => 'Professional installation and system commissioning.'],
            ['name' => 'Maintenance and repair', 'category' => 'Maintenance', 'description' => 'Keep your solar equipment performing as expected.'],
            ['name' => 'System monitoring', 'category' => 'Monitoring', 'description' => 'Review production and identify performance issues.'],
        ]);
    }

    public function down()
    {
        foreach (['service_reports', 'attendance_checkins', 'customer_preferences', 'payments', 'support_tickets', 'notifications', 'bookings', 'services', 'products'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        if ($this->db->tableExists('users')) {
            foreach (['job_title', 'must_change_password', 'is_active'] as $field) {
                if ($this->db->fieldExists($field, 'users')) {
                    $this->forge->dropColumn('users', $field);
                }
            }
        }
    }

    private function seedCatalog(string $table, array $items): void
    {
        if ($this->db->table($table)->countAllResults() > 0) {
            return;
        }

        foreach ($items as $item) {
            $this->db->table($table)->insert($item + [
                'price' => 0,
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}