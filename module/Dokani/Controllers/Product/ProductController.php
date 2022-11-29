<?php

namespace Module\Dokani\Controllers\Product;

use App\Traits\FileSaver;
use Illuminate\Http\Request;
use Module\Dokani\Models\Brand;
use Module\Dokani\Models\ProductStockLog;
use Module\Dokani\Models\Unit;
use Module\Dokani\Models\Product;
use Illuminate\Support\Facades\DB;
use Module\Dokani\Models\Category;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Module\Dokani\Models\ProductStock;
use Module\Dokani\Models\ProductLedger;
use Module\Dokani\Services\ProductService;
use Module\Dokani\Import\ProductUploadCSV;

class ProductController extends Controller
{

    use FileSaver;

    private $service;


    /*
     |--------------------------------------------------------------------------
     | CONSTRUCTOR
     |--------------------------------------------------------------------------
    */
    public function __construct(ProductService $productService)
    {
        $this->service = $productService;
    }












    /*
     |--------------------------------------------------------------------------
     | INDEX METHOD
     |--------------------------------------------------------------------------
    */
    public function index()
    {

        $data['categories'] = Category::dokani()->pluck('name', 'id');
        $data['units']      = Unit::dokani()->pluck('name', 'id');
        $data['brands']      = Brand::dokani()->pluck('name', 'id');
        $data['products']   = Product::dokani()->with('category:id,name', 'unit:id,name' , 'brand:id,name')
                                                ->likeSearch('name')
                                                ->searchByFields(['category_id','brand_id','barcode'])
                                                ->latest()
                                                ->paginate(25);
        return view('products/product/index', $data);
    }













    /*
     |--------------------------------------------------------------------------
     | CREATE METHOD
     |--------------------------------------------------------------------------
    */
    public function create()
    {
        $data['categories'] = Category::dokani()->pluck('name', 'id');
        $data['units']      = Unit::dokani()->pluck('name', 'id');
        $data['brands']      = Brand::dokani()->pluck('name', 'id');

        return view('products/product/create', $data);
    }













    /*
     |--------------------------------------------------------------------------
     | STORE METHOD
     |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        try {

            if ($request->store_type == 'upload') {

                if(!$request->csv_file){
                    return redirect()->back()->withError('Please upload csv file!');
                }
                Excel::import(new ProductUploadCSV(), $request->file('csv_file'));
            } else {
                // for ($i = 0; $i < 100; $i++) {
                DB::transaction(function () use ($request) {
                    $this->service->storeOrUpdate($request);
                    if ($request->opening_stock > 0){
                        $this->service->stockUpdateOrCreate($request);
                    }
                });
                // }
            }
        } catch (\Throwable $th) {
            redirectIfError($th, 1);
        }
        return redirect()->route('dokani.products.index')->withMessage('Product added successfully !');
    }













    /*
     |--------------------------------------------------------------------------
     | SHOW METHOD
     |--------------------------------------------------------------------------
    */
    public function show(Product $product)
    {
        return view('products.product.show', compact('product'));
    }













    /*
     |--------------------------------------------------------------------------
     | EDIT METHOD
     |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        $data['categories'] = Category::dokani()->pluck('name', 'id');
        $data['units']      = Unit::dokani()->pluck('name', 'id');
        $data['brands']      = Brand::dokani()->pluck('name', 'id');
        $data['product']    = Product::with('unit:id,name', 'category:id,name', 'stocks')->find($id);

        return view('products.product.edit', $data);
    }













    /*
     |--------------------------------------------------------------------------
     | UPDATE METHOD
     |--------------------------------------------------------------------------
    */
    public function update($id, Request $request)
    {
        try {
            $this->service->storeOrUpdate($request, $id);
        } catch (\Throwable $th) {

            redirectIfError($th, 1);
        }

        return redirect()->route('dokani.products.index')->withMessage('Product edit successfully !');
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

                $product = Product::find($id);
                if (file_exists($product->image)) {
                    unlink($product->image);
                }

                ProductStock::where('product_id', $product->id)->delete();

                ProductStockLog::where('product_id', $product->id)->delete();

                ProductLedger::where('product_id', $product->id)->delete();

                $product->delete();

            });
        } catch (\Throwable $th) {
            return redirect()->back()->withError('This product used another table');
        }

        return redirect()->route('dokani.products.index')->withMessage('Product delete successfully !');
    }



    public function barcode()
    {
        try {
            $products = Product::dokani()->select('id', 'name', 'barcode')->paginate(25);

            return view('products/product/barcode/index', compact('products'));
        } catch (\Throwable $th) {
            //throw $th;
        }
    }


    public function barcodePrint($id)
    {
        try {

            $product = Product::find($id);;

            return view('products.product.barcode.label-print', compact('product'));
        } catch (\Throwable $th) {
            //throw $th;
        }
    }









    /**
     * -----------------------------------------------------------------------------------
     * PRODUCT GET VIA AJAX
     * -----------------------------------------------------------------------------------
     */
    public function getProduct(Request $request)
    {

        $products = Product::dokani()->with('category:id,name', 'unit:id,name')
            ->withCount(['stocks as available_qty' => function ($query) {
                $query->select(DB::raw('SUM(available_quantity)'));
            }])
            ->searchByField('category_id')
            ->latest()->paginate(25);
        return view('partials._card', compact('products'))->render();
    }






    /**
     * -----------------------------------------------------------------------------------
     * PRODUCT GET VIA AJAX
     * -----------------------------------------------------------------------------------
     */
    public function getPurchaseProduct(Request $request)
    {

        $products = Product::dokani()->with('category:id,name', 'unit:id,name')->searchByField('category_id')->latest()->paginate(25);

        return view('partials._purchase-card', compact('products'))->render();
    }





    /**
     * -----------------------------------------------------------------------------------
     * CREATE BRAND VIA AJAX
     * -----------------------------------------------------------------------------------
     */
    public function createBrand(Request $request)
    {

        Brand::create([
            'name' => $request->name,
        ]);


        return response()->json(['data'=>Brand::dokani()->latest()->get()]);
    }







    /**
     * -----------------------------------------------------------------------------------
     * PRODUCT SEARCH VIA AJAX
     * -----------------------------------------------------------------------------------
     */
    public function getSearchableProduct(Request $request)
    {
         //if ($request->ajax()) {


        return Product::dokani()
            ->where('name', 'like', $request->search . '%')
            ->orWhere('barcode', 'like', $request->search . '%')
            ->withCount(['stocks as available_qty' => function ($query) {
                $query->select(DB::raw('SUM(available_quantity)'));
            }])
            ->dokani()->paginate(50)->map(function ($item) {
                return [
                    'id'                    => $item->id,
                    'name'                  => $item->name,
                    'image'                 => file_exists($item->image) && $item->image ? asset($item->image) : '/assets/images/default.png',
                    'product_code'          => $item->barcode,
                    'vat'                   => $item->vat,
                    'unit'                  => optional($item->unit)->name,
                    'product_price'         => $item->sell_price,
                    'product_cost'          => $item->purchase_price,
                    'product_description'   => $item->description,
                    'stock'                 => $item->available_qty ?? 0,
                ];
            });
         //}
    }
}
