<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingWay extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 
        'shipping_method', 
        'freight_rate', 
        'type'
    ];

}
