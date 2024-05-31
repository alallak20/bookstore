<?php

namespace App\Controllers;

use App\Controllers\BaseController;

use App\Models\BooksModel;
use App\Models\OrdersModel;
use App\Models\OrderItemsModel;

use CodeIgniter\Database\Exceptions\DataException;

class Carts extends BaseController
{
    private $booksModel;
    private $ordersModel;
    private $orderItemsModel;
    // private $cartItemsModel;
    public function __construct()
    {
        // Initialize models needed.
        $this->booksModel = new BooksModel();
        $this->ordersModel = new OrdersModel();
        $this->orderItemsModel = new OrderItemsModel();
    }

    // Add item to cart.
    public function addItem()
    {
        // Fetch data.
        $itemData = $this->request->getRawInput();

        // Set the validation rules.
        $rules = [
            'title'   => 'required',
            'quantity' => 'required|integer'
        ];
        if (!$this->validateData($itemData, $rules)) {
            return $this->response->setJSON(['Error' => $this->validator->getErrors()], 500);
        }
        // Fetch book details.
        try {
            $book_details = $this->booksModel->bookByTitle2Cart($itemData['title']);
            // Check if the book exist in DB.
            if (empty($book_details)) {
                return $this->response->setJSON(['Field' => 'Book is not available'], 404);
            }
        } catch (DataException $e) {
            return $this->response->setJSON(['Error' => $e->getMessage()], 500);
        }

        // Check if the quantity is not enough.
        if($itemData['quantity'] > $book_details[0]['quantity']) {
            return $this->response->setJSON(['Field', 'Sorry, Only ' . $book_details[0]['quantity'] . ' left in the store'], 500);
        }
        
        // Prepare data variables.
        $book_id = $book_details[0]['id'];
        $book_price = $book_details[0]['price'];

        // Get existing cart from session
        $cart = $this->session->get('cart') ?? [];

        // Check if the book is already in the cart
        if (isset($cart[$book_id])) {
            $cart[$book_id]['quantity'] += $itemData['quantity'];
        } else {
            // Add new book to the cart
            $cart[$book_id] = [
                'quantity' => $itemData['quantity'],
                'price' => $book_price
            ];
        }

        // Save cart back to session.
        $this->session->set('cart', $cart);

        return $this->response->setJSON(['Success' => 'Book added to cart successfully'], 200);
    }

    // Remove item from cart.
    public function removeItem($id)
    {
        // Fetch session cart.
        try {
            $cart = $this->session->get('cart');
        } catch (DataException $e) {
            // Handle session access error.
            return $this->response->setJSON(['Error' => $e->getMessage()], 500);
        }

        // Check if item exists in cart (more efficient approach for large carts)
        if (!array_key_exists($id, $cart)) {
            return $this->response->setJSON(['Field' => 'Book already not in cart'], 404);
        }

        // Remove item from cart
        unset($cart[$id]);

        // Set the new session data
        try {
            $this->session->set('cart', $cart);
        } catch (DataException $e) {
            // Handle session save error .
            return $this->response->setJSON(['Error' => $e->getMessage()], 500);
        }

        return $this->response->setJSON(['Success' => 'Book removed successfully'], 200);
    }


    // Retrieve cart items.
    public function viewCart()
    {
        // Get cart from session.
        $cart = $this->session->get('cart') ?? [];

        // Make sure cart not empty.
        if (!empty($cart)) {
            return $this->response->setJSON(['Success' => $cart], 200);

            // In case cart empty let the user know.
        } else {
            return $this->response->setJSON(['Field' => 'Cart is empty'], 404);
        }
        // Handel session errors.

    }

    // Checkout - place client order.
    public function checkout()
    {
        // Fetch data.
        $address_response = $this->request->getRawInput();
        // Check if the address is empty.
        if (empty($address_response)) {
            return $this->response->setJSON(['Field' => 'Address is empty'], 404);
        }

        $address = $address_response['address'];

        // Fetch user from DB.
        try {
            $user = $this->session->get('user_info');
        } catch (DataException $e) {
            return $this->response->setJSON(['Error' => $e->getMessage()], 500);
        }

        $user_id = $user['id'];

        // Get cart content.
        try {
            $cart = $this->session->get('cart');

            // Check if the cart is empty.
            if (empty($cart)) {
                return $this->response->setJSON(['Field' => 'Cart is empty'], 404);
            }
        } catch (DataException $e) {
            return $this->response->setJSON(['Error' => $e->getMessage()], 500);
        }

        // Calculate total amount.
        $totalAmount = array_reduce($cart, function ($sum, $item) {
            return $sum + ($item['quantity'] * $item['price']);
        }, 0);

        // Prepare order data.
        $orderData = [
            'user_id' => $user_id,
            'total' => ($totalAmount),
            'address' => $address
        ];

        // Make new order & retrieve it ID. 
        try {
            $order_ID = $this->ordersModel->insert($orderData);
            if (!$order_ID) {
                return $this->response->setJSON(['Field' => 'Order insertion failed'], 500);
            }
        } catch (DataException $e) {
            return $this->response->setJSON(['Error' => $e->getMessage()], 500);
        }

        // Prepare data for batch insert.
        $orderItems = [];
        foreach ($cart as $item_id => $details) {
            // Initialize an array to store items details.
            $orderItems[] = [
                'order_id' => $order_ID,
                'item_id' => $item_id,
                'quantity' => (int)$details['quantity']
            ];
        }

        // Insert all items in one command.
        try {
            $this->orderItemsModel->insertBatch($orderItems);
        } catch (DataException $e) {
            return $this->response->setJSON(['Error' => $e->getMessage()], 500);
        }

        // Update the availability status of books after an order is placed.
        foreach ($cart as $item_id => $details) {

            // Fetch the current quantity in DB.
            try {
                $quantity = $this->booksModel->getBookQuantity($item_id);

                // Calculate the new quantity.
                $new_quantity =  $quantity['quantity'] - $details['quantity'];

                // Save it.
                try {
                    $this->booksModel->updateQuantity($item_id, $new_quantity);
                } catch (DataException $e) {
                    return $this->response->setJSON(['Error' => $e->getMessage()], 500);
                }
            } catch (DataException $e) {
                return $this->response->setJSON(['Error' => $e->getMessage()], 500);
            }
        }

        // Clear the cart after checkout.
        $this->session->remove('cart');

        return $this->response->setJSON(['Success' => 'Order is placed successfully'], 200);
    }
}
