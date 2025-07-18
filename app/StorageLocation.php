<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorageLocation extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'business_id',
        'location_id',
        'rack',
        'row',
        'position'
    ];

    public function getFullCodeAttribute()
    {
        return "{$this->rack}.{$this->row}.{$this->position}";
    }

    public function location()
    {
        return $this->belongsTo(BusinessLocation::class);
    }

    public function businessLocation()
    {
        return $this->belongsTo(BusinessLocation::class, 'location_id');
    }
}