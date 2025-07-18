<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Transaction;
use App\BusinessLocation;
use App\Currency;

class ExpensePurchase extends Model
{
    
    // protected $guarded = ['id'];
    protected $guarded=[];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    public $timestamps = false;


    public function transaction()
    {
        return $this->belongsTo(\App\Transaction::class, 'purchase_id');
    }

    // Add relationship to contact
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    // Add relationship to payments
    public function payment_lines()
    {
        return $this->hasMany(TransactionPayment::class, 'transaction_id');
    }

  
}
