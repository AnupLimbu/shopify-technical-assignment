<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'shop_id',
        'shopify_id',
        'title',
        'body_html',
        'status',
        'published_at',
        'synced_at',
    ];

    protected $dates = [
        'published_at',
        'synced_at',
        'created_at',
        'updated_at',
    ];


    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
