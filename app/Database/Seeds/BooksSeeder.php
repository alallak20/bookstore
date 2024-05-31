<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BooksSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'title'    => 'To Kill a Mockingbird 2',
                'author'   => 'Harper Lee',
                'genre'    => 'Fiction',
                'quantity' => 10,
                'price'    => 15.99,
            ],
            [
                'title'    => '1984 A',
                'author'   => 'George Orwell',
                'genre'    => 'Dystopian',
                'quantity' => 8,
                'price'    => 12.99,
            ],
            [
                'title'    => 'The Great Gatsby 2',
                'author'   => 'F. Scott Fitzgerald',
                'genre'    => 'Classic',
                'quantity' => 5,
                'price'    => 10.99,
            ],
            [
                'title'    => 'The Catcher in the Rye 2',
                'author'   => 'J.D. Salinger',
                'genre'    => 'Classic',
                'quantity' => 7,
                'price'    => 14.99,
            ],
            [
                'title'    => 'Moby-Dick 2',
                'author'   => 'Herman Melville',
                'genre'    => 'Adventure',
                'quantity' => 3,
                'price'    => 18.50,
            ],
            [
                'title'    => 'Pride and Prejudice 2',
                'author'   => 'Jane Austen',
                'genre'    => 'Romance',
                'quantity' => 10,
                'price'    => 11.99,
            ],
            [
                'title'    => 'The Hobbit 2',
                'author'   => 'J.R.R. Tolkien',
                'genre'    => 'Fantasy',
                'quantity' => 6,
                'price'    => 13.99,
            ],
            [
                'title'    => 'Harry Potter and the Sorcerer\'s Stone 2',
                'author'   => 'J.K. Rowling',
                'genre'    => 'Fantasy',
                'quantity' => 15,
                'price'    => 9.99,
            ],
            [
                'title'    => 'The Lord of the Rings 2',
                'author'   => 'J.R.R. Tolkien',
                'genre'    => 'Fantasy',
                'quantity' => 4,
                'price'    => 25.99,
            ],
            [
                'title'    => 'The Da Vinci Code 2',
                'author'   => 'Dan Brown',
                'genre'    => 'Thriller',
                'quantity' => 8,
                'price'    => 14.50,
            ],
        ];

        // Using Query Builder for multiple rows
        $this->db->table('books')->insertBatch($data);
    }
}
