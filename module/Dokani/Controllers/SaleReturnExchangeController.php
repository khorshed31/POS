<?php

namespace Module\Dokani\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Traits\CheckPermission;
use Module\Dokani\Models\BusinessSetting;
use Module\Dokani\Models\Customer;
use Module\Dokani\Models\Product;
use Module\Dokani\Models\ProductDamage;
use Module\Dokani\Models\ProductStock;
use Module\Dokani\Models\ProductStockLog;
use Module\Dokani\Models\Sale;
use Module\Dokani\Models\SaleDetail;
use Module\Dokani\Models\SaleExchange;
use Module\Dokani\Models\SaleReturn;
use Module\Dokani\Models\SaleReturnExchange;
use Module\Dokani\Services\AjaxService;
use Module\Dokani\Services\SaleReturnExchangeService;
use Auth;

class SaleReturnExchangeController extends Controller
{
    use CheckPermission;


    public $saleReturnExchangeService;
    public $productDamageService;
    public $stockService;
    public $productExchange;
    public $invoiceNumberService;



    /*
     |--------------------------------------------------------------------------
     | CONSTRUCTOR
     |--------------------------------------------------------------------------
    */
    public function __construct()
    {
        $this->saleReturnExchangeService    = new SaleReturnExchangeService;

    }












    /*
     |--------------------------------------------------------------------------
     | INDEX METHOD
     |--------------------------------------------------------------------------
    */
    public function index()
    {
        $saleReturnExchanges = SaleReturnExchange::latest()->paginate(25);
        return view('sales.return-exchanges.index', compact('saleReturnExchanges'));
    }












    /*
     |--------------------------------------------------------------------------
     | CREATE METHOD
     |--------------------------------------------------------------------------
    */
    public function create(Request $request)
    {

        $data['saleInvoices']   = Sale::pluck('invoice_no');

        $data['sale']           = Sale::query()->dokani()

                                ->when($request->filled('customer_id'), function ($q) use ($request) {
                                    $q->where('customer_id', $request->customer_id);
                                })

                                ->when($request->filled('invoice_no'), function ($q) use ($request) {
                                    $q->where('invoice_no', $request->invoice_no);
                                })
                                ->first();
       // dd($data['sale']);
        $data['customers']      = Customer::dokani()->with('sales:id,customer_id,invoice_no')->get(['id','name','mobile']);
//
        $data['products']       = Product::dokani()->get(['id', 'name', 'barcode', 'category_id', 'unit_id']);

        return view('sales.return-exchanges.create', $data);

    }












    /*
     |--------------------------------------------------------------------------
     | GET RETURN PRODUCT METHOD
     |--------------------------------------------------------------------------
    */
    public function getReturnProduct(Request $request)
    {

        $data['getReturnProduct']   = SaleDetail::where('id', !empty($getBarcode) ? $getBarcode->sale_detail_id : $request->sale_detail_id)
                                    ->with(['product' => function ($q) {
                                        $q->with('unit:id,name')
                                            ->with('category:id,name')
                                            ->select('id', 'name', 'barcode', 'category_id', 'unit_id');
                                    }])
                                    ->first();

        return $data;
    }












    /*
     |--------------------------------------------------------------------------
     | GET EXCHANGE PRODUCT METHOD
     |--------------------------------------------------------------------------
    */
    public function getExchangeProduct(Request $request)
    {

        $data['getExchangeProduct']   = Product::dokani()->where('barcode' , $request->search)
            ->with('unit:id,name')
            ->with('category:id,name')
            ->with('stocks')
            ->first();

        return $data;
    }












