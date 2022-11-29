<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>{{ $sale->invoice_no }}</title>




    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @media print {
            #invoice {
                margin-top: 0 !important;
                margin-bottom: 0 !important;
                padding-top: 2px !important;
            }

            .company-logo {
                margin-top: 0 !important;
                padding-top: 0 !important;
            }


            .no-print,
            .no-print * {
                display: none !important;
            }

            .container {
                height: 100%;
                width: 58mm;
            }

        }

        body {
            background: #efefef;
            font-family: 'Fira Code', monospace;
            font-size: .8rem;
            font-weight: 600;
        }

        .row:after {
            content: "";
            display: table;
            clear: both;
        }

        .col-2 {
            float: left;
            width: 16.6666666667%;
        }

        .col-3 {
            float: left;
            width: 25%;
        }

        .col-4 {
            float: left;
            width: 33.3333333333%;
        }

        .col-6 {
            float: left;
            width: 50%;
        }

        .col-8 {
            float: left;
            width: 66.6666666666%;
        }

        .col-9 {
            float: left;
            width: 75%;
        }

        .col-10 {
            float: left;
            width: 83.3333333333%;
        }

        #invoice {
            background: #ffffff;
            width: 302.36px;
            min-height: 100px;
            margin: 0 auto;
            margin-top: 10px;
            margin-bottom: 50px;
            padding: 5px;
        }

        .container {
            height: 100%;
        }

        .company-logo {
            width: 60px;
            margin: 0px auto;
            margin-top: 5px;
        }

        .logo {
            width: 100%;
        }

        .company-info {
            font-size: .6rem;
            font-weight: 700;
            text-align: center;
        }

        .receipt-heading {
            text-align: center;
            font-weight: 700;
            margin: 0 auto;
            margin-top: 2px;
            max-width: 450px;
            position: relative;
        }


        .invoice-info {
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .invoice-info .date {
            text-align: right;
        }

        table {
            width: 100%;
        }

        table,
        th {
            border-top: 1px solid black;
            border-bottom: 1px solid black;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 5px;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .invoice-price {
            padding: 5px;
            margin-top: 10px;
            margin-bottom: 10px;
            text-align: right;
        }

        .footer {
            /* margin: -25px; */
            font-size: .7rem;
            padding: 1rem;
            text-align: center;
        }


        .no-print {
            text-align: center;
            margin-top: 10px;
        }

        .no-print .btn {
            border: none;
            color: white;
            padding: 6px 16px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }

        .no-print .btn.btn-print {
            background-color: #4CAF50;
        }

        .no-print .btn.btn-back {
            background-color: rgb(250, 225, 0);
        }

    </style>

    <script>
        window.console = window.console || function(t) {};
    </script>
    <script>
        if (document.location.search.match(/type=embed/gi)) {
            window.parent.postMessage("resize", "*");
        }
    </script>
</head>

<body>


<div class="no-print">
    <a href="javascript:void(0)" onclick="window.print()" class="btn btn-print"> Print</a>
    <a href="{{ route('dokani.sales.index') }}" class="btn btn-back">Back</a>
    <a onclick="window.close();"
       style=" background-color: red; border: none;color: white; padding: 6px 12px;text-align: center;text-decoration: none;display: inline-block;font-size: 16px;cursor: pointer; border-radius: 5px;">Close</a>
</div>
<?php $dokan_user = \App\Models\User::query()->where('id', dokanId())->first() ?>

<section id="invoice">
    <div class="container">
        <div class="company-logo">
            @if (file_exists($dokan_user->image))
                <img src="{{ asset($dokan_user->image) }}" alt="Logo" class="logo">
            @else
                <b>{{ optional($business_settings)->shop_name }}</b>
            @endif

        </div>




        <div class="company-info">
            <p>{{ optional($business_settings)->shop_name }}</p>
            <p>{{ optional($business_settings)->shop_address }}</p>
            <p>{{ optional($business_settings)->business_mobile ?? auth()->user()->mobile }}</p>
        </div>




        <p class="receipt-heading"> Customer Receipt </p>




        <div class="invoice-info">
            <div class="row">
                <div class="col-12 invoice-no">
                    Invoice No: {{ $sale->invoice_no }}<br>
                    Date: {{ \Carbon\Carbon::now()->timezone('Asia/Dhaka')->format('d M, Y, g:i A') }}
                </div>
            </div>
            <p>Customer: {{ optional($sale->customer)->name }}</p>
            <p>Phone: {{ optional($sale->customer)->mobile }}</p>
        </div>




        <div class="product-info">
            <table>

                <thead>
                <tr>
                    <th>SL</th>
                    <th class="text-left">Item Details</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Amount</th>
                </tr>
                </thead>

                <tbody>
                    @php
                        $totalPrice = 0;
                    @endphp
                    @foreach ($sale->details as $detail)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                {{ optional($detail->product)->name }} <br> {{ ($detail->description) }}
                            </td>
                            <td class="text-center">{{ round($detail->price,2) }}</td>
                            <td class="text-center">{{ round($detail->quantity, 2) }}</td>
                            <td class="text-right">{{ round($detail->quantity * $detail->price, 2) }}</td>
                        </tr>
                        @php
                            $totalPrice += $detail->quantity * $detail->price
                        @endphp
                    @endforeach
                </tbody>
            </table>


            <div class="invoice-price">
                <div class="row">
                    <div class="col-9">
                        <p>Subtotal: </p>
                        <p>VAT:</p>
                    </div>
                    <div class="col-3">
                        @php $price = $totalPrice; @endphp
                        {{-- @php $price = number_format($sale->payable_amount - $sale->total_vat, 2, '.', ''); @endphp --}}
                        <p>{{ $price }}</p>
                        <p><span style="font-size: 7px">(৳)</span> {{ round($sale->total_vat, 2) }}</p>
                    </div>
                </div>
                ------------
                <div class="row">
                    <div class="col-9">
                        <p></p><br>
                        <p>Discount:</p>
                        <p>Delivery Change:</p>
                    </div>
                    <div class="col-3">
                        <p>
                            @php $price_after_vat = round($price + $sale->total_vat, 2); @endphp
                            {{ $price_after_vat }}
                        </p>
                        <p><span style="font-size: 7px;">(-)</span> {{ round($sale->discount, 2) }}</p>
                        <p><span style="font-size: 7px;">(+)</span> {{ round($sale->delivery_charge, 2) }}</p>
                    </div>
                </div>
                ------------
                <div class="row">
                    <div class="col-9">
                        <p></p><br>
                        <p>Rounding:</p>
                    </div>
                    <div class="col-3">
                        <p>
                            @php $price_after_discount = round($price_after_vat + $sale->delivery_charge - $sale->discount , 2); @endphp
                            {{ $price_after_discount }}
                        </p>
                        <p><span style="font-size: 7px">(+/-)</span> {{ round(round($price_after_discount), 2) }}</p>
                    </div>
                </div>
                ------------
                <div class="row">
                    <div class="col-9">
                        <p>Total Payable:</p>
                    </div>
                    <div class="col-3">
                        {{-- <p>{{ number_format($sale->payable_amount + $sale->delivery_charge - $sale->discount , 2, '.', '') }}</p> --}}
                        <p>{{ round($price_after_discount , 2) }}</p>
                    </div>
                </div>
                ------------
                <div class="row">
                    <div class="col-9">
                        <p>Paid Amount: </p>
                    </div>
                    <div class="col-3">
                        <p>{{ round($sale->paid_amount - $sale->change_amount, 2) }}</p>
                    </div>
                </div>
            </div>

        </div>




        <div class="footer">
            <p>Thanks For Purchase with {{ optional(auth()->user()->businessProfile)->shop_name }}</p>
            <p>For any queries complaints or feedback.</p>
            <p>Please call {{ auth()->user()->mobile }}</p>
        </div>
    </div>
</section>


<script>
    window.print()
</script>
</body>

</html>
