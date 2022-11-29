<?php


namespace Module\Dokani\Services;


use Carbon\Carbon;
use Module\Dokani\Models\Product;
use Module\Dokani\Models\ProductStock;

class ProductStockService
{


    public function stockUpdateOrCreate(
        $product_id,
        $expiry_date,
        $lot,
        $type,
        $purchase_qty = 0,
        $sold_quantity = 0,
        $sold_exchange_quantity = 0,
        $sold_return_quantity = 0,
        $wastage_quantity = 0,
        $purchase_return_quantity = 0
    )
    {

//        $expiry = Carbon::parse($expiry_date)->format('Y-m-d');
        $product_stock = ProductStock::dokani()->where('lot', $lot)
//            ->where('expiry_at', $expiry_date ?? null)
            ->where('product_id', $product_id)->first();

        $purchase_qty = $purchase_qty + optional($product_stock)->purchased_quantity;
        $sold_quantity = $sold_quantity + optional($product_stock)->sold_quantity;
        $sold_exchange_quantity = $sold_exchange_quantity + optional($product_stock)->sold_exchange_quantity;

        $product = Product::dokani()->where('id',$product_id)->first();

        $stock_in_value = ($product->opening_stock * $product->purchase_price)
                        + ($purchase_qty * $product->purchase_price)
                        + ($sold_return_quantity * $product->purchase_price);

        $stock_out_value = ($wastage_quantity * $product->purchase_price)
                        + ($sold_quantity * $product->purchase_price)
                        + ($purchase_return_quantity * $product->purchase_price);

             $stock = ProductStock::updateOrCreate(
                [
                    'dokan_id'              => dokanId(),
                    'product_id'            => $product_id,
                    'lot'                   => $lot,
                ],
                [
                'product_id'                => $product_id,
                'dokan_id'                  => dokanId(),
                'lot'                       => $lot,
                'expiry_at'                 => $expiry_date,
                'opening_quantity'          => $product_stock->opening_quantity ?? 0,
//                'available_quantity'        => $available_quantity ?? 0,
                'purchased_quantity'        => $type == 'purchase' ? $purchase_qty : optional($product_stock)->purchased_quantity,
                'sold_quantity'             => $type == 'sale' ? $sold_quantity : 0,
                'sold_exchange_quantity'    => $sold_exchange_quantity,
                'wastage_quantity'          => $wastage_quantity,
                'sold_return_quantity'      => $sold_return_quantity,
                'purchase_return_quantity'  => $purchase_return_quantity,
                'stock_in_value'            => $stock_in_value,
                'stock_out_value'           => $stock_out_value,
            ]);
//        dd($stock);
    }

}
