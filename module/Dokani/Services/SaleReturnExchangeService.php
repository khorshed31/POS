<?php


namespace Module\Dokani\Services;



use Module\Dokani\Models\Product;
use Module\Dokani\Models\ProductDamageDetail;
use Module\Dokani\Models\ProductStock;
use Module\Dokani\Models\ProductStockLog;
use Module\Dokani\Models\PurchaseDetail;
use Module\Dokani\Models\SaleDetail;
use Module\Dokani\Models\SaleExchange;
use Module\Dokani\Models\SaleReturn;
use Module\Dokani\Models\SaleReturnExchange;
use Module\Dokani\Models\SaleReturnExchangeDetail;
use Module\Dokani\Models\SaleReturnExchangePayment;
use Auth;
use Illuminate\Support\Facades\DB;

class SaleReturnExchangeService
{
    public $saleReturnExchange;
    public $saleReturnExchangeDetail;
    public $saleReturn;
    public $saleExchange;
    public $productBarcodeTrackingService;
    public $stockService;
    public $productDamageService;
    public $invoiceNumberService;











    /*
     |--------------------------------------------------------------------------
     | CONSTRUCTOR
     |--------------------------------------------------------------------------
    */
    public function __construct()
    {
//        $this->productBarcodeTrackingService    = new ProductBarcodeTrackingService;
//        $this->stockService                     = new StockService;
//        $this->productDamageService             = new ProductDamageService;
//        $this->invoiceNumberService             = new InvoiceNumberService;
    }








    // public function saleProductExchangeInvoiceNo()
    // {
    //     $lastInvNo = SaleReturnExchange::latest()->first();
    //     $invoice_no = $lastInvNo ? explode('-', $lastInvNo->invoice_no) : '';

    //     if(!empty($invoice_no[1])) {
    //         $invoice_no = (int) $invoice_no[1] + 1;
    //     }else {
    //         $invoice_no = 100001;
    //     }

    //     return 'R&E-' . $invoice_no;
    // }







    /*
     |--------------------------------------------------------------------------
     | STORE SALE RETURNS (METHOD)
     |--------------------------------------------------------------------------
    */
    public function storeSaleReturns($request, $key)
    {
        return $this->saleReturn = SaleReturn::create([

            'product_id'                  => $request->return_product_id[$key] ?? 0,
            'sale_detail_id'              => $request->return_sale_detail_id[$key] ?? 0,
            'sale_price'                  => $request->return_sale_price[$key] ?? 0,
            'quantity'                    => $request->return_quantity[$key] ?? 0,
            'subtotal'                    => $request->return_subtotal[$key] ?? 0,
            'return_type'                 => $request->return_type[$key] ?? 0,
        ]);

    }









    /*
     |--------------------------------------------------------------------------
     | STORE SALE EXCHANGES (METHOD)
     |--------------------------------------------------------------------------
    */
    public function storeSaleExchanges($request, $key)
    {
        if ($request->exchange_product_id == []) {
            return $this->saleExchange = [];
        }


//        $discountAmount             = $this->saleReturnExchange->total_exchange_discount_amount ? $this->saleReturnExchange->total_exchange_discount_amount / array_sum($request->exchange_quantity) : 0;
//        $exchangeDiscountPercent    = $discountAmount / $request->exchange_sale_price[$key] * 100;
//        $exchangeDiscountAmount     = $discountAmount * $request->exchange_quantity[$key];


        return $this->saleExchange = SaleExchange::create([

            'product_id'                => $request->return_product_id[$key],
            'quantity'                  => $request->exchange_quantity[$key] ?? 0,
            'purchase_price'            => $request->exchange_purchase_price[$key] ?? 0,
            'sale_price'                => $request->exchange_sale_price[$key] ?? 0,
            'discount_percent'          => 0,
            'discount_amount'           => 0,
        ]);
    }










