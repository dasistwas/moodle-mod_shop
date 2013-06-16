<?php
require_once (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once (dirname(__FILE__) . '/lib.php');
require_once (dirname(__FILE__) . '/paypal.php');
$Data = unserialize(stripslashes($_POST["custom"]));
$paypalReceiver = $_POST["business"];
$service = $Data[0];
$priceperunit = $Data[1];
$units = $Data[2];
$buyerid = $Data[3];
$shopid = $Data[4];
$amount = $units * $priceperunit;

$doCheck = new Paypal($paypalReceiver,NULL,'EUR');
$isPaymentOkay = $doCheck->checkPayment();
$transactionid = shop_logpayment($shopid, $units, $buyerid, $amount); // function is in lib.php

if($isPaymentOkay){
	$stilltopay = floatval($amount) - floatval($_POST["mc_gross"]);
	$teststring = " paymentisOKay!";
	if($stilltopay <= 0){
		shop_logpaymentpaid($transactionid);
	}
}

?>
