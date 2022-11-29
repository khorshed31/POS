<?php

namespace Module\HRM\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\FileSaver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Module\HRM\Models\Employee;
use Module\HRM\Services\EmployeeServices;

class EmployeeController extends Controller
{


    use FileSaver;

    private $service;


    /*
     |--------------------------------------------------------------------------
     | CONSTRUCTOR
     |--------------------------------------------------------------------------
    */
    public function __construct(EmployeeServices $employeeServices)
    {
        $this->service = $employeeServices;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['employees'] = Employee::dokani()
            ->searchByFields(['name','status'])
            ->latest()
            ->paginate(25);

        return view('employee.index', $data);
    }







    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('employee.create');
    }









    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //dd($request->all());

        try {
            DB::transaction(function () use ($request) {

                $this->service->storeOrUpdate($request);

                if (isset($request->isUser)){
                    $this->service->isUser($request);
                }
            });
        } catch (\Throwable $th) {
            return $th->getMessage();
        }

        return redirect()->route('dokani.employees.index')->withMessage('Employee added successfully !');
    }









    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }









    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['employee'] = Employee::dokani()->where('id',$id)
            ->with('user')
            ->first();

        return view('employee.edit',$data);
    }










    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            DB::transaction(function () use ($request,$id) {

                $this->service->storeOrUpdate($request,$id);

                if (isset($request->isUser)){
                    $this->service->isUser($request,$id);
                }
            });
        } catch (\Throwable $th) {
            return $th->getMessage();
        }

        return redirect()->route('dokani.employees.index')->withMessage('Employee added successfully !');
    }










    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {

                $employee = Employee::dokani()->where('id',$id)
                    ->with('user')
                    ->first();

                if (file_exists($employee->image)) {
                    unlink($employee->image);
                }
                if (file_exists($employee->nid_image)) {
                    unlink($employee->nid_image);
                }
                $employee->delete();

                if ($employee->user){
                    $employee->user->delete();
                }

            });

        } catch (\Throwable $th) {

            return redirect()->back()->withError($th->getMessage());
        }

        return redirect()->route('dokani.employees.index')->withMessage('Employee delete success !');
    }


}
