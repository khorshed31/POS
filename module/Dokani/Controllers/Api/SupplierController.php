<?php

namespace Module\Dokani\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\FileSaver;
use Module\Dokani\Models\Customer;
use Module\Dokani\Models\Supplier;

class SupplierController extends Controller
{

    use FileSaver;

    private $service;


    /*
     |--------------------------------------------------------------------------
     | CONSTRUCTOR
     |--------------------------------------------------------------------------
    */
    public function __construct()
    {
    }












    /*
     |--------------------------------------------------------------------------
     | INDEX METHOD
     |--------------------------------------------------------------------------
    */
    public function index()
    {
        $data['suppliers'] = Supplier::dokani()->likeSearch('name')->latest()->paginate(25);
        return $data;
    }









    /*
     |--------------------------------------------------------------------------
     | STORE METHOD
     |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        try {
            $supplier = $this->storeOrUpdate($request);
            if ($supplier) {
                return response()->json([

                    'data'      => $supplier,
                    'message'   => "Success",
                    'status'    => 1,
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([

                'data'      => $th->getMessage(),
                'message'   => "Server Error",
                'status'    => 0,
            ]);
        }
    }













    /*
     |--------------------------------------------------------------------------
     | SHOW METHOD
     |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        return response()->json([

            'data'      => Supplier::find($id),
            'message'   => "Success",
            'status'    => 1,
        ]);
    }














    /*
     |--------------------------------------------------------------------------
     | UPDATE METHOD
     |--------------------------------------------------------------------------
    */
    public function updateSupplier($id, Request $request)
    {

        try {
            $this->storeOrUpdate($request, $id);
            return response()->json([

                'data'      => 'Supplier update success',
                'message'   => "Success",
                'status'    => 1,
            ]);
        } catch (\Throwable $th) {
            return response()->json([

                'data'      => $th->getMessage(),
                'message'   => "Server Error",
                'status'    => 0,
            ]);
        }
    }












    /*
     |--------------------------------------------------------------------------
     | DELETE/DESTORY METHOD
     |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        try {
            $supplier = Supplier::find($id);
            if (file_exists($supplier->image)) {
                unlink($supplier->image);
            }
            $supplier->delete();
            return response()->json([

                'data'      => 'Supplier delete success',
                'message'   => "Success",
                'status'    => 1,
            ]);
        } catch (\Throwable $th) {
            return response()->json([

                'data'      => $th->getMessage(),
                'message'   => "Server Error",
                'status'    => 0,
            ]);
        }
    }


    /*
     |--------------------------------------------------------------------------
     | STORE/UPDATE METHOD
     |--------------------------------------------------------------------------
    */
    private function storeOrUpdate($request, $id = null)
    {
        $data = $request->validate([
            'name'          => 'required',
            'mobile'        => 'nullable',
            'address'       => 'nullable',
        ]);

        $data['opening_due'] =  $request->opening_due ?? 0;



        $supplier = Supplier::updateOrCreate([
            'id'    => $id
        ], $data);

        $this->upload_file($request->image, $supplier, 'image', 'suppliers');
        return $supplier;
    }
}
