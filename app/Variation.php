<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Variation extends Model
{
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
    
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'combo_variations' => 'array',
    ];
    
    public function product_variation()
    {
        return $this->belongsTo(\App\ProductVariation::class);
    }

    public function product()
    {
        return $this->belongsTo(\App\Product::class, 'product_id');
    }

    /**
     * Get the sell lines associated with the variation.
     */
    public function sell_lines()
    {
        return $this->hasMany(\App\TransactionSellLine::class);
    }

    /**
     * Get the location wise details of the the variation.
     */
    public function variation_location_details()
    {
        return $this->hasMany(\App\VariationLocationDetails::class);
    }
    
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    
    // public function getDefaultPurchasePriceAttribute($value)
    // {
    //     $currencyRate = $this->currency->rate;
    //     $convertedPrice = (float)$value / (float)$currencyRate;
    //     return $convertedPrice;
    // }
    
    // public function getDppIncTaxAttribute($value)
    // {
    //     $currencyRate = $this->currency->rate;
    //     $convertedPrice = (float)$value / (float)$currencyRate;
    //     return $convertedPrice;
    // }
    
    // public function getDefaultSellPriceAttribute($value)
    // {
    //     $currencyRate = $this->currency->rate;
    //     $convertedPrice = (float)$value / (float)$currencyRate;
    //     return $convertedPrice;
    // }
    
    // public function getSellPriceIncTaxAttribute($value)
    // {
    //     $currencyRate = $this->currency->rate;
    //     $convertedPrice = (float)$value / (float)$currencyRate;
    //     return $convertedPrice;
    // }


    /**
     * Get Selling price group prices.
     */
    public function group_prices()
    {
        return $this->hasMany(\App\VariationGroupPrice::class, 'variation_id');
    }

    public function media()
    {
        return $this->morphMany(\App\Media::class, 'model');
    }

    public function getFullNameAttribute()
    {
        $name = $this->product->name;
        if ($this->product->type == 'variable') {
            $name .= ' - ' . $this->product_variation->name . ' - ' . $this->name;
        }
        $name .= ' (' . $this->sub_sku . ')';

        return $name;
    }
}
