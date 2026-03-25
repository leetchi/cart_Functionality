<?php

namespace App\Models;

use CodeIgniter\Model;

class AttributeValueModel extends Model
{
    protected $table = 'attribute_values';
    protected $primaryKey = 'id';
    protected $allowedFields = ['attribute_id', 'value'];
}
