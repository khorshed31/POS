<?php

namespace Module\Permission\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Company;
use App\Models\UserCredential;
use Module\HRM\Models\Department;
use Module\HRM\Models\Designation;
use Module\HRM\Models\Employee\Employee;


use Module\Permission\Models\PermissionFeature;
use App\Models\User;
use App\Traits\CheckPermission;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Module\Permission\Models\EmployeePermission;
use Module\Permission\Models\Module;

class UserPermissionController extends Controller
{
    use CheckPermission;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index($id)
    {
        $this->hasAccess("permission.accesses.create");     // check permission

        $user                 = User::where('id', $id)->where('status', 1)->first();

        $data['user']         = $user;
        $data['modules']      = Module::with('submodules.parent_permissions.permissions')->get();


        $data['isPermitted']      = $user->permissions()->pluck('slug')->toArray();
        $data['hasCompanies']     = $user->companies()->pluck('name')->toArray();
        $data['hasDepartments']   = $user->departments()->pluck('name')->toArray();
        $data['hasDesignations']  = $user->designations()->pluck('name')->toArray();

        $user = User::where('type', 'user')->pluck('id');


        return view('access.create', $data);
    }





    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $this->hasAccess("permission.accesses.create");     // check permission

        $user = User::where('type', 'user')->pluck('id');


        $data['modules']                = Module::with('submodules.permissions')->active()->get();



        return view('access.create', $data);
    }











    public function edit($id)
    {
        $this->hasOwnerAccess("permission.accesses.edit");     // check permission

        $data['user']         = User::find($id);
        $data['modules']      = Module::with('submodules.parent_permissions.permissions')->active()->get();
//dd($data['modules']);

        $data['isPermitted']            = User::find($id)->permissions()->pluck('slug')->toArray();


        return view('access.edit', $data);
    }






    public function update(Request $request, $id)
    {


        try {

            $user_created = User::find($id);

            $user_created->permissions()->sync($request->permissions);



            if (auth()->id() == $id) {
                session()->forget('slugs');
            }


        } catch (Exception $ex) {
            return redirect()->back()->with('error', 'Some error, please check');
        }
        return redirect()->back()->with('message', 'Permission Update Successfull');

    }
}
