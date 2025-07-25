<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestItem extends Model
{
    use HasFactory;
    protected $fillable=['discount',
                'discount_type',
                'subtotal_wd',
                'subtotal_wd_tax',
                'tax',
                'item_notes',
                'supply_ref',
                'status',
                'sell_price_wot',
                'quantity',
                'assigned_to'
            ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    public function request()
    {
        return $this->belongsTo(CustomerRequest::class, 'request_id', 'id');
    }
    public function variation()
    {
        return $this->belongsTo(Variation::class, 'variation_id', 'id');
    }
    
    /**
     * Get the user assigned to this request item
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
