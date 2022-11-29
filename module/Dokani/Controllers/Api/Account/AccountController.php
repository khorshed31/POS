<?php


namespace Module\Dokani\Controllers\Api\Account;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Module\Dokani\Models\Account;
use Module\Dokani\Models\AccountType;

class AccountController extends Controller
{



    public function index(){

        $data['accounts'] = Account::dokani()->get();

        return response()->json([
            'data'      => $data,
            'message'   => "Success",
            'status'    => 1,
        ]);

    }



//    public function update(Request $request, $id) {
//
//
//
//        $account = Account::where('id', $id)->first();
//
//
//
//        if ($account){
//            $account->opening_balance = $request->opening_balance;
//            $account->balance = $account->balance + $request->opening_balance;
//            $account->status = 1 ;
//            $account->update();
//        }
//        else{
//
//            Account::create([
//
//                'dokan_id'              => dokanId(),
//                'account_type_id'       => $request->account_type_id,
//                'opening_balance'       => $request->opening_balance,
//                'balance'               => $request->opening_balance,
//                'status'                => 1,
//            ]);
//        }
////            ->update(['opening_balance' => $request->opening_balance, 'status' => 1]);
//
//        return response()->json([
//            'message'   => "Success",
//            'status'    => 1,
//        ]);
//    }

}
