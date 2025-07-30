<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeightUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'unit_name',
        'equivalent_to_lb',
    ];
}
