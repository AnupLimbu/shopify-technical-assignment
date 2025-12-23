<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCollection extends Model
{
    use HasFactory;
    protected $fillable = [
        'shop_id',
        'title',
        'products_count',
        'synced_at',
    ];

    protected $dates = [
        'synced_at',
        'created_at',
        'updated_at',
    ];

    public function shop(){
        return $this->belongsTo(Shop::class);
    }
}
