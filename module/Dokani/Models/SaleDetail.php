<?php

namespace Module\Dokani\Models;

use App\Models\Model;

class SaleDetail extends Model
{


    public function product()
    {
        return $this->belongsTo(Product::class);
    }



    public function sale()
    {
        return $this->belongsTo(Sale::class,'sale_id','id');
    }

}
