<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;


class Orders extends Migration
{
    public function up()
    {
        // $db = \Config\Database::connect(); // Get the database connection
        // $db->query('SET FOREIGN_KEY_CHECKS=0');

        // Fields.
        $fields = [
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => '100',
                'unsigned' => true,
                'null' => false,
            ],
            'total' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'unsigned' => true,
                'null' => false,
            ],
            'address' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ]
        ];

        $this->forge->addPrimaryKey('id', 'orders');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addField($fields);
        $this->forge->createTable('orders');
        // $this->db->$forge->createTable('orders');

        // $db->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->forge->dropTable('orders');
    }
}
