<?php

namespace Module\Dokani\Models;

use App\Models\Model;
use App\Traits\AutoCreatedUpdated;

class SaleReturnExchange extends Model
{
    use AutoCreatedUpdated;

    protected $table = 'sale_return_exchanges';








    /*
     |--------------------------------------------------------------------------
     | SALE (RELATION)
     |--------------------------------------------------------------------------
    */
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }





    /*
     |--------------------------------------------------------------------------
     | CUSTOMER (RELATION)
     |--------------------------------------------------------------------------
    */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }





    /*
     |--------------------------------------------------------------------------
     | SALE RETURN EXCHANGE DETAILS (RELATION)
     |--------------------------------------------------------------------------
    */
    public function saleReturnExchangeDetails()
    {
        return $this->hasMany(SaleReturnExchangeDetail::class, 'sale_return_exchange_id');
    }









    /*
     |--------------------------------------------------------------------------
     | SALE RETURN EXCHANGE PAYMENT (RELATION)
     |--------------------------------------------------------------------------
    */
    public function saleReturnExchangePayments()
    {
        return $this->hasMany(SaleReturnExchangePayment::class, 'sale_return_exchange_id');
    }
}
