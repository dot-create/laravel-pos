<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $guarded = [];
    
    public function location() {
        return $this->belongsTo(BusinessLocation::class);
    }
    // public function getRateAttribute($rate) {
    //     return str_replace(",", "", number_format($rate, 4));
    // }
    
    //     public function setRateAttribute($rate) {
    //     $businessId = request()->session()->get('user.business_id');
    //     $business = Business::find($businessId);
    //     $exchange_rate = $business->currency_exchange_rate ?? 2;
    //     return number_format($exchange_rate / $rate, 4);
    // }
    
}
