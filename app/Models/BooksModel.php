<?php

namespace App\Models;

use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Model;


class BooksModel extends Model
{
    protected $table            = 'books';
    protected $primaryKey       = 'id';
    protected $allowedFields = ['title', 'author', 'genre', 'price', 'quantity'];


    // Search book by title - Used for duplicates.
    public function bookByTitle($title)
    {
        $bookTitle = $this->select('title')->where('title', $title)->find();
        return $bookTitle;
    }

    // Search book by title - Used for ID and price retrieval in carts.
    public function bookByTitle2Cart($title)
    {
        $book = $this->select('id, price')->where('title', $title)->find();
        return $book;
    }

    // Fetch Book quantity to know the availability.
    public function getBookQuantity($id)
    {
        try {
            $book = $this->select('quantity')->find($id);
        } catch (DataException $e) {
            return $e->getMessage();
        }

        return $book;
    }

    // Update book quantity status.
    public function updateQuantity($id, $quantity)
    {
        try {
            $this->where('id', $id)
                ->set('quantity', $quantity) // Update with the single value.
                ->update();

            return ['Success', 'Quantity is updated successfully'];
        } catch (DataException $e) {
            return $e->getMessage();
        }
    }

    // Search books.
    public function search($content)
    {
        try {
            $result = $this->select(['id', 'title', 'author', 'genre'])
                ->like('title', $content, 'i')
                ->orLike('author', $content, 'i')
                ->orLike('genre', $content, 'i')
                ->findAll();

            return $result;
        } catch (DataException $e) {
            return $e->getMessage();
        }
    }
}
