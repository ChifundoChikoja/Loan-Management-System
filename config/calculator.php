<?php


function calculator($principal, $paydays){
    $values = array();
    $interest_rate = 0.01561;


    $PR = $principal * $interest_rate;
    $r1 = 1+$interest_rate;
    $a = pow ($r1,($paydays* -1));

    $b = 1-$a;

    $daily = round(($PR / $b),2);
    $daily_3 = ($daily*5)/3;
    $daily_3 = round($daily_3,2);
    $tot_repayment = round(($daily * $paydays),2);
    $total_int_charged = round(($tot_repayment - $principal),2);
    $effective_int_per_month = round(($total_int_charged/$principal),2);

    // populating the array with calculated values
    array_push($values,$daily_3);
    array_push($values,$tot_repayment);
    array_push($values,$total_int_charged);
    array_push($values,$effective_int_per_month);

    return $values;
}