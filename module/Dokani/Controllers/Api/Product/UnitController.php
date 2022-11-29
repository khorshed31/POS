<?php

namespace Module\Dokani\Controllers\Api\Product;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\Trycatch;
use Illuminate\Support\Facades\Validator;
use Module\Dokani\Models\Unit;

class UnitController extends Controller
{

    use Trycatch;





    /*
     |--------------------------------------------------------------------------
     | INDEX METHOD
     |--------------------------------------------------------------------------
    */
    public function index()
    {
        return $this->load(Unit::dokani()->latest()->paginate(20));
    }











    /*
     |--------------------------------------------------------------------------
     | STORE METHOD
     |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        try {
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

            $category = Unit::create($request->except('image'));

            if ($category) {
                return response()->json([

                    'data'      => $category,
                    'message'   => "Success",
                    'status'    => 1,
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([

                'data'      => $th->getMessage(),
                'message'   => "Server Error",
                'status'    => 1,
            ]);
        }
    }











    /*
     |--------------------------------------------------------------------------
     | UPDATE METHOD
     |--------------------------------------------------------------------------
    */
    public function update($id, Request $request)
    {
        return $this->load($this->storeOrUpdate($request, $id));
    }





    /*
         |--------------------------------------------------------------------------
         | SHOW METHOD
         |--------------------------------------------------------------------------
        */
    public function show($id)
    {
        # code...
    }






    /*
     |--------------------------------------------------------------------------
     | DELETE/DESTORY METHOD
     |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        try {
            $unit = Unit::find($id);
            $unit->delete();
            return response()->json([

                'data'      => 'Unit delete success',
                'message'   => "Success",
                'status'    => 1,
            ]);
        } catch (\Throwable $th) {
            return response()->json([

                'data'      => 'Unit was used another table',
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
        ]);



        Unit::updateOrCreate([
            'id'    => $id
        ], $data);
    }
}
