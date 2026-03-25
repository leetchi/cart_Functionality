<?php

namespace App\Models;

use CodeIgniter\Model;

class CartItemModel extends Model
{
    protected $table = 'cart_items';
    protected $primaryKey = 'id';
    protected $allowedFields = ['session_id', 'product_id', 'variant_id', 'quantity', 'price_each'];
}
