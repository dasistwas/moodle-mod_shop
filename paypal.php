<?php

class Paypal {
	private $Receiver;
	private $Cmd;
	private $Currency;
	private $Custom;
	private $Tax;
	private $Shipping;
	
	private $Items;
	private $DisplayCart;
	private $MinGiftPrice = '5';
	private $MaxGiftPrice = '100';
	
	private $CancelByUser = false;
	private $RetryPayment = true;
	private $UserNote = false;
	private $TrialCost;
	private $TrialPeriod;
	private $TrialTimes;
	private $RegularCost;
	private $RegularPeriod;
	private $RegularTimes;
	
	private $Sandbox = false;
	private $ShippingAddress = 1; // 0 - Prompted for address but not needed, 1 - No address, 2 - Must enter address
	private $CustomButton;
	
	// IPN - Answer from Paypal
	private $ReturnMethod = 2; // 2 - POST, 1 - GET
	private $ShoppingPage;
	private $ThanksPage;
	private $NotifyPage;
	private $CancelPage;
	
	const BuyNow = "_xclick";
	const Cart = "_cart";
	const Donate = "_donations";
	const BuyGiftCertificate = "_oe-gift-certificate";
	const Subscribe = "_xclick-subscriptions";
	const SecureBuyNow = "_s-xclick";
	
	const Days = "D";
	const Weeks = "W";
	const Months = "M";
	const Years = "Y";
	
	function __get($prop_name){
		if (isset($this->$prop_name)) {
			return $this->$prop_name;
		}else{
			return NULL;
		}
	}

	function __set($prop_name, $prop_value){
		$this->$prop_name = $prop_value;
	}
	
	public function __construct($Receiver, $Type, $Currency, $Custom = ''){
		$this->Items = array();
		$this->Receiver = $Receiver;
		$this->Cmd = $Type;
		$this->Currency = $Currency;
		$this->Custom = $Custom;
	}
	
	public function AddItem(PaypalItem $Item){
		$this->Items[] = $Item;
	}
	
	function getLink(){
		$url = $this->getPaypal();
		$link = 'https://'.$url.'/cgi-bin/webscr?';
		
		$link .= "business=".$this->Receiver."&";
		$link .= "cmd=".$this->Cmd."&";
		$link .= "currency_code=".$this->Currency."&";
		$link .= "no_shipping=".$this->ShippingAddress."&";
		$link .= "custom=".$this->Custom."&";
		if(isset($this->Tax)){ $link .= "tax=".$this->Tax."&"; }
		if(isset($this->Shipping)){ $link .= "shipping=".$this->Shipping."&"; }
		
		if($this->Cmd == self::Cart AND count($this->Items) == 1){ $link .= "add=1&"; }
		else if($this->Cmd == self::Cart AND count($this->Items) > 1){ $link .= "upload=1&"; }
		
		if($this->Cmd == self::Subscribe){
			$link .= "src=".($this->CancelByUser ? "1":"0")."&"; 
			$link .= "sra=".($this->RetryPayment ? "1":"0")."&"; 
			$link .= "no_note=".($this->UserNote ? "0":"1")."&"; 
			if(isset($this->TrialCost)){ $link .= "a1=".$this->TrialCost."&"; }
			if(isset($this->TrialPeriod)){ $link .= "p1=".$this->TrialPeriod."&"; }
			if(isset($this->TrialTimes)){ $link .= "t1=".$this->TrialTimes."&"; }
			if(isset($this->RegularCost)){ $link .= "a3=".$this->RegularCost."&"; }
			if(isset($this->RegularPeriod)){ $link .= "p3=".$this->RegularPeriod."&"; }
			if(isset($this->RegularTimes)){ $link .= "t3=".$this->RegularTimes."&"; }
		}
		
		if($this->Cmd == self::BuyGiftCertificate){
			$link .= "min_denom=".$this->MinGiftPrice."&"; 
			$link .= "max_denom=".$this->MaxGiftPrice."&";
		}
		else if(isset($this->DisplayCart)){ $link .= "display=".($this->DisplayCart ? "1" : "0")."&"; 
		}
		
		if(count($this->Items)>1){
			for($i = 0; $i < count($this->Items); $i++){
				$link .= "item_name".$i."=".$this->Items[$i]->ItemName."&";
				$link .= "item_number".$i."=".$this->Items[$i]->ItemNumber."&";
				$link .= "quantity".$i."=".$this->Items[$i]->ItemQuantity."&";
				$link .= "amount".$i."=".$this->Items[$i]->ItemAmount."&";
			}
		}else{
			$link .= "item_name=".$this->Items[0]->ItemName."&";
			$link .= "item_number=".$this->Items[0]->ItemNumber."&";
			$link .= "quantity=".$this->Items[0]->ItemQuantity."&";
			$link .= "amount=".$this->Items[0]->ItemAmount."&";
		}
		
		$link .= "rm=".$this->ReturnMethod."&";
		$link .= "notify_url=".$this->NotifyPage."&";
		if(isset($this->ShoppingPage)){ $link .= "shopping_url=".$this->ShoppingPage."&"; }
		if(isset($this->ThanksPage)){ $link .= "return=".$this->ThanksPage."&"; }
		if(isset($this->CancelPage)){ $link .= "cancel_return=".$this->CancelPage."&"; }
		
		return $link;
	}
	
