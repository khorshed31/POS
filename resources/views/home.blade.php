@extends('layouts.master')
@section('css')
    <style>
        .infobox {
            /* height: fit-content !important; */
            height: 100px !important;
            /* width: fit-content !important; */
            display: inline-block;
            vertical-align: top;
            width: 60px;
            border: 3px solid !important;
            background: antiquewhite !important;
            /* padding: 15px; */
        }

        .infobox-small {
            width: 100% !important;
        }


    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">



            <div class="widget-box">

                <!-- body -->
                <div class="widget-body">
                    <div class="widget-main">

                        <div class="row">
                            <div class="space-6"></div>
                            <div class="col-sm-12">
                                <div class="infobox infobox-green">
                                    <div class="infobox-icon infobox-dark">
                                        <i class="ace-icon fa fa-shopping-cart"></i>
                                    </div>

                                    <div class="infobox-data">
                                        <div class="infobox-content">TOTAL PRODUCT</div>
                                        <span class="infobox-data-number">{{ $total_product ?? '0' }}</span>
                                    </div>


                                </div>



                                <div class="infobox infobox-blue">
                                    <div class="infobox-icon">
                                        <i class="ace-icon fa fa-shopping-cart"></i>
                                    </div>

                                    <div class="infobox-data">
                                        <div class="infobox-content">YESTERDAY</div>
                                        <div class="infobox-content-body"><strong>SALES:
                                            </strong><span>{{ number_format($yesterday_sale, 2)}}</span>
                                        </div>
                                        <div class="infobox-content-body"><strong>PURCHASE:
                                            </strong><span>{{ number_format($yesterday_purchase, 2) }}</span></div>
                                    </div>


                                </div>

                                <div class="infobox infobox-pink">
                                    <div class="infobox-icon">
                                        <i class="ace-icon fa fa-shopping-cart"></i>
                                    </div>

                                    <div class="infobox-data">
                                        <div class="infobox-content">TODAY</div>
                                        <div class="infobox-content-body"><strong>SALES:
                                            </strong><span>{{ number_format($today_sale, 2) }}</span></div>
                                        <div class="infobox-content-body"><strong>PURCHASE:
                                            </strong><span>{{ number_format($today_purchase, 2) }}</span></div>
                                    </div>

                                </div>

                                @php
                                    $profit_total = 0;
                                @endphp

                                @foreach ($today_income as $key => $item)
                                    @php
                                        // $grand_total += $total = $item->payable_amount * $item->product->sell_price;
                                        $purchase = 0;
                                         foreach ($item->details as $detail){
                                             $purchase += $detail->buy_price * $detail->quantity;

                                         }
                                        $profit_total += $item->payable_amount - $purchase;

                                    @endphp
                                @endforeach

                                <div class="infobox infobox-pink">
                                    <div class="infobox-icon">
                                        <i class="ace-icon fa fa-exchange"></i>
                                    </div>

                                    <div class="infobox-data">
                                        <div class="infobox-content">TODAY INCOME</div>
                                        <div class="infobox-content-body"><strong>
                                            </strong><span>{{ number_format($profit_total,2) ?? 0 }}</span></div>
                                        {{-- <div class="infobox-content-body"><strong>INCOME:
                                            </strong><span>{{ $today_income ?? 0 }}</span></div> --}}
                                    </div>

                                </div>
                                <div class="infobox infobox-pink">
                                    <div class="infobox-icon">
                                        <i class="ace-icon fa fa-exchange" style="height: 35px;width:40px"></i>
                                    </div>
                                    @php
                                        $profit_total1 = 0;
                                    @endphp

                                    @foreach ($yesterday_income as $key => $item)
                                        @php
                                            // $grand_total += $total = $item->payable_amount * $item->product->sell_price;
                                            $purchase1 = 0;
                                             foreach ($item->details as $detail){
                                                 $purchase1 += $detail->buy_price * $detail->quantity;

                                             }
                                            $profit_total1 += $item->payable_amount - $purchase1;

                                        @endphp
                                    @endforeach
                                    <div class="infobox-data">
                                        <div class="infobox-content" style="font-size: 12px;">YESTERDAY INCOME</div>
                                        <div class="infobox-content-body"><strong>
                                            </strong><span>{{ number_format($profit_total1,2) ?? 0 }}</span></div>
                                        {{-- <div class="infobox-content-body"><strong>INCOME:
                                            </strong><span>{{ $yesterday_income }}</span></div> --}}
                                    </div>

                                </div>
                                <div class="infobox infobox-pink">
                                    <div class="infobox-icon">
                                        <i class="ace-icon fa fa-exchange"></i>
                                    </div>
                                    @php
                                        $profit_total2 = 0;
                                    @endphp

                                    @foreach ($net_income as $key => $item)
                                        @php
                                            // $grand_total += $total = $item->payable_amount * $item->product->sell_price;
                                            $purchase2 = 0;
                                             foreach ($item->details as $detail){
                                                 $purchase2 += $detail->buy_price * $detail->quantity;

                                             }
                                            $profit_total2 += $item->payable_amount - $purchase2;

                                        @endphp
                                    @endforeach

                                    <div class="infobox-data">
                                        <div class="infobox-content">NET INCOME</div>
                                        <div class="infobox-content-body"><strong>
                                            </strong><span></span></div>
                                         <div class="infobox-content-body"><strong>INCOME:
                                            </strong><span>{{ number_format($profit_total2,2) ?? 0 }}</span></div>
                                    </div>

                                </div>


                                <div class="infobox infobox-green">
                                    <div class="infobox-icon">
                                        <i class="ace-icon fa fa-shopping-cart"></i>
                                    </div>

                                    <div class="infobox-data">
                                        <div class="infobox-content">ONLINE ORDER</div>
                                        <div class="infobox-content-body"><strong>{{ $online_order }}</strong></div>
                                    </div>

                                </div>

                                <div class="infobox infobox-green">
                                    <div class="infobox-icon">
                                        <i class="ace-icon fa fa-send"></i>
                                    </div>

                                    <div class="infobox-data">
                                        <div class="infobox-content">SMS Balance</div>
                                        <div class="infobox-content-body"><strong>{{ $sms_balance }}</strong></div>
                                    </div>

                                </div>
                                <div class="space-6"></div>

                            </div>
                        </div>



                        <div class="row mt-3">
                            <div class="col-sm-12">
                                <div class="widget-box transparent">
                                    <div class="widget-header">
                                        <h4 class="widget-title">
                                            <i class="ace-icon fa fa-signal"></i>
                                            Cash Flow
                                        </h4>
                                        <span id="thismonth" style="font-size: 18px;font:bold"></span>
                                        <select name="monthselection" id="monthselection" class="chosen-selecst-280">
                                            <option value="1">January</option>
                                            <option value="2">February</option>
                                            <option value="3">March</option>
                                            <option value="4">April</option>
                                            <option value="5">May</option>
                                            <option value="6">Jun</option>
                                            <option value="7">July</option>
                                            <option value="8">August</option>
                                            <option value="9">September</option>
                                            <option value="10">October</option>
                                            <option value="11">November</option>
                                            <option value="12">December</option>
                                        </select>
                                        <!-- <div class="widget-toolbar">
                                                                                                                                                                                                                                                                                                                                                                                        <a href="#" data-action="collapse">
                                                                                                                                                                                                                                                                                                                                                                                            <i class="ace-icon fa fa-chevron-up"></i>
                                                                                                                                                                                                                                                                                                                                                                                        </a>
                                                                                                                                                                                                                                                                                                                                                                                    </div> -->
                                    </div>

                                    <div class="widget-body">
                                        <canvas id="myChart" width="400" height="150"></canvas>
                                        <!-- /.widget-main -->
                                    </div><!-- /.widget-body -->
                                </div><!-- /.widget-box -->
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>

    <script>
        var today = new Date()
        var current_year = today.getFullYear()
        var current_month = today.getMonth() + 1
        var end = new Date(current_year, current_month, 0).getDate(); // end date of month
        // current month all days return
        function daysInMonth(month, year) {
            return new Date(year, month, 0).getDate();
        }

        function alldays() {

            days = daysInMonth(today.getMonth() + 1, today.getFullYear())
                result = Array.from({
                    length: days
                }, (_, i) => i + 1);
            return result;

        }
    </script>

    <script>
        $(document).on('ready', function() {
            // const monthNames = ["January", "February", "March", "April", "May", "June",
            //     "July", "August", "September", "October", "November", "December"
            // ];



            $('#thismonth').html('(Current Month: ' + today.toLocaleString('en-us', {
                month: 'long'
            }) + ')');
            $('#monthselection').val(today.getMonth() + 1).prop('selected', true);

            var result = [];
            var cash_flow_all = '<?php echo $cash_flows; ?>';
            var cash_flow = JSON.parse(cash_flow_all)
            var balance = []

            for (let i = 1; i <= end; i++) {
                var total = []
                var date = today.getFullYear() + '-' + (today.getMonth() < 10 ? + (today.getMonth() + 1) :
                    today
                        .getMonth() + 1) + '-' + (i < 10 ? '0' + i : i);

                $.each(cash_flow, function(key, val) {
                    console.log(date+' '+val.date)
                    if (date === val.date) {
                        total = val.amount;
                    } else {
                        total += 0
                    }
                })

                if (total < -1) {
                    total = -(total)

                }
                balance.push(total)
            }
            // make chart

            var ctx = document.getElementById('myChart');
            var myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: alldays(),
                    datasets: [{
                        label: 'Cash Flow',
                        data: balance,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                color: function(context) {
                    var index = context.dataIndex;
                    var value = context.dataset.data[index];
                    return value < 0 ? 'red' : // draw negative values in red
                        index % 2 ? 'blue' : // else, alternate values in blue and green
                            'green';
                }

            });

            var chartColors = {
                red: 'rgb(255, 99, 132)',
                green: 'rgb(51, 204, 51)',
                color3: 'rgb(255, 99, 132)'
            };
            //set this to whatever is the deciding color change value
            var dataset = myChart.data.datasets[0];
            for (var i = 0; i < dataset.data.length; i++) {
                if (dataset.data[i] < 30) {
                    dataset.backgroundColor[i] = chartColors.red;
                    dataset.borderColor[i] = chartColors.red;
                } else if ((dataset.data[i] > 31) && (dataset.data[i] <= 60)) {
                    dataset.backgroundColor[i] = chartColors.green;
                } else {
                    dataset.backgroundColor[i] = chartColors.green;
                }
            }
            myChart.update();
        })



    </script>
@endsection
