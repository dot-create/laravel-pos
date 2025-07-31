<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'discount',
        'discount_type',
        'subtotal_wd',
        'subtotal_wd_tax',
        'tax',
        'item_notes',
        'supply_ref',
        'status',
        'sell_price_wot',
        'quantity',

        // New fields
        'order_date',
        'cso_purchasing_req_no',
        'ipr_qty',
        'stock_on_hand_hs',
        'approved_ipr_qty_hs',
        'in_transit_qty_hs',
        'committed_qty_hs',
        'available_qty',
        'qty_available_for_invoice',
        'suggested_qty_to_request',
        'invoiced_for_this_req',
        'pending_invoice',
        'committed_for_this_order',
        'received_for_this_order',
        'live_available_for_this_order',
        'qty_to_generate_invoice',
        'internal_req_qty',
        'status_purchase',
        'status_invoice'
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
}
