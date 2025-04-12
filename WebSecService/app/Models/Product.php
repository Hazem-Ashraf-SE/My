<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model  {

	protected $fillable = [
        'code',
        'name',
        'price',
        'model',
        'description',
        'photo',
        'in_stock',
        'quantity'
    ];

    public function purchases()
    {
        return $this->belongsToMany(User::class, 'purchases', 'product_id', 'user_id');
    }
}