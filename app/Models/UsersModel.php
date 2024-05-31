<?php

namespace App\Models;

use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Model;

class UsersModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['name', 'email', 'password'];


    //  Search for user email.
    public function userByEmail($email)
    {
        try {
            $email = $this->select('email')->where('email', $email)->find();
            return $email;
        } catch (DataException $e) {
            return $e->getMessage();
        }
    }

    // Search for user details by email.
    public function userDetailsByEmail($email)
    {
        $email = $this->select('*')->where('email', $email)->find();
        return $email;
    }
}