    /*
     |--------------------------------------------------------------------------
     | STORE SALE PRODUCT EXCHANGE (METHOD)
     |--------------------------------------------------------------------------
    */
    public function storeSaleProductExchange($request)
    {
        if ($request->payable_amount < 0 ){
            if ($request->paid_amount > 0 ){

                $paid_amount = -$request->paid_amount;
            }
            else{

                $paid_amount = $request->paid_amount;
            }
        }
        else{
            $paid_amount = $request->paid_amount;
        }

        return $this->saleReturnExchange = SaleReturnExchange::create([

            'company_id'                            => Auth::user()->id,
            'sale_id'                               => $request->sale_id,
            'customer_id'                           => $request->customer_id,
            'date'                                  => $request->date ?? date('Y-m-d'),
            'total_return_quantity'                 => array_sum($request->return_quantity),
            'return_subtotal'                       => $request->total_return_subtotal ?? 0,
            'total_return_discount_percent'         => $request->total_return_discount_percent ?? 0,
            'total_return_discount_amount'          => $request->total_return_discount_amount ?? 0,
            'total_exchange_quantity'               => $request->exchange_quantity != [] ? array_sum($request->exchange_quantity) : 0,
            'total_exchange_cost'                   => $request->total_exchange_cost ?? 0,
            'exchange_subtotal'                     => $request->total_exchange_subtotal ?? 0,
            'total_exchange_discount_percent'       => $request->total_exchange_discount_percent ?? 0,
            'total_exchange_discount_amount'        => $request->total_exchange_discount_amount ?? 0,
            'rounding'                              => $request->rounding ?? 0,
            'paid_amount'                           => $paid_amount ?? 0,
            'due_amount'                            => $request->due_amount ?? 0,
            'change_amount'                         => $request->change_amount ?? 0,
            'dokan_id'                              => dokanId(),
        ]);

    }








    public function againReturnProduct(){


        $returnProducts = SaleReturn::dokani()->get();

        foreach ($returnProducts as $returnProduct){

            $stock_log = ProductStockLog::where('sourceable_id',$returnProduct->sale_detail_id)->where('sourceable_type','Sale')->first();
            $sale_detail = SaleDetail::where('id',$returnProduct->sale_detail_id)->first();

            $product_stock = ProductStock::where('product_id',$returnProduct->product_id)
                ->where('lot',$stock_log->lot)
                ->first();
//            dd($stock_log, $returnProduct->product_id);
//                $available_quantity = $product_stock->available_quantity;
            $sold_return_quantity = $product_stock->sold_return_quantity;
            $product_stock->update([

//                    'available_quantity'        => $request->quantity + $available_quantity,
                'sold_return_quantity'      => $returnProduct->quantity + $sold_return_quantity,
                'sale_return_exchange_id'   => $returnProduct->id,
            ]);

            (new ProductStockLogService())->stockLog(
                $returnProduct->sale_detail_id,
                'Sale Return',
                $returnProduct->product_id,
                $product_stock->lot,
                null,
                'In',
                $returnProduct->quantity,
                -abs($returnProduct->quantity),
                $sale_detail->buy_price,
                $returnProduct->sale_price
            );

            (new ProductLedgerService())->storeLedger(
                $returnProduct->product_id,
                $returnProduct->sale_detail_id,
                'Sale',
                'In',
                $returnProduct->quantity
            );
        }

    }






