<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
// Time.
use CodeIgniter\Database\RawSql;


class Books extends Migration
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
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'unique'     => true,
                'null' => false,
            ],
            'author' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
            ],
            'genre' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
            ],
            'price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'unsigned' => true,
                'null' => false,
            ],
            'quantity' => [
                'type' => 'INT',
                'constraint' => '25',
                'unsigned' => true,
                'default' => 0
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ]
        ];
        $forge->addPrimaryKey('id', 'books');
        $forge->addField($fields);
        $forge->createTable('books');
    }

    public function down()
    {
        //
    }
}
