<?php

namespace App\Models;

use CodeIgniter\Model;

class ConstModel extends Model
{
    protected $table = 'const';
    protected $returnType = 'object';
    protected $primaryKey = 'rkey';
    protected $allowedFields = ['rkey', 'nilai'];
}
