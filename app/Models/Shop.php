<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_domain',
        'access_token',
        'scope',
        'installed_at',
    ];

    protected $dates = [
        'installed_at',
        'created_at',
        'updated_at',
    ];

    public function products(){
        return $this->hasMany(Product::class);
    }

    public function productCollections(){
        return $this->hasMany(ProductCollection::class);
    }
}
