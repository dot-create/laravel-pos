<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductRack extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the business location this rack belongs to.
     */
    public function location()
    {
        return $this->belongsTo(BusinessLocation::class, 'location_id');
    }

    /**
     * Get the product assigned to this rack.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the business this rack belongs to.
     */
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    /**
     * Scope a query to filter by business.
     */
    public function scopeByBusiness($query, $business_id)
    {
        return $query->where('business_id', $business_id);
    }
}
