<?php

namespace Module\Dokani\Controllers;

use Illuminate\Http\Request;
use Module\Dokani\Models\CusArea;
use Module\Dokani\Models\CusCategory;
use Module\Dokani\Models\PointSetting;
use Module\Dokani\Models\Product;
use Illuminate\Support\Facades\DB;
use Module\Dokani\Models\Customer;
use App\Http\Controllers\Controller;
use Module\Dokani\Models\BusinessSetting;
use Module\Dokani\Models\Category;
use Module\Dokani\Models\Sale;
use App\Models\User;
use Module\Dokani\Models\Courier;
use Module\Dokani\Services\SaleService;

class SaleController extends Controller
{
    private $service;


    /*
     |--------------------------------------------------------------------------
     | CONSTRUCTOR
     |--------------------------------------------------------------------------
    */
    public function __construct(SaleService $saleService)
    {
        $this->service = $saleService;
    }












    /*
     |--------------------------------------------------------------------------
     | INDEX METHOD
     |--------------------------------------------------------------------------
    */
    public function index()
    {
        $data['sales']  = Sale::with('customer')
            ->latest()
            ->dokani()
            ->dateFilter()
            ->searchByField('courier_id')
            ->searchFromRelation('customer','name')
            ->likeSearch('invoice_no')
            ->paginate(25);

            $data['couriers']   = Courier::dokani()->get();

        return view('sales/index', $data);
    }




    /*
     |--------------------------------------------------------------------------
     | CREATE METHOD
     |--------------------------------------------------------------------------
    */
    public function create()
    {
        $data['categories'] = Category::dokani()->pluck('name', 'id');
        $data['invoice']    = BusinessSetting::where('user_id', User::first()->id)->first();
        $data['customers']  = Customer::dokani()->getCustomer()->get();
        $data['areas']      = CusArea::dokani()->get();
        $data['cus_categories'] = CusCategory::dokani()->get();
        $data['users']      = User::whereDokanId(auth()->id())->get();
        $data['couriers']   = Courier::dokani()->get();
        $data['point']      = PointSetting::dokani()->first();
        $data['products']   = Product::dokani()->with('unit')
            ->withCount(['stocks as available_qty' => function ($query) {
                $query->select(DB::raw('SUM(available_quantity)'));
            }])
            ->latest()
            ->paginate(25);

        return view('sales/sales/create-new', $data);
    }


    /*
     |--------------------------------------------------------------------------
     | CREATE Sale METHOD
     |--------------------------------------------------------------------------
    */
    public function createSale()
    {

        $data['invoice']    = BusinessSetting::where('user_id', User::first()->id)->first();
        $data['customers']  = Customer::dokani()->getCustomer()->get();
        $data['areas']      = CusArea::dokani()->get();
        $data['cus_categories'] = CusCategory::dokani()->get();
        $data['users']      = User::whereDokanId(auth()->id())->get();
        $data['couriers']   = Courier::dokani()->get();
        $data['point']      = PointSetting::dokani()->first();

        return view('sales/sales/create-sale', $data);
    }












    /*
     |--------------------------------------------------------------------------
     | STORE METHOD
     |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
//dd($request->all());
        try {
            DB::transaction(function () use ($request) {

                if ($request->customer_id == null){
                    return redirect()->back()->withError('Select Customer');
                }
                else{
                    $this->service->store($request);
                    $this->service->saleDetails($request);
                }

            });
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }

        $url = route('dokani.sales.show', $this->service->sale->id) . '?type=' . $request->invoice_type;
        return redirect($url);
    }













    /*
     |--------------------------------------------------------------------------
     | SHOW METHOD
     |--------------------------------------------------------------------------
    */
    public function show($id, Request $request)
    {
        try {
            $view = $request->type == 'pos-invoice' ? 'sales.pos-invoice' : 'sales.show';

             $data['sale'] = Sale::query()->with('details','account','multi_accounts.account')->find($id);
//             dd($data['sale']->multi_accounts);
             $data['business_settings'] = BusinessSetting::query()->where('user_id',dokanId())->first();

            return view($view, $data);
        } catch (\Throwable $th) {
             return back()->withError($th->getMessage());
        }
    }













    /*
     |--------------------------------------------------------------------------
     | EDIT METHOD
     |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        $data['sale'] = Sale::dokani()
            ->where('id',$id)
            ->with('sale_details.product.unit','customer')
            ->first();
        $data['customers']  = Customer::dokani()->getCustomer()->get();
        $data['users']      = User::whereDokanId(auth()->id())->get();
        $data['point']      = PointSetting::dokani()->first();
        $data['couriers']   = Courier::dokani()->get();
//        dd($data);
        return view('sales.sales.edit',$data);
    }













    /*
     |--------------------------------------------------------------------------
     | UPDATE METHOD
     |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {

        try {
            DB::transaction(function () use ($request , $id) {

                $this->service->saleEdit($request ,$id);
                $this->service->saleDetailsEdit($request ,$id);

            });
        } catch (\Throwable $th) {
            redirectIfError($th, 1);
        }

        $url = route('dokani.sales.show', $this->service->sale->id) . '?type=' . $request->invoice_type;

        return redirect($url);
    }












    /*
     |--------------------------------------------------------------------------
     | DELETE/DESTORY METHOD
     |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $this->service->saleDelete($id);
            });

            return redirect()->route('dokani.sales.index')->withMessage('Sale deleted successfully !');
        } catch (\Throwable $th) {

            return redirect()->back()->withError($th->getMessage());
        }
    }
}
