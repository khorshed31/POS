<?php

namespace Module\Dokani\Services;

use App\Traits\FileSaver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Module\Dokani\Models\Product;
use Module\Dokani\Models\ProductStock;
use Illuminate\Validation\Rule;


class ProductService
{
    use FileSaver;

    public $product;

    /*
     |--------------------------------------------------------------------------
     | VALIDATION METHOD
     |--------------------------------------------------------------------------
    */

    public function validation($request)
    {
        return $request->validate([
            'name'              => 'required',
            'unit_id'           => 'nullable',
            'category_id'       => 'nullable',
            'barcode'           => [
                                    'required'
//                                     Rule::unique('products')->where('dokan_id', dokanId()),
                                   ],
            'purchase_price'    => 'required',
            'sell_price'        => 'required',
        ]);
    }
    /*
     |--------------------------------------------------------------------------
     | STORE/UPDATE METHOD
     |--------------------------------------------------------------------------
    */
    public function storeOrUpdate($request, $id = null)
    {

        $data = $this->validation($request);

        $data['brand_id']       = $request->brand_id;
        $data['description']    = $request->description;
        $data['opening_stock']  = $request->opening_stock ?? 0;
        $data['alert_qty']      = $request->alert_qty ?? 0;
        $data['vat']            = $request->vat;

        $this->product = Product::updateOrCreate([
            'id'    => $id,
        ], $data);

        $this->upload_file($request->image, $this->product, 'image', 'products');


        if ($id == null) {
            (new ProductLedgerService())->storeLedger(
                $this->product->id,
                $this->product->id,
                'Product Add',
                'In',
                $request->opening_stock
            );

//            (new ProductStockLogService())->stockLog(
//                $this->product->id,
//                'Product Add',
//                $this->product->id,
//                null,
//                $this->product->expiry_at ?? null,
//                'In',
//                $this->product->opening_stock,
//                $this->product->opening_stock,
//                $this->product->purchase_price,
//                $this->product->sell_price
//            );
        }
    }


    public function againAddProduct(){

        $products = Product::dokani()->get();

        foreach ($products as $product){

            if ($product->opening_stock > 0){

                $product_stock = ProductStock::where('product_id', $product->id)->first();

                $lot = strtoupper(Str::random(5));

                if ($product_stock) {
                    $product_stock->increment('opening_quantity', $product->opening_stock);
//            $product_stock->increment('available_quantity', $request->opening_stock);

                } else {

                    $stock_in_value = ($product->opening_stock * $product->purchase_price);
                    $stock_out_value = 0 ;

                    $product->stocks()->create([
                        'dokan_id'                  => dokanId(),
                        'expiry_at'                 => $product->expiry_at ?? null,
                        'lot'                       => $lot,
                        'opening_quantity'          => $product->opening_stock,
//                'available_quantity'        => $request->opening_stock ?? 0,
                        'purchased_quantity'        => $product->purchased_quantity ?? 0,
                        'sold_quantity'             => $product->sold_quantity ?? 0,
                        'wastage_quantity'          => $product->wastage_quantity ?? 0,
                        'sold_return_quantity'      => $product->sold_return_quantity ?? 0,
                        'purchase_return_quantity'  => $product->purchase_return_quantity ?? 0,
                        'stock_in_value'            => $stock_in_value,
                        'stock_out_value'           => $stock_out_value,
                    ]);

                    (new ProductStockLogService())->stockLog(
                        $product->id,
                        'Product Add',
                        $product->id,
                        $lot,
                        $product->expiry_at,
                        'In',
                        $product->opening_stock,
                        $product->opening_stock,
                        $product->purchase_price,
                        $product->sell_price
                    );

                }
            }



        }



    }


    public function stockUpdateOrCreate($request)
    {
        $product_stock = ProductStock::where('product_id', $this->product->id)->first();

        $lot = strtoupper(Str::random(5));

        if ($product_stock) {
            $product_stock->increment('opening_quantity', $request->opening_stock);
//            $product_stock->increment('available_quantity', $request->opening_stock);

        } else {

            $stock_in_value = ($request->opening_stock * $request->purchase_price);
            $stock_out_value = 0 ;

            $this->product->stocks()->create([
                'dokan_id'                  => dokanId(),
                'expiry_at'                 => $request->expiry_at ?? null,
                'lot'                       => $lot,
                'opening_quantity'          => $request->opening_stock,
//                'available_quantity'        => $request->opening_stock ?? 0,
                'purchased_quantity'        => $request->purchased_quantity ?? 0,
                'sold_quantity'             => $request->sold_quantity ?? 0,
                'wastage_quantity'          => $request->wastage_quantity ?? 0,
                'sold_return_quantity'      => $request->sold_return_quantity ?? 0,
                'purchase_return_quantity'  => $request->purchase_return_quantity ?? 0,
                'stock_in_value'            => $stock_in_value,
                'stock_out_value'           => $stock_out_value,
            ]);

            (new ProductStockLogService())->stockLog(
                $this->product->id,
                'Product Add',
                $this->product->id,
                $lot,
                $request->expiry_at,
                'In',
                $request->opening_stock,
                $request->opening_stock,
                $request->purchase_price,
                $request->sell_price

            );

        }

    }
}
