<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'price', 'rate', 'types', 'product_photo_path'
    ];

    protected $appends = [
        'product_photo_url',
    ];
}