	function ShowForm(){
		$url = $this->getPaypal();
		$Form  = '<form action="https://'.$url.'/cgi-bin/webscr" method="post" onsubmit="this.target=\'_blank\'; return true;" style="display:inline;">'."\n";
		
		$Form .= "<div>";
		$Form .= "<input type='hidden' name='business' value='".$this->Receiver."'/>";
		$Form .= "<input type='hidden' name='cmd' value='".$this->Cmd."'/>";
		$Form .= "<input type='hidden' name='currency_code' value='".$this->Currency."'/>";
		$Form .= "<input type='hidden' name='custom' value='".$this->Custom."'/>";
		$Form .= "<input type='hidden' name='no_shipping' value='".$this->ShippingAddress."'/>";
		if(isset($this->Tax)){ $Form .= "<input type='hidden' name='tax' value='".$this->Tax."'/>"; }
		if(isset($this->Shipping)){ $Form .= "<input type='hidden' name='shipping' value='".$this->Shipping."'/>"; }
		
		if($this->Cmd == self::Cart AND count($this->Items) == 1){ $Form .= "<input type='hidden' name='add' value='1'/>"; }
		if($this->Cmd == self::Cart AND count($this->Items) > 1){ $Form .= "<input type='hidden' name='upload' value='1'/>"; }
		
		if($this->Cmd == self::Subscribe){
			$Form .= "<input type='hidden' name='src' value='".($this->CancelByUser ? "1":"0")."'/>"; 
			$Form .= "<input type='hidden' name='sra' value='".($this->RetryPayment ? "1":"0")."'/>"; 
			$Form .= "<input type='hidden' name='no_note' value='".($this->UserNote ? "0":"1")."'/>"; 
			if(isset($this->TrialCost)){ $Form .= "<input type='hidden' name='a1' value='".$this->TrialCost."'/>"; }
			if(isset($this->TrialPeriod)){ $Form .= "<input type='hidden' name='t1' value='".$this->TrialPeriod."'/>"; }
			if(isset($this->TrialTimes)){ $Form .= "<input type='hidden' name='p1' value='".$this->TrialTimes."'/>"; }
			if(isset($this->RegularCost)){ $Form .= "<input type='hidden' name='a3' value='".$this->RegularCost."'/>"; }
			if(isset($this->RegularPeriod)){ $Form .= "<input type='hidden' name='t3' value='".$this->RegularPeriod."'/>"; }
			if(isset($this->RegularTimes)){ $Form .= "<input type='hidden' name='p3' value='".$this->RegularTimes."'/>"; }
		}
		
		if($this->Cmd == self::BuyGiftCertificate){
			$Form .= "<input type='hidden' name='min_denom' value='".$this->MinGiftPrice."'/>"; 
			$Form .= "<input type='hidden' name='max_denom' value='".$this->MaxGiftPrice."'/>";
		}
		
		else if(isset($this->DisplayCart)){ $Form .= "<input type='hidden' name='display' value='".($this->DisplayCart ? "1" : "0")."'/>"; }
		
		if(count($this->Items)>1){
			for($i = 0; $i < count($this->Items); $i++){
				$Form .= "<input type='hidden' name='item_name_".($i + 1)."' value='".$this->Items[$i]->ItemName."'/>";
				$Form .= "<input type='hidden' name='item_number_".($i + 1)."' value='".$this->Items[$i]->ItemNumber."'/>";
				$Form .= "<input type='hidden' name='quantity_".($i + 1)."' value='".$this->Items[$i]->ItemQuantity."'/>";
				$Form .= "<input type='hidden' name='amount_".($i + 1)."' value='".$this->Items[$i]->ItemAmount."'/>";
			}
		}else{
			$Form .= "<input type='hidden' name='item_name' value='".$this->Items[0]->ItemName."'/>";
			$Form .= "<input type='hidden' name='item_number' value='".$this->Items[0]->ItemNumber."'/>";
			$Form .= "<input type='hidden' name='quantity' value='".$this->Items[0]->ItemQuantity."'/>";
			$Form .= "<input type='hidden' name='amount' value='".$this->Items[0]->ItemAmount."'/>";
		}
		
		$Form .= "<input type='hidden' name='rm' value='".$this->ReturnMethod."'/>";
		$Form .= "<input type='hidden' name='notify_url' value='".$this->NotifyPage."'/>";
		if(isset($this->ShoppingPage)){ $Form .= "<input type='hidden' name='shopping_url' value='".$this->ShoppingPage."'/>"; }
		if(isset($this->ThanksPage)){ $Form .= "<input type='hidden' name='return' value='".$this->ThanksPage."'/>"; }
		if(isset($this->CancelPage)){ $Form .= "<input type='hidden' name='cancel_return' value='".$this->CancelPage."'/>"; }
				
		$Form .= $this->GetButton();    
		$Form .= "</div>";
		$Form .= '</form>';
		echo $Form;
	}
	
