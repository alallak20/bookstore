<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class OrderItems extends Migration
{
    public function up()
    {
        $forge = \Config\Database::forge();

        // Fields.
        $fields = [
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
                'unsigned' => true,
            ],
            'order_id' => [
                'type' => 'INT',
                'constraint' => '100',
                'unsigned' => true,
                'null' => false,
            ],
            'item_id' => [
                'type' => 'INT',
                'constraint' => '100',
                'unsigned' => true,
                'null' => false,
            ],
            'quantity' => [
                'type' => 'INT',
                'constraint' => '30',
                'unsigned' => true,
                'null' => false,
            ],
        ];

        $forge->addPrimaryKey('id', 'orderItems');
        $forge->addForeignKey('order_id', 'orders', 'id', 'CASCADE', 'CASCADE');
        $forge->addForeignKey('item_id', 'books', 'id', 'CASCADE', 'CASCADE');
        $forge->addField($fields);
        $forge->createTable('orderItems');
    }

    public function down()
    {
        //
    }
}
