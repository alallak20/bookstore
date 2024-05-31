<?php

namespace App\Controllers;

use App\Models\UsersModel;
use App\Controllers\BaseController;
use CodeIgniter\Database\Exceptions\DataException;

class Users extends BaseController
{
    private $model;
    public function __construct()
    {
        $this->model = new UsersModel();
    }

    // Register a new user.
    public function register()
    {
        // Fetch request data.
        $userData = $this->request->getRawInput();
        $rules = [
            'name' => 'required|min_length[2]|max_length[50]',
            'email' => 'required|valid_email',
            'password' => 'required|regex_match[^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d!@#$%^&*]{6,}$]',
            'confirm_password'  => 'matches[password]'
        ];

        // Validate data.
        if (!$this->validateData($userData, $rules)) {
            return $this->response->setJSON($this->validator->getErrors());
        }

        // Check email duplicate.
        if ($this->model->userByEmail($userData['email']) != []) {
            return $this->response->setJSON(['Failed' => 'Email is already taken'], 400);
            // Save if all good.
        }

        // Hash password before store it in DB.
        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);

        // Remove confirm_password from data.
        unset($userData['confirm_password']);

        // Store user.
        try {
            if ($this->model->insert($userData)) {
                return $this->response->setJSON(['Success' => 'User signed up successfully'], 201);
            }
        } catch (DataException $e) {
            return $this->response->setJSON(['Error', $e->getMessage()], 500);
        }
    }

    // Login User.
    public function login()
    {
        // Fetch request data.
        $userData = $this->request->getRawInput();
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required|regex_match[^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d!@#$%^&*]{6,}$]',
        ];

        try {
            // Validate data.
            if (!$this->validateData($userData, $rules)) {
                return $this->response->setJSON($this->validator->getErrors());
            }

            // Checks if email is exist.
            try {
                if (!empty($user = $this->model->userDetailsByEmail($userData['email']))) {
                    $user = $user[0];
                    $stored_pass = $user['password'];
                    $provided_pass = $userData['password'];

                    // Check password.
                    if (password_verify($provided_pass, $stored_pass)) {
                        // Create session for user.
                        $this->createUserSession($user);
                        $this->session->set('isLoggedIn', true);

                        return $this->response->setJSON(['Success' => 'User {' . $user['name'] . '} logged in successfully'], 200);
                    }
                } else {
                    return $this->response->setJSON(['Field' => 'User not found'], 404);
                }
            } catch (DataException $e) {
                return $this->response->setJSON(['Error' => $e->getMessage()], 500);
            }
        } catch (DataException $e) {
            return $this->response->setJSON(['Filed' => $e->getMessage()], 500);
        }
    }

    // Logout.
    public function logout()
    {
        try {
            // Destroy session data
            $this->session->destroy();

            return $this->response->setJSON(['Success' => 'User logged out successfully'], 200);
        } catch (DataException $e) {
            return $this->response->setJSON(['Filed' => $e->getMessage()], 500);
        }
    }


    // Create new users session.
    public function createUserSession($user)
    {
        try {
            $this->session->set('user_info', [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'logged_in' => true
            ]);
        } catch (DataException $e) {
            return $this->response->setJSON(['Filed' => $e->getMessage()], 500);
        }
    }
}
