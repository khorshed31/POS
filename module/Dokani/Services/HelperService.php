<?php

namespace Module\Dokani\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Module\Dokani\Models\ManageSmsApi;
use Module\Dokani\Models\Order;
use Module\Dokani\Models\Sale;
use Module\Dokani\Models\Product;
use Module\Dokani\Models\CashFlow;
use Module\Dokani\Models\Purchase;


class HelperService
{


    public function dahboardData()
    {

        $startDateOfMonth       = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDateOfMonth         = Carbon::now()->endOfMonth()->format('Y-m-d');
        $today                  = Carbon::now()->format('Y-m-d');
        $yesterday              = Carbon::yesterday()->format('Y-m-d');
        $cashFlow               = CashFlow::where('dokan_id', dokanId());
        $todaySales             = Sale::where('dokan_id', dokanId());
        $yesterdaySales         = Sale::where('dokan_id', dokanId());
        $todayPurchase          = Purchase::where('dokan_id', dokanId());
        $yesterdayPurchase      = Purchase::where('dokan_id', dokanId());

        $data['total_product']      = Product::dokani()->count();
        $data['today_sale']         = $todaySales->whereDate('date', $today)->sum('payable_amount');
        $data['yesterday_sale']     = $yesterdaySales->whereDate('date', $yesterday)->sum('payable_amount');

        $data['today_purchase']     = $todayPurchase->whereDate('date',$today)->sum('payable_amount');
        $data['yesterday_purchase'] = $yesterdayPurchase->whereDate('date', $yesterday)->sum('payable_amount');

        $data['today_income']       = $todaySales->where('date',$today)->withCount(['details as total_qty' => function ($query) {
            $query->select(DB::raw('SUM(quantity)'));
        }])->get();

        $data['yesterday_income']   = $yesterdaySales->where('date',$yesterday)->withCount(['details as total_qty' => function ($query) {
            $query->select(DB::raw('SUM(quantity)'));
        }])->get();

        $data['net_income']   = Sale::where('dokan_id', dokanId())->withCount(['details as total_qty' => function ($query) {
            $query->select(DB::raw('SUM(quantity)'));
        }])->get();
//
        $data['today_expense']      = 0;
        $data['yesterday_expense']  = 0;
//        $data['net_income']         = Sale::dokani()->with(['details' => function ($q) {
//            $q->withSum('product', 'purchase_price');
//        }])->get();
        $data['net_expense']        = 0;
        $data['sms_balance']        = $this->getAPIBalance();
        $data['online_order']       = Order::where('dokan_id', dokanId())->count();
        $data['cash_flows']         = CashFlow::where('dokan_id', dokanId())->where('date', '>=', $startDateOfMonth)->where('date', '<=', $endDateOfMonth)->get(['id', 'date', 'amount', 'balance_type']);

        //dd($today);
        return $data;
    }


    public function getAPIBalance()
    {

        $sms_api = ManageSmsApi::where('dokan_id', dokanId())->first();
        if ($sms_api){
            $client = new \GuzzleHttp\Client();
            $url = $sms_api->balance_url;
            $data = $client->request('get',$url);
            $success = $data->getBody();
            return json_decode($success)->Balance;
        }
        else {

            return 0;
        }

    }

    public function sidebars()
    {
        return (object)[
            [
                'name' => 'Sale',
                'url' => '#',
                'icon' => 'fa fa-shopping-bag',
                'target' => '',
                'child' => [
                    [
                        'name' => 'POS Sale',
                        'url' => route('dokani.sales.create'),
                        'icon' => 'fa-caret-right',
                        'target' => '',
                    ],
                    [
                        'name' => 'Sales list',
                        'url' => route('dokani.sales.index'),
                        'icon' => 'fa-caret-right',
                        'target' => '_blank',
                    ],
                    [
                        'name' => 'Due Collection',
                        'url' => route('dokani.collections.create'),
                        'icon' => 'fa-caret-right',
                        'target' => '_blank',
                    ],
                ],
            ],
        ];
    }
}