	function GetButton(){
		$button = "";
		
		switch($this->Cmd){
			case self::BuyNow:
				$button = '<input type="image" style="width:86;border:0px;"';
				$button .= 'src="https://www.paypal.com/en_US/i/btn/btn_paynow_SM.gif" name="submit" ';
				$button .= 'alt="PayPal - The safer, easier way to pay online!"/>';
				break;
			case self::Cart:
				$button = '<input type="image" style="width:120;border:0px;"';
				$button .= 'src="https://www.paypal.com/en_US/i/btn/btn_cart_LG.gif" name="submit"';
				$button .= 'alt="PayPal - The safer, easier way to pay online!"/>';
				break;
			case self::Donate:
				$button = '<input type="image" style="width:122;border:0px;"';
				$button .= 'src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" name="submit"';
				$button .= 'alt="PayPal - The safer, easier way to pay online!"/>';
				break;
			case self::BuyGiftCertificate:	
				$button = '<input type="image" style="width:179;border:0px;"';
				$button .= 'src="https://www.paypal.com/en_US/i/btn/btn_giftCC_LG.gif" name="submit"';
				$button .= 'alt="PayPal - The safer, easier way to pay online!"/>';
				break;
			case self::Subscribe:	
				$button = '<input type="image" style="width:122;border:0px;"';
				$button .= 'src="https://www.paypal.com/en_US/i/btn/btn_subscribeCC_LG.gif" name="submit"';
				$button .= 'alt="PayPal - The safer, easier way to pay online!"/>';
				break;
		}
		
		return $button;
	}
	
	function checkPayment(){
		global $DB;
		if($_POST['receiver_email'] != $this->Receiver OR $_POST['mc_currency'] != $this->Currency OR $_POST['payment_status'] != 'Completed'){
			return false;
		}

		
		$req = 'cmd=_notify-validate';
		
		foreach ($_POST as $key => $value) {
			$value = urlencode(stripslashes($value));
			$req .= "&".$key."=".$value;
		}
		
		$url = $this->getPaypal();
		
		/* post back to PayPal system to validate */
		$header  = "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Host: ".$url."\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		$fp = fsockopen ('ssl://'.$url, 443, $errno, $errstr, 30);
		
		if (!$fp){
			/* HTTP ERROR */
			return false;
		}else{
			fputs ($fp, $header . $req);
			while (!feof($fp)){
				$res = fgets ($fp, 1024);
				if (strcmp ($res, "VERIFIED") == 0){
					/*
					check the payment_status is Completed
					check that txn_id has not been previously processed
					check that receiver_email is your Primary PayPal email
					check that payment_amount/payment_currency are correct
					process payment
					*/					
					return true;
				}else if(strcmp ($res, "INVALID") == 0){
					return false;
				}
			}
			fclose ($fp);
		}
		
		return false;
	}
	
	private function getPaypal(){
		if($this->Sandbox == true){
			return 'www.sandbox.paypal.com';
		} else {
			return 'www.paypal.com';
		}
	}

}

class PaypalItem{
	private $ItemName;
	private $ItemNumber;
	private $ItemQuantity;
	private $ItemAmount;
	
	function __get($prop_name){
		if (isset($this->$prop_name)) {
			return $this->$prop_name;
		}else{
			return NULL;
		}
	}

	function __set($prop_name, $prop_value){
		$this->$prop_name = $prop_value;
	}
	
	public function __construct($ItemName, $ItemNumber, $ItemQuantity, $ItemAmount){
		$this->ItemName = $ItemName;
		$this->ItemNumber = $ItemNumber;
		$this->ItemQuantity = $ItemQuantity;
		$this->ItemAmount = $ItemAmount;
	}
	
}
?>