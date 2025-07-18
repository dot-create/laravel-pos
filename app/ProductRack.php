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

    protected $table = 'product_racks';

    protected $fillable = [
        'business_id',
        'location_id',
        'product_id',
        'storage_location_id' // Changed
    ];

    public function storageLocation()
    {
        return $this->belongsTo(StorageLocation::class);
    }

    public function rackLocation()
    {
        return $this->belongsTo(StorageLocation::class);
    }

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