    /*
     |--------------------------------------------------------------------------
     | STORE METHOD
     |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        //dd($request->all());
        DB::transaction(function () use ($request) {

            $this->productExchange = $this->saleReturnExchangeService->storeSaleProductExchange($request);
            //dd($this->productExchange);
            $is_damage = false;
            foreach($request->return_type as $return_type) {

                if ($return_type == 'Damaged') {
                    $is_damage = true;
                }
            }

            $damage = '';
            if ($is_damage == true) {
                $damage = ProductDamage::create([

                    'dokan_id'                      => Auth::user()->id,
                    'sale_id'                       => $request->sale_id,
                    'date'                          => date('Y-m-d'),
                    'amount'                        => $request->paid_amount,
                ]);
            }
            $this->saleReturnExchangeService->storeSaleReturnExchangeDetails($request, $damage);

            //$this->saleReturnExchangeService->storeSaleReturnExchangePayment($request);

            //$this->invoiceNumberService->setNextInvoiceNo($request->company_id, $request->branch_id, 'Sale-Return-Exchange', date('Y'));

        });

//        if ($request->radio == 'pos-print') {
//
//            $url = route('dokani.sale-return-exchanges.show', ['id' => $this->productExchange->id, 'print_type' => 'pos-print']);
//        } else {
//
//            $url = route('dokani.sale-return-exchanges.show', ['id' => $this->productExchange->id, 'print_type' => 'normal-print']);
//        }

        return redirect()->route('dokani.sale-return-exchanges.index')->withMessage('Sale Return & Exchange Create Successfully');
    }


















    /*
     |--------------------------------------------------------------------------
     | SHOW METHOD
     |--------------------------------------------------------------------------
    */
    public function show(SaleReturnExchange $saleReturnExchange, Request $request)
    {
//        $this->hasAccess("sale-return-exchanges.show");
        $business_settings = BusinessSetting::query()->where('user_id',dokanId())->first();
        if ($request->print_type == 'normal-print') {

            $customer = $saleReturnExchange->customer;

            return view('sales.return-exchanges.invoice', compact('saleReturnExchange', 'customer','business_settings'));
        }

        $customer = $saleReturnExchange->customer;

        return view('sales.return-exchanges.pos-print', compact('saleReturnExchange', 'customer','business_settings'));
    }


















    /*
     |--------------------------------------------------------------------------
     | DESTROY METHOD
     |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
//        $this->hasAccess("sale-return-exchanges.destroy");
        try {

            DB::transaction(function () use ($id) {

                $returnExchange = SaleReturnExchange::with('saleReturnExchangeDetails')->where('id', $id)->first();

//dd($returnExchange);
                foreach($returnExchange->saleReturnExchangeDetails ?? [] as $returnExchangeDetail) {

                   SaleDetail::where('id', $returnExchangeDetail->sale_detail_id)->update(['return_id' => null]);

                    $productDamage = ProductDamage::where('sale_id', $returnExchange->sale_id)->with('productDamageDetails')->first();

                    if ($productDamage != null) {

                        foreach($productDamage->productDamageDetails ?? [] as $productDamageDetail) {
                            $productDamageDetail->delete();
                        }

                        $productDamage->delete();
                    }


                        $product_stock = ProductStock::where('sale_return_exchange_id', $returnExchange->id)->first();
//dd($product_stock);
                    if ($product_stock){
//                        $available_quantity = $product_stock->available_quantity;
                        $sold_return_quantity = $product_stock->sold_return_quantity;
                        $product_stock->update([

//                            'available_quantity'    => $available_quantity - $returnExchange->total_return_quantity,
                            'sold_return_quantity'  => $sold_return_quantity - $returnExchange->total_return_quantity,
                            'sale_return_exchange_id'  => null,
                        ]);
                    }

                    $stock_log = ProductStockLog::dokani()->where('sourceable_type','Sale Return')->where('sourceable_id',$returnExchangeDetail->sale_detail_id)->first();

                    optional($stock_log)->delete();

                    $saleReturn = SaleReturn::where('id', $returnExchangeDetail->sale_return_id)->first();
                    $saleExchange = SaleExchange::where('id', $returnExchangeDetail->sale_exchange_id)->first();
                    $returnExchangeDetail->delete();

                    if ($saleReturn) {

                        $saleReturn->delete();
                    }

                    if ($saleExchange) {

                        $saleExchange->delete();
                    }
                }


                $returnExchange->delete();
            });


        } catch (\Throwable $th) {


            return redirect()->back()->withError('Something went wrong');
            //return redirect()->back()->withError($th->getMessage());
        }

        return redirect()->back()->withMessage('Sale Return & Exchange Deleted!');
    }
}