    /*
     |--------------------------------------------------------------------------
     | STORE SALE RETURN EXCHANGE DETAILS (METHOD)
     |--------------------------------------------------------------------------
    */
    public function storeSaleReturnExchangeDetails($request, $damage = null)
    {
//dd($request->all());
        foreach($request->return_product_id ?? [] as $key => $return_product_id) {

            $this->saleReturn = $this->storeSaleReturns($request, $key);
            $this->saleExchange = $this->storeSaleExchanges($request, $key);

            $this->saleReturnExchangeDetail = SaleReturnExchangeDetail::create([

                'sale_return_exchange_id'       => $this->saleReturnExchange->id,
                'sale_detail_id'                => $request->return_sale_detail_id[$key],
                'sale_return_id'                => $this->saleReturn->id,
                'sale_exchange_id'              => $this->saleExchange ? $this->saleExchange->id : null,
            ]);

            $sale_detail = SaleDetail::where('id',$request->return_sale_detail_id[$key])->first();
            $stock_log = ProductStockLog::where('sourceable_id',$request->return_sale_detail_id[$key])->where('sourceable_type','Sale')->first();
//            dd($sale_detail);
            $sale_detail->update([

                'return_id' => $this->saleReturn->id,
            ]);


//            SaleDetail::where('id', $request->return_sale_detail_id[$key])->update([
//
//                'return_id' => $this->saleReturn->id,
//            ]);

            if($request->return_type[$key] == 'Damaged') {

                for ($i = 0 ; $i < count($request->return_product_id) ; $i++){

                    $this->productDamageService = ProductDamageDetail::create([

                        'product_damage_id'     => $damage->id,
                        'product_id'            => $request->return_product_id[$i],
                        'lot'                   => $stock_log->lot,
                        'condition'             => $request->return_type[$i],
                        'quantity'              => $request->return_quantity[$i],
                        'sale_price'            => $request->return_sale_price[$i],
                    ]);


                    $product_stock = ProductStock::where('product_id',$request->return_product_id[$i])
                        ->where('lot',$stock_log->lot)
                        ->first();
//                dd($stock_log->lot);
//                $available_quantity = $product_stock->available_quantity;
                    $wastage_quantity = $product_stock->wastage_quantity;
                    $product_stock->update([

//                    'available_quantity'        => $request->quantity + $available_quantity,
                        'wastage_quantity'      => $request->return_quantity[$i] + $wastage_quantity,
                    ]);

                    (new ProductStockLogService())->stockLog(
                        $request->return_sale_detail_id[$key],
                        'Sale Return Damaged',
                        $request->return_product_id[$i],
                        $product_stock->lot,
                        null,
                        'In',
                        $request->return_quantity[$i],
                        -abs($request->return_quantity[$i]),
                        $sale_detail->buy_price,
                        $request->return_sale_price[$i]
                    );

                    (new ProductLedgerService())->storeLedger(
                        $request->return_product_id[$i],
                        $request->return_sale_detail_id[$key],
                        'Sale Return Damaged',
                        'In',
                        $request->return_quantity[$i]
                    );


                }

//                $this->productDamageService->storeProductDamageDetail($damage, $request->return_product_id[$key], $request->return_type[$key],
//                    $request->return_quantity[$key], $request->return_purchase_price[$key], $request->return_sale_price[$key],
//                    $request->return_discount_percent[$key], $request->return_discount_amount[$key]);

                //$this->productDamageService->storeProductDamageBarcode($request->return_barcode_id[$key]);

            }
            if($request->return_type[$key] != 'Damaged') {
                $product_stock = ProductStock::where('product_id',$request->return_product_id[$key])
                    ->where('lot',$stock_log->lot)
                    ->first();
//                dd($stock_log->lot);
//                $available_quantity = $product_stock->available_quantity;
                $sold_return_quantity = $product_stock->sold_return_quantity;
                $product_stock->update([

//                    'available_quantity'        => $request->quantity + $available_quantity,
                    'sold_return_quantity'      => $request->return_quantity[$key] + $sold_return_quantity,
                    'sale_return_exchange_id'   => $this->saleReturnExchange->id,
                ]);

                (new ProductStockLogService())->stockLog(
                    $request->return_sale_detail_id[$key],
                    'Sale Return',
                    $request->return_product_id[$key],
                    $product_stock->lot,
                    null,
                    'In',
                    $request->return_quantity[$key],
                    -abs($request->return_quantity[$key]),
                    $sale_detail->buy_price,
                    $request->return_sale_price[$key]
                );

                (new ProductLedgerService())->storeLedger(
                    $request->return_product_id[$key],
                    $request->return_sale_detail_id[$key],
                    'Sale Return',
                    'In',
                    $request->return_quantity[$key]
                );

            }


            if ($request->exchange_product_id != []) {

                for ($key = 0; $key < count($request->exchange_product_id); $key++){

//                    $product_stock = ProductStock::where('product_id',$request->exchange_product_id[$key]);
//                    $available_quantity = $product_stock->available_quantity;
//
//                    $product_stock->update([
//
//                        'available_quantity' => $available_quantity - $request->exchange_quantity[$key],
//                    ]);

                    $lotQtyArr = [];

//            $getLotNo = $this->getLotNo($product_id, $details->quantity);



                    $getLotNumbers = $this->getLotNumbers($request->exchange_product_id[$key]);


                    foreach($getLotNumbers as $lotNumber) {

                        $availableQty = $this->checkAvailableQuantity($request->exchange_product_id[$key], $lotNumber->lot);

                        if ($availableQty > 0) {

                            $leftQty = $request->exchange_quantity[$key] - array_sum($lotQtyArr);

                            $purchaseDetail = PurchaseDetail::where(['product_id' => $request->exchange_product_id[$key], 'lot' => $lotNumber->lot])->first();
                            $product = Product::where('id',$request->exchange_product_id[$key])->first();
//                    dd($product_id, $lotNumber->lot);
                            $quantity = $lotNumber->available_quantity;

                            if ($leftQty <= $lotNumber->available_quantity) {
                                $quantity = $leftQty;
                            }

                            if ($request->exchange_quantity[$key] > array_sum($lotQtyArr)) {


                                (new ProductStockService())->stockUpdateOrCreate(
                                    $request->exchange_product_id[$key],
                                    $purchaseDetail->expiry_at ?? null,
                                    $lotNumber->lot,
                                    'sale',
                                    $purchaseDetail->quantity ?? 0,
                                    0,
                                    $quantity);

                                (new ProductStockLogService())->stockLog(
                                    $request->exchange_product_id[$key],
                                    'Sale Exchange',
                                    $request->exchange_product_id[$key],
                                    $lotNumber->lot,
                                    $purchaseDetail->expiry_at ?? null,
                                    'Out',
                                    $quantity,
                                    -abs($quantity),
                                    $product->purchase_price,
                                    $product->sell_price
                                );

                                (new ProductLedgerService())->storeLedger(
                                    $request->exchange_product_id[$key],
                                    $request->exchange_product_id[$key],
                                    'Sale Exchange',
                                    'Out',
                                    $quantity
                                );
                            }

                            array_push($lotQtyArr, $quantity);

                        }

                    }

                }


            }
        }
    }






