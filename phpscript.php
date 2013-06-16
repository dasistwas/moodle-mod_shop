<?php
$subject = $DB->get_field('shop','service',array('id' => $shop->id));			//paypaladresse des lehrers
$pathtoscript = $CFG->wwwroot."/mod/shop"; //Url zum verzeichnis der datei"programming";		//fach für nachhife
$priceperhour = $DB->get_field('shop','price', array('id' => $shop->id));				//preis pro stunde von lehrer
$hours = $DB->get_field('shop','units',array('id' => $shop->id)); 	//stunden
$buyerid = $USER->id;//id des schuelers

$paypalreceiver = $DB->get_field('shop','paypalmail', array('id' => '$shop->id'));			//paypaladresse des lehrers

$finalprice = $priceperhour * $hours;

$Buynow = new Paypal($paypalreceiver,Paypal::BuyNow,'EUR',serialize(array($subject,$priceperhour,$hours,$buyerid)));
$Buynow->AddItem(new PaypalItem('Coaching for '.$hours.' hour(s) in '.$subject.' with '.$teachername,'COACHING','1',$finalprice));

$Buynow->NotifyPage = 'http://'.$pathtoscript.'/checkpayment.php';

echo 'Coaching for '.$hours.' hour(s) in '.$subject.' with '.$teachername;
echo "Finalprice: ".$finalprice."<br>";
$Buynow->showForm();
?>
