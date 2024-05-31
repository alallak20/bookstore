<?php

namespace App\Models;

use CodeIgniter\Model;

class OrdersModel extends Model
{
    protected $table            = 'orders';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['user_id', 'total', 'address'];

    // Add order.
    // public function addOrder($order_data) {
    //     $this->insert($order_data);
    //     retu
    // }
}