    public function checkAvailableQuantity($product_id, $lot)
    {
        return  ProductStock::dokani()
            ->where([

                'product_id'            => $product_id,
                'lot'                   => $lot,
            ])
            ->orderBy('id', 'ASC')
            ->sum('available_quantity');
    }


    public function getLotNumbers($product_id)
    {
        return  ProductStock::dokani()
            ->where([
                'product_id'            => $product_id,
            ])
            ->orderBy('id', 'ASC')
            ->get();
    }




    /*
     |--------------------------------------------------------------------------
     | STORE SALE RETURN EXCHANGE PAYMENT (METHOD)
     |--------------------------------------------------------------------------
    */
//    public function storeSaleReturnExchangePayment($request)
//    {
//        foreach (array_filter($request->pos_account_id) as $key => $account_id) {
//
//            $last_key = array_key_last($request->pos_account_id);
//
//            if ($request->payment_amount[$key] ?? 0 > 0) {
//
//                $balance = (float) $request->payment_amount[$key];
//
//                if ($key == $last_key) {
//
//                    $balance = $balance - $this->saleReturnExchange->change_amount;
//                }
//
//                SaleReturnExchangePayment::create([
//
//                    'sale_id'                       => $request->sale_id,
//                    'sale_return_exchange_id'       => $this->saleReturnExchange->id,
//                    'pos_account_id'                => $account_id,
//                    'amount'                        => $balance,
//                ]);
//
//                PosAccount::find($account_id)->increment('balance', $balance ?? 0);
//            }
//        }
//    }
}
