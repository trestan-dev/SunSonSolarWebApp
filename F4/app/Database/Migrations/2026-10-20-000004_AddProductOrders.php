<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProductOrders extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('orders')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'customer_id' => ['type' => 'INT', 'unsigned' => true],
                'delivery_staff_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'recipient_name' => ['type' => 'VARCHAR', 'constraint' => 150],
                'recipient_phone' => ['type' => 'VARCHAR', 'constraint' => 30],
                'delivery_address' => ['type' => 'VARCHAR', 'constraint' => 500],
                'delivery_notes' => ['type' => 'TEXT', 'null' => true],
                'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'pending'],
                'payment_status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'to_be_confirmed'],
                'total_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'tracking_code' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addKey(['customer_id', 'created_at']);
            $this->forge->addKey(['delivery_staff_id', 'status']);
            $this->forge->addKey(['status', 'created_at']);
            $this->forge->addForeignKey('customer_id', 'users', 'id', 'CASCADE', 'RESTRICT', 'fk_orders_customer');
            $this->forge->addForeignKey('delivery_staff_id', 'users', 'id', 'CASCADE', 'SET NULL', 'fk_orders_delivery_staff');
            $this->forge->createTable('orders');
        }

        if (!$this->db->tableExists('order_items')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'order_id' => ['type' => 'INT', 'unsigned' => true],
                'product_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'product_name' => ['type' => 'VARCHAR', 'constraint' => 140],
                'quantity' => ['type' => 'INT', 'unsigned' => true],
                'unit_price' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'line_total' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addKey('order_id');
            $this->forge->addKey('product_id');
            $this->forge->addForeignKey('order_id', 'orders', 'id', 'CASCADE', 'CASCADE', 'fk_order_items_order');
            $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'SET NULL', 'fk_order_items_product');
            $this->forge->createTable('order_items');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('order_items')) {
            $this->forge->dropTable('order_items', true);
        }
        if ($this->db->tableExists('orders')) {
            $this->forge->dropTable('orders', true);
        }
    }
}
