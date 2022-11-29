<?php

namespace Module\Dokani\Services\Reports;

use Module\Dokani\Models\Product;
use Illuminate\Support\Facades\DB;
use Module\Dokani\Models\CashFlow;
use Module\Dokani\Models\ProductLedger;
use Module\Dokani\Models\ProductStockLog;

class InventoryReportService
{
    public function inventory()
    {
        return Product::dokani()->searchByFields(['category_id','unit_id','name'])
            ->withCount(['stocks as opening_qty' => function ($query) {
                $query->select(DB::raw('SUM(opening_quantity)'));
            }])
            ->withCount(['stocks as available_qty' => function ($query) {
                $query->select(DB::raw('SUM(available_quantity)'));
            }])
            ->withCount(['stocks as sold_qty' => function ($query) {
                $query->select(DB::raw('SUM(sold_quantity)'));
            }])
            ->withCount(['stocks as sold_exchange_qty' => function ($query) {
                $query->select(DB::raw('SUM(sold_exchange_quantity)'));
            }])
            ->withCount(['stocks as purchased_qty' => function ($query) {
                $query->select(DB::raw('SUM(purchased_quantity)'));
            }])
            ->withCount(['stocks as sold_return_qty' => function ($query) {
                $query->select(DB::raw('SUM(sold_return_quantity)'));
            }])
            ->withCount(['stocks as purchase_return_qty' => function ($query) {
                $query->select(DB::raw('SUM(purchase_return_quantity)'));
            }])
            ->latest()
            ->paginate(50);
    }



    public function alertInventory()
    {
        return Product::searchByField('category_id')->likeSearch('name')->with('stocks')
            ->dokani()
            ->withCount(['stocks as available_qty' => function ($query) {
                $query->select(DB::raw('SUM(available_quantity)'));
            }])
            ->latest()
            ->paginate(25);

    }




    public function productLedger($request)
    {
//        return ProductLedger::dokani()->with('product.stocks')
//                            ->where('product_id',$request->product_id)
//                            ->searchByField('product_id')
//                            ->searchFromRelation('product','unit_id')
//                            ->dateFilter('date')
//                            ->orderBy('id','desc')
//                            ->paginate(25);

        return ProductStockLog:: dokani()->with('product.stocks')
                            ->where('product_id',$request->product_id)
                            ->searchByField('product_id')
                            ->searchFromRelation('product','unit_id')
                            ->dateFilter('date')
                            ->orderBy('date')
                            ->paginate(25);

    }

}
