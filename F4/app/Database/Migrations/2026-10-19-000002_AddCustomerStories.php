<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCustomerStories extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('customer_stories')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'customer_display' => ['type' => 'VARCHAR', 'constraint' => 100],
            'service_name' => ['type' => 'VARCHAR', 'constraint' => 140],
            'quote' => ['type' => 'TEXT'],
            'is_active' => ['type' => 'BOOLEAN', 'default' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('customer_stories');
    }

    public function down()
    {
        if ($this->db->tableExists('customer_stories')) {
            $this->forge->dropTable('customer_stories', true);
        }
    }
}