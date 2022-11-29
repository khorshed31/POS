@extends('layouts.master')


@section('title', 'Inventory Report')

@section('page-header')
    <i class="fa fa-info-circle"></i> Inventory Report
@stop

@section('content')

    <div class="row">
        <div class="col-md-12">


            @include('partials._alert_message')




            <div class="widget-box">


                <!-- header -->
                <div class="widget-header">
                    <h4 class="widget-title"> @yield('page-header')</h4>

                </div>


                <!-- body -->
                <div class="widget-body">
                    <div class="widget-main">


                        <!-- Searching -->
                        <div class="row">
                            <div class="col-sm-12">
                                <form action="">

                                    <table class="table table-bordered">
                                        <tr>
                                            <td class="row">
                                                <div class="col-md-4">
                                                    <select name="name" class="select2 form-control"
                                                            data-placeholder="-Select Product-">
                                                        <option value=""></option>

                                                        @foreach ($products as $key => $item)
                                                            <option value="{{ $item }}" {{ request('name') == $item ? 'selected' : '' }}>
                                                                {{ $item }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <select name="unit_id" class="select2 form-control"
                                                            data-placeholder="-Select Unit-">
                                                        <option value=""></option>

                                                        @foreach ($units as $key => $item)
                                                            <option value="{{ $key }}" {{ request('unit_id') == $key ? 'selected' : '' }}>
                                                                {{ $item }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <select name="category_id" class="select2 form-control"
                                                            data-placeholder="-Select Category-">
                                                        <option value=""></option>

                                                        @foreach ($categories as $key => $item)
                                                            <option value="{{ $key }}" {{ request('category_id') == $key ? 'selected' : '' }}>
                                                                {{ $item }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="btn-group btn-corner" style="float: right">
                                                    <button type="submit" class="btn btn-xs btn-success">
                                                        <i class="fa fa-search"></i> Search
                                                    </button>
                                                    <a href="{{ request()->url() }}" class="btn btn-xs">
                                                        <i class="fa fa-refresh"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </form>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-xs-12">

                                <div class="table-responsive" style="border: 1px #cdd9e8 solid;">

                                    <!-- Table -->
                                    <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                                        <thead>

                                            <tr>
                                                <th>Sl</th>
                                                <th>P. Name</th>
                                                <th>P. Barode</th>
                                                <th>P. Unit</th>
                                                <th class="text-right"> Opening Qty</th>
                                                <th class="text-right">Purchased Qty</th>
                                                <th class="text-right">Sold Qty</th>
                                                <th class="text-right">Sold Exchange Qty</th>
                                                <th class="text-right">Sold Return Qty</th>
                                                <th class="text-right">Purchased Return Qty</th>
                                                <th class="text-right">Available Qty</th>
                                                <th class="text-right">Product Cost</th>
                                                <th class="text-right">Value</th>
                                            </tr>
                                        </thead>

                                        <tbody>

                                            @php
                                                $total_value = 0;
                                                $total_opening_qty = 0;
                                                $total_purchase_qty = 0;
                                                $total_purchase_return_qty = 0;
                                                $total_sold_qty = 0;
                                                $total_sold_return_qty = 0;
                                                $total_available_qty = 0;
                                                $total_cost = 0;

                                            @endphp

                                            @forelse ($reports as $key => $item)
                                                @php
                                                    $total_value += $total = $item->purchase_price * $item->available_qty;
                                                    $total_opening_qty += $item->opening_qty;
                                                    $total_purchase_qty += $item->purchased_qty;
                                                    $total_purchase_return_qty += $item->purchase_return_qty;
                                                    $total_sold_qty += $item->sold_qty;
                                                    $total_sold_return_qty += $item->sold_return_qty;
                                                    $total_available_qty += $item->available_qty;
                                                    $total_cost += $item->purchase_price;
                                                @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $item->name }}</td>
                                                    <td>{{ $item->barcode  }}</td>
                                                    <td>{{ optional($item->unit)->name }}</td>
                                                    <td class="text-right">{{ $item->opening_qty }}</td>
                                                    <td class="text-right">{{ $item->purchased_qty }}</td>
                                                    <td class="text-right">{{ $item->sold_qty }}</td>
                                                    <td class="text-right">{{ $item->sold_exchange_qty }}</td>
                                                    <td class="text-right">{{ $item->sold_return_qty }}</td>
                                                    <td class="text-right">{{ $item->purchase_return_qty }}</td>
                                                    <td class="text-right">{{ $item->available_qty }}</td>
                                                    <td class="text-right">{{ number_format($item->purchase_price) }}
                                                    </td>
                                                    <td class="text-right">
                                                        {{ number_format($total) }}
                                                    </td>

                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="30" class="text-center text-danger py-3"
                                                        style="background: #eaf4fa80 !important; font-size: 18px">
                                                        <strong>No records found!</strong>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                         <tfoot>
{{--                                            <tr>--}}
{{--                                                <th colspan="4"><strong>Total:</strong></th>--}}
{{--                                                <th class="text-right">{{ $total_opening_qty }}</th>--}}
{{--                                                <th class="text-right">{{ $total_purchase_qty }}</th>--}}
{{--                                                <th class="text-right">{{ $total_sold_qty }}</th>--}}
{{--                                                <th class="text-right">{{ $total_sold_return_qty }}</th>--}}
{{--                                                <th class="text-right">{{ $total_purchase_return_qty }}</th>--}}
{{--                                                <th class="text-right">{{ $total_available_qty }}</th>--}}
{{--                                                <th class="text-right">{{ $total_cost }}</th>--}}
{{--                                                <th class="text-right">{{ $total_value }}</th>--}}
{{--                                            </tr>--}}
{{--                                            <tr>--}}
{{--                                                <th colspan="4"><strong>Grand Total:</strong></th>--}}
{{--                                                <th class="text-right">{{ $grand_inventories->sum('opening_qty') }}</th>--}}
{{--                                                <th class="text-right">{{ $grand_inventories->sum('purchased_qty') }}</th>--}}
{{--                                                <th class="text-right">{{ $grand_inventories->sum('sold_qty') }}</th>--}}
{{--                                                <th class="text-right">{{ $grand_inventories->sum('sold_return_qty') }}</th>--}}
{{--                                                <th class="text-right">{{ $grand_inventories->sum('purchase_return_qty') }}</th>--}}
{{--                                                <th class="text-right">{{ $grand_inventories->sum('available_qty') }}</th>--}}
{{--                                                <th class="text-right">{{ $grand_inventories->sum('purchase_price') }}</th>--}}
{{--                                                <th class="text-right">{{ $grand_total_cost }}</th>--}}
{{--                                            </tr>--}}

                                            <tr>
                                                <th colspan="12">
                                                    <a  href="{{ route('dokani.reports.inventory') }}?export_type=inventory-excel&{{ http_build_query(request()->except('export_type', '_token')) }}"
                                                        title="Excel" style="padding-left: 10px" target="_blank">
                                                        <img src="{{ asset('assets/images/export-icons/excel-icon.png') }}" alt="">
                                                    </a>&nbsp;&nbsp;&nbsp;
                                                    <a href="{{ route('dokani.reports.inventory') }}?export_type=inventory-pdf&{{ http_build_query(request()->except('export_type', '_token')) }}"
                                                       title="Pdf" target="_blank">
                                                        <img src="{{ asset('assets/images/export-icons/pdf-icon.png') }}" alt="">
                                                    </a>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>

                                    @include('partials._paginate',['data'=> $reports])

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection


@section('js')
    <script>
        /***************/
        $('.show-details-btn').on('click', function(e) {
            e.preventDefault();
            $(this).closest('tr').next().toggleClass('open');
            $(this).find(ace.vars['.icon']).toggleClass('fa-eye').toggleClass('fa-eye-slash');
        });
        /***************/
    </script>

@stop
