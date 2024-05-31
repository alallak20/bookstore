<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderItemsModel extends Model
{
    protected $table            = 'orderitems';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['order_id', 'quantity', 'item_id'];

}
