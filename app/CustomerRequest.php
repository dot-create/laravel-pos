<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id', 'customer_id', 'request_reference', 'business_location_id', 'foreign_business_location_id','status'
    ];
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'customer_id', 'id');
    }
    public function items()
    {
        return $this->hasMany(RequestItem::class, 'request_id', 'id');
    }
}
