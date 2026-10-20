<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCustomerFeedbackModeration extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('customer_stories')) {
            return;
        }

        $fields = [];
        if (!$this->db->fieldExists('customer_id', 'customer_stories')) {
            $fields['customer_id'] = ['type' => 'INT', 'unsigned' => true, 'null' => true];
        }
        if (!$this->db->fieldExists('booking_id', 'customer_stories')) {
            $fields['booking_id'] = ['type' => 'INT', 'unsigned' => true, 'null' => true];
        }
        if (!$this->db->fieldExists('moderation_status', 'customer_stories')) {
            $fields['moderation_status'] = ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'approved'];
        }
        if ($fields !== []) {
            $this->forge->addColumn('customer_stories', $fields);
        }

    }

    public function down()
    {
        if (!$this->db->tableExists('customer_stories')) {
            return;
        }

        foreach (['moderation_status', 'booking_id', 'customer_id'] as $field) {
            if ($this->db->fieldExists($field, 'customer_stories')) {
                $this->forge->dropColumn('customer_stories', $field);
            }
        }
    }
}
