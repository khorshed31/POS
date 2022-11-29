<?php

namespace Module\Dokani\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Module\Dokani\Models\Brand;

class BrandController extends Controller
{




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
        $data['brands'] = Brand::latest()->dokani()->paginate(25);
        return $data;
    }













    /*
     |--------------------------------------------------------------------------
     | CREATE METHOD
     |--------------------------------------------------------------------------
    */
    public function create()
    {
        $data = [];
        return view('', $data);
    }













    /*
     |--------------------------------------------------------------------------
     | STORE METHOD
     |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'name'    =>  'required',
        ]);

        if ($validator->fails()) {

            return response()->json([

                'data'      => $validator->errors()->first(),
                'message'   => "Validation Error",
                'status'    => 0,
            ]);
        }
        try {

            $brand = $this->storeOrUpdate($request);

                return response()->json([

                    'data'      => $brand,
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
     | SHOW METHOD
     |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        return response()->json([

            'data'      => Brand::find($id),
            'message'   => "Success",
            'status'    => 1,
        ]);
    }













    /*
     |--------------------------------------------------------------------------
     | EDIT METHOD
     |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        # code...
    }













    /*
     |--------------------------------------------------------------------------
     | UPDATE METHOD
     |--------------------------------------------------------------------------
    */
        public function updateBrand(Request $request, $id)
    {

        $validator = Validator::make($request->all(),[
            'name'          => 'required',
        ]);
        if ($validator->fails()) {

            return response()->json([

                'data'      => $validator->errors()->first(),
                'message'   => "Validation Error",
                'status'    => 0,
            ]);
        }
        try {
            $brand = $this->storeOrUpdate($request, $id);
            if ($brand) {
                return response()->json([

                    'data'      => $brand,
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
     | DELETE/DESTORY METHOD
     |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        try {
            $brand = Brand::find($id);

            $brand->delete();
            return response()->json([

                'data'      => 'Brand delete success',
                'message'   => "Success",
                'status'    => 1,
            ]);
        } catch (\Throwable $th) {
            return response()->json([

                'data'      => 'Brand was used another table',
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
        $data = $request->all();


        $brand = Brand::updateOrCreate([
            'id'    => $id
        ], $data);

        return $brand ;
    }




}
