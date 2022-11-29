<?php

use Module\Dokani\Models\AccountType;
use Module\Dokani\Models\Account;
use Module\Dokani\Models\Customer;


function redirectIfError($error, $with_input = null)
{
    if (request()->dev == 1) {
        throw $error;
    }
    if ($with_input) {
        return redirect()->back()->withInput(request()->except('image'))->withError($error->getMessage());
    }
    return redirect()->back()->withError($error->getMessage());
}


function dokanId(){

    return auth()->user()->type == 'owner' ? auth()->id() : auth()->user()->dokan_id;
}


function account(){

    return Account::dokani()->pluck('name','id');
}

function accountInfo(){

    return Account::dokani()->get();
}

function cashBalance(){

    return Account::dokani()->where('name', 'Cash')->pluck('balance')->first();
}

function customerPoint(){

    return Customer::dokani()->where('is_default', 1)->pluck('point')->first();
}

function customerDue(){

    return Customer::dokani()->where('is_default', 1)->pluck('balance')->first();
}

function customerId(){

    return Customer::dokani()->where('is_default', 1)->pluck('id')->first();
}

function defaultCustomer(){

    return Customer::dokani()->where('is_default', 1)->first();
}






function fdate($value, $format = null)
{
    if ($value == '') {
        return '';
    }

    if ($format == null) {
        $format = 'd/m/Y';
    }

    return \Carbon\Carbon::parse($value)->format($format);
}


function getInWord($number)
{
    $decimal = round($number - ($no = floor($number)), 2) * 100;
    $hundred = null;
    $digits_length = strlen($no);
    $i = 0;
    $str = array();
    $words = array(0 => '', 1 => 'One', 2 => 'Two',
        3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
        7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
        10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
        13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
        16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
        19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
        40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
        70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');
    $digits = array('', 'Hundred','Thousand','Lakh', 'Crore');
    while( $i < $digits_length ) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += $divider == 10 ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
        } else $str[] = null;
    }
    $Taka = implode('', array_reverse($str));
    $poysa = ($decimal) ? " and " . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' poysa' : '';

    return ($Taka ? $Taka . 'taka ' : '') . $poysa . 'only' ;
}


