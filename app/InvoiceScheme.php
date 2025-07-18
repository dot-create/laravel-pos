<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceScheme extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 
        'scheme_type', 
        'prefix', 
        'start_number', 
        'total_digits',
        'is_default', 
        'status',  
        'invoice_count',
        'end_number',
        'start_date', 
        'expiration_date', 
        'invoicing_key', 
        'business_id'
    ];

    protected $casts = [
        'start_date' => 'date',
        'expiration_date' => 'date',
    ];


    /**
     * Returns list of invoice schemes in array format
     */
    public static function forDropdown($business_id)
    {
        $dropdown = InvoiceScheme::where('business_id', $business_id)
                                ->pluck('name', 'id');

        return $dropdown;
    }

    /**
     * Retrieves the default invoice scheme
     */
    public static function getDefault($business_id)
    {
        $default = InvoiceScheme::where('business_id', $business_id)
                                ->where('is_default', 1)
                                ->first();
        return $default;
    }
}
