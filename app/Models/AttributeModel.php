<?php

namespace App\Models;

use CodeIgniter\Model;

class AttributeModel extends Model
{
    protected $table = 'attributes';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'slug'];
}
