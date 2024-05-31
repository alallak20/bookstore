<?php

namespace App\Controllers;

use App\Models\BooksModel;
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\HTTP\ResponseInterface;


class Books extends BaseController
{
    private $model;
    public function __construct()
    {
        $this->model = new BooksModel();
    }

    // Test function.
    public function index()
    {
        echo (json_encode("Testing only", JSON_PRETTY_PRINT));
    }

    // Fetches all books.
    public function allBooks()
    {
        try {
            // Fetch data from DB.
            $books = $this->model->findAll();
            // Send the data as JSON format.
            return $this->response->setJSON($books, JSON_PRETTY_PRINT);
        } catch (DataException $e) {
            return $this->response->setJSON(['Error', $e->getMessage()], 500);
        }
    }

    // Fetch single book.
    public function singleBook($id)
    {
        try {
            // Checks if no match found.
            $book = $this->model->find($id);
            if (empty($book)) {
                return $this->response->setJSON(['Error' => 'Book not found'], 404); // HTTP 404 not found.
            }
            // HTTP 200 success.
            return $this->response->setJSON([$book, JSON_PRETTY_PRINT], 200);
        } catch (DataException $e) {
            return $this->response->setJSON(['Error', $e->getMessage()], 500);
        }
    }

    // Add new book.
    public function add()
    {
        // Get POST data.
        $data = $this->request->getRawInput();
        $title = trim($_POST['title']);

        // Set the validation rules.
        $rules = [
            'title'   => 'required',
            'author' => 'required|regex_match[/^[a-zA-Z\s\.]+$/]',
            'price' => 'required|decimal',
            'genre' => 'required',
            'quantity' => 'required|integer'
        ];

        try {
            // Check for duplicates.
            if ($this->model->bookByTitle($title) !== []) {
                return $this->response->setJSON(['Failed' => 'Book is already exist'], 400);
            }
        } catch (DataException $e) {
            return $this->response->setJSON(['Error', $e->getMessage()], 500);
        }

        // Check if something went wrong.
        if (!$this->validateData($data, $rules)) {
            return $this->response->setJSON(['Error' => $this->validator->getErrors()]);
        }

        // Insert into DB if its all good.
        try {
            $this->model->insert($data);
            return $this->response->setJSON(['Success' => 'Book is inserted successfully'], 201);
        } catch (DataException $e) {
            return $this->response->setJSON(['Error', $e->getMessage()], 500);
        }
    }

    // Update book.
    public function update($id)
    {
        // Fetch PUT data.
        $data = $this->request->getRawInput();

        // Set the validation rules.
        $rules = [
            'title' => 'required',
            'author' => 'required|regex_match[/^[a-zA-Z\s\.]+$/]',
            'price' => 'required|decimal',
            'genre' => 'required',
            'quantity' => 'required|integer'
        ];

        // Check if something went wrong.
        if (!$this->validateData($data, $rules)) {
            return $this->response->setJSON(['Error' => $this->validator->getErrors()], 500);
        }

        // Insert into DB if all's good.
        try {
            $this->model->update($id, $data);
            return $this->response->setJSON(['Success' => 'Book is updated successfully'], 200);
        } catch (DataException $e) {
            return $this->response->setJSON(['Error', $e->getMessage()], 500);
        }
    }

    // Delete a book.
    public function destroy($id)
    {
        // For some reason (Delete) method always returns 1
        // so we are checking if book is exist first.
        try {
            // Check for book existents.
            if ($this->model->find($id)) {
                // If so, delete it.
                try {
                    $this->model->delete($id);
                    return $this->response->setJSON(['Success' => 'Book is deleted successfully'], 200);
                } catch (DataException $e) {
                    return $this->response->setJSON(['Error' => $e->getMessage()], 500);
                }
            } else {
                return $this->response->setJSON(['Field' => 'Book is not available to be deleted'], 404);
            }
        } catch (DataException $e) {
            return $this->response->setJSON(['Error' => $e->getMessage()], 500);
        }
    }

    // Book Availability.
    public function bookAvailability(int $id)
    {
        // Fetch quantity from DB.
        try {
            $quantity = $this->model->getBookQuantity($id);
            // Incase the book never was available in the store.
            if (empty($quantity)) {
                return $this->response->setJSON(['Failed' => 'Book is not available in the store'], 404);
                // Book does exist in books table but quantity equals 0.
            } elseif ($quantity['quantity'] == 0) {
                return $this->response->setJSON(['Failed' => 'Book is currently! not available in the store'], 404);
            } else {
                // Book is available.
                return $this->response->setJSON(['Success' => 'Book is available in the store'], 200);
            }
        } catch (DataException $e) {

            return $this->response->setJSON(['Field' => $e->getMessage()], 500);
        }
    }


    // Update quantity status.
    public function updateBookQuantity($id)
    {
        try {
            // Check if the book exists.
            if (!$this->model->find($id)) {
                return $this->response->setJSON(['Field' => 'Book not found'], 404);
            }
            // Fetch PUT data.
            $quantity = $this->request->getRawInput();

            // Check if the new status is provided and it must be either 0 or 1.
            $rules = ['quantity' => 'required|integer'];
            // Validate the date.
            if (!$this->validateData($quantity, $rules)) { //All good? update.
                return $this->response->setJSON(['Error' => $this->validator->getErrors()]);
            } else {
                // Update the quantity.
                try {
                    if ($this->model->updateQuantity($id, $quantity)) {

                        return $this->response->setJSON(['Success' => 'Book availability is updated'], 200);
                    }
                } catch (DataException $e) {
                    return $this->response->setJSON(['Field' => $e->getMessage()], 500);
                }
            }
        } catch (DataException $e) {
            return $this->response->setJSON(['Field' => $e->getMessage()], 500);
        }
    }


    // Search.
    public function search($content)
    {
        /* 
        - Ensure that the search string in your URL is properly URL-encoded.
        - Spaces should be replaced with  (+).
        */

        // Decode the URL before passing it to search function.
        $content = urldecode($content);

        // Incase search content exist, start the search.
        if (!empty(trim($content))) {
            try {
                $result = $this->model->search($content);

                // Incase no match found.
                if (!$result) {
                    return $this->response->setJSON(['Field' => 'No match found'], 404);
                } else {
                    return $this->response->setJSON([$result, JSON_PRETTY_PRINT], 200);
                }
            } catch (DataException $e) {
                return $this->response->setJSON(['Field' => $e->getMessage()], 500);
            }
        }
    }
    // Search by criteria.
    public function searchBy($criteria = null)
    {
        // Trim the input.
        $criteria = trim($criteria);

        try {
            // Checks if the column exist.
            if (!$this->model->fieldExists($criteria, 'books')) {
                return $this->response->setJSON(['Error' => 'Criteria not found']);
            }

            try {
                $result = $this->model
                    ->select(['id', $criteria])
                    ->findAll();
                // Remove duplicate rows.
                $uniqueResults = array_unique($result, SORT_REGULAR);

                return $this->response->setJSON($uniqueResults, JSON_PRETTY_PRINT);
            } catch (DataException $e) {
                return $this->response->setJSON(['Error' => $e->getMessage()], 400);
            }

            // Error handling.
        } catch (DataException $e) {
            return $this->response->setJSON(['Error' => $e->getMessage()], 500);
        }
    }
}
