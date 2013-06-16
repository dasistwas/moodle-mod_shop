<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Prints a particular instance of shop
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package   mod-shop
 * @copyright 2009 David Bogner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/// (Replace shop with the name of your module and remove this line)
require_once (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once (dirname(__FILE__) . '/lib.php');
require_once ($CFG->dirroot . '/user/profile/lib.php');
require_once (dirname(__FILE__) . '/shopforms.php');
require_once (dirname(__FILE__) . '/paypal.php');
$id = required_param('id', PARAM_INT); // course_module ID, or
$units = optional_param('units', 0, PARAM_INT); //number of units of one service bought
$task = optional_param('task', '', PARAM_ALPHA); //what to do
$buyerid = optional_param('buyerid', 0, PARAM_INT); // ID of the buyer
$comment = optional_param('comment', '', PARAM_TEXT); //what to do
$pathtopaypalscript = $CFG->wwwroot. '/mod/shop/checkpayment.php';


if ($id) {
	if (!$cm = get_coursemodule_from_id('shop', $id)) {
		error('Course Module ID was incorrect');
	}
	if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
		error('Course is misconfigured');
	}
	if (!$shop = $DB->get_record('shop', array('id' => $cm->instance))) {
		error('Course module is incorrect');
	}
} else {
	error('You must specify a course_module ID');
}
require_login($course, true, $cm);
add_to_log($course->id, "shop", "view", "view.php?id=$cm->id", "$shop->id");
/// Print the page header
$url = new moodle_url('/mod/shop/view.php', array('id'=>$cm->id));
$PAGE->set_url($url);

$PAGE->set_url($url);
$PAGE->set_title($shop->name);
$PAGE->set_heading($course->shortname);
$PAGE->set_button(update_module_button($cm->id, $course->id, get_string('modulename', 'shop')));

// TODO navigation will be changed yet for Moodle 2.0
$context = context_module::instance($cm->id);
$coursecontext = context_course::instance($course->id);
$strshops = get_string('modulenameplural', 'shop');
$strshop = get_string('modulename', 'shop');

/// echo $OUTPUT->header($navigation, $menu);
echo $OUTPUT->header();
/// Print the main part of the page

if (!has_capability('mod/shop:view', $context)) {
	notice(get_string('nopermission', 'shop'));
}

$profilefield = $CFG->shop_userprofilefield;
$endofmonth = shop_getlastdayofmonth(date("n", time()),date("y", time()));
$endofmonthunix = mktime('23','59','59', date("n"),$endofmonth,date("y"));
$startofmonthunix = mktime('0','0','0', date("n"),1,date("y"));
$thisyearstart = mktime('0','0','0','1','1',date("y", time()));
$thisyearend = mktime('23','59','59','12','31',date("y", time()));
//cost per unit is saved in module settings of the course
$price = $DB->get_field('shop','price', array('id' => $shop->id));

if ($task == "pay") {
	if (has_capability('mod/shop:view', $context) && confirm_sesskey()) {
		if($units!=0) {
			//subtract credits from the user
			//$userprofile = profile_user_record($USER->id);
			$paydata = $DB->get_record('shop', array('id' => $shop->id));
			$finalprice = $units * $paydata->price;
			if($units == 1){
				$quantityname = $paydata->unit;
			}
			else{
				$quantityname = $paydata->unitplural;
			}
			$Buynow = new Paypal($paydata->paypalmail,Paypal::BuyNow,'EUR',serialize(array($paydata->service,$paydata->price,$units,$USER->id,$shop->id)));
			$Buynow->AddItem(new PaypalItem($paydata->service,$shop->id,$units,$paydata->price));
			$Buynow->NotifyPage = $pathtopaypalscript;
			echo $OUTPUT->box_start();
			echo '<div style="text-align: center;">';
			echo $units.' '.$quantityname.' '.$paydata->service.', ';
			echo get_string('price', 'shop').': '.$finalprice."€<br/>";
			$Buynow->showForm();
			// for testing only
			/*
			$transferdata = new object;
			$transferdata->id ="";
			$transferdata->shop = $shop->id;
			$transferdata->buyerid = $USER->id;
			$transferdata->moneypaid = $price * $paydata->price;
			$transferdata->transactiontime = time();
			$transferdata->ispaid = 1;
			$transferdata->units = $units;
			$recordid = $DB->insert_record('shop_transactions', $transferdata);
			*/
			// testing end
			echo '</div>';
			echo $OUTPUT->box_end();
		}
		//redirect($CFG->wwwroot.'/mod/shop/view.php?id='.$cm->id.'task=thank');
	}
}
else if ($task == "consumed" && confirm_sesskey()) {
	if (has_capability('mod/shop:getpayment', $context) && confirm_sesskey()) {
		if ($units != 0) {
			//add consumed units to database records
			$participantprofile = profile_user_record($buyerid);
			$data->shop = $shop->id;
			$data->buyerid = $buyerid;
			$data->transactiontime = time();
			$data->comment = $comment;
			$data->units = $units;
			if (!$DB->insert_record('shop_usedunits', $data)) {
				error('Error updating status!');
			}
		}
		redirect($CFG->wwwroot.'/mod/shop/view.php?id='.$cm->id.'task=thank');
	}
}

else {
	if (is_siteadmin($USER->id)) {
		echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
		echo shop_adminview("transactiontime >=$startofmonthunix AND transactiontime<=$endofmonthunix AND ispaid=1", $cm->id, 'this month');
		echo $OUTPUT->box_end();
		echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
		echo shop_adminview("transactiontime >=".strtotime('-1 month',$startofmonthunix)." AND transactiontime<=".strtotime('-1 month',$endofmonthunix)." AND ispaid=1", $cm->id, 'last month');
		echo $OUTPUT->box_end();
	}
	if (has_capability('mod/shop:getpayment', $context)) {
		//title in teacherview
		echo '<div style="padding: 5px;"></div>';
		echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
		echo "<h1>".get_string('overview', 'shop')."</h1>";
		//display participants and their amount of credits
		$participants = get_enrolled_users($coursecontext,'','0','u.id,username,firstname,lastname');
		echo '<h2 style="font-weight: bold;">'.get_string('creditsofparticipants', 'shop').'</h2>';
		echo '<ul style="list-style-type:none; padding:0; margin:0;"><li style="padding-bottom:5px;"><div style="width:=100%; font-weight: bold;"><div style="width:20%; display:inline-block;">'.get_string('user').'</div><div style="width:20%; display:inline-block;">'.get_string('credits', 'shop').'</div><div style="width:20%; display:inline-block;">'.get_string('consumed','shop').'</div><div style="width:20%; display:inline-block;">'.get_string('comment','shop').'</div><div style="width:20%; display:inline-block;">'.get_string('savechanges').'</div></div></li>';
		$i = 0;
		foreach ($participants as $participant) {
			$i++;
			$userprofile = profile_user_record($participant->id);
			if ($USER->username != $participant->username) { //prevent teachers from viewing own credits
				if ($unitsbought = $DB->get_records('shop_transactions', array("shop" => $shop->id, 'ispaid' => 1, 'buyerid' => $participant->id))) {
					$units = 0;
					foreach($unitsbought as $unitbought) {
						$units += $unitbought->units;
					}
					if ($unitsconsumed = $DB->get_record('shop_usedunits', array('shop' => $shop->id, 'buyerid' => $participant->id))) {
						foreach($unitsconsumed as $unitconsumed) {
							$units -= $unitconsumed->units;
						}
					}
					echo '<li>';
					echo '<form action="" method="post">';
					if ( $i%2 ) {
						echo '<div style="width:=100%; padding-top: 2px; padding-bottom: 2px; background-color:#eeeeee">';
					} else {
						echo '<div style="width:=100%; padding-top: 2px; padding-bottom: 2px; background-color:#ffeeff"">';
					}
					echo '<div style="width:20%; display:inline-block;">' . $participant->firstname . ' ' . $participant->lastname . '</div>';
					echo '<div style="width:20%; display:inline-block;">'. $units .' '.$DB->get_field('shop','unitplural', array('id' => $shop->id)).'</div>';
					echo '<div style="width:20%; display:inline-block;">';
					echo '<select name="units">';
					echo '<option value="0">0</option>';
					for ($counter = 1; $counter <= $units; $counter += 1) {
						echo '<option value="' . $counter . '">' . $counter . '</option>';
					}
					echo '</select></div>';
					echo '<div style="width:20%; display:inline-block;">';
					echo '<input type="text" name="comment" value=" " size="18" maxlength="100"/>';
					echo '</div>';
					echo '<div style="width:20%; display:inline-block;"><input type="hidden" value="consumed" name="task"/>';
					echo '<input type="hidden" value="'.sesskey().'" name="sesskey"/>';
					echo '<input type="hidden" value="'.$participant->id.'" name="buyerid"/>';
					echo '<input type="submit" value="' .  get_string('savechanges') .'" />';
					echo '</div>';
					echo '</div>';
					echo '</form>';
					echo '</li>';
				}
			}
		}
		
		echo '</ul>';
		// display earned credits of current month;
		echo '<h2 style="font-weight: bold;">'.get_string('creditsearned', 'shop').'</h2>';
		$unitsearned = new object;
		$unitsearned->thismonth = 0;
		$unitsearned->lastmonth = 0;
		$unitsearned->thisyear = 0;
		$unitsearned->alltime = 0;
		echo '<p style="font-weight:bold">'.get_string('thismonth', 'shop').'</p>';
		echo shop_earned("transactiontime >=$startofmonthunix AND transactiontime<=$endofmonthunix AND ispaid = 1 AND shop=$shop->id", $shop);
		echo '<p style="font-weight:bold">'.get_string('lastmonth', 'shop').'</p>';
		echo shop_earned("transactiontime >=".strtotime('-1 month',$startofmonthunix)." AND ispaid = 1 AND transactiontime<=".strtotime('-1 month',$endofmonthunix)." AND shop=$shop->id",$shop);
		echo '<p style="font-weight:bold">'.get_string('thisyear', 'shop');
		if ($thisyearearned = $DB->get_records_select('shop_transactions', "transactiontime >=$thisyearstart AND ispaid = 1 AND transactiontime<=$thisyearend AND shop=$shop->id")) {
			foreach($thisyearearned as $userrecord) {
				$unitsearned->thisyear += $userrecord->moneypaid;
			};
			echo ": ".$unitsearned->thisyear.'€';
			echo '</p>';
		}
		if ($totalearned = $DB->get_records('shop_transactions', array('ispaid' => 1, 'shop' => $shop->id))) {
			foreach($totalearned as $userrecord) {
				$unitsearned->alltime += $userrecord->moneypaid;
			}
		}
		echo '<p style="font-weight:bold">'.get_string('alltime', 'shop').": ".$unitsearned->alltime."€</p>";
	}
	else if( has_capability('mod/shop:view', $context)) {
		echo '<div style="padding: 5px;"></div>';
		echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
		$unitspaid = new object;
		$unitspaid->thismonth = 0;
		$unitspaid->lastmonth = 0;
		$unitspaid->thisyear = 0;
		$unitspaid->alltime = 0;
		$maxamount = 100;
		echo '<form action="" method="post">';
		echo '<h1>'.get_string('pay', 'shop').'</h1>';
		echo "<table cellpadding='10' style='text-align: center; border: 1px dashed #888888; background-color:#ffeeff;'>";
		echo '<tr>    <th>'.get_string('serviceoffered', 'shop').'</th><th>'.get_string('priceper', 'shop').' '.$DB->get_field('shop','unit', array('id' => $shop->id)).'</th><th>'.get_string('quantity', 'shop').'</th><th>'.get_string('totalamount','shop').'</th><th>'.get_string('payment', 'shop').'</th></tr>';
		echo "<tr>";
		echo "<td>" . $DB->get_field('shop','service', array('id' => $shop->id)).'</td>';
		echo "<td>" . $DB->get_field('shop','price', array('id' => $shop->id)) . '€<input type="hidden" name="price" id="pricebox" value="' . $DB->get_field('shop','price', array('id' => $shop->id)) . '"/></td>';
		echo '<td>';
		echo '<select name="units" id="unitsid" onchange="updatePrice()">';
		echo '<option value="0">0</option>';
		for ($counter = 1; $counter <= $maxamount; $counter += 1) {
			echo '<option value="' . $counter . '">' . $counter . '</option>';
		}
		echo '</select></td>';
		echo '<td><span id="finalprice"></span><span>€</span>';
		echo '</td>';
		echo '<td><input type="hidden" value="pay" name="task"/>';
		echo '<input type="hidden" value="'.sesskey().'" name="sesskey"/>';
		echo '<input type="submit" value="' . get_string('paynow', 'shop') .'" />';
		echo '<script type="text/javascript">
				function updatePrice() {
				  var selbox = document.getElementById("unitsid");
				  var priceperhour = parseInt(document.getElementById("pricebox").value);
				  
				  var newprice = parseInt(selbox.options[selbox.selectedIndex].value) * priceperhour;
				  var finalprice = document.getElementById("finalprice");
				  finalprice.innerHTML = newprice;
				 }
				 updatePrice();
				</script>';
		echo '</td>';
		echo "</tr>";
		echo '<tr><td colspan="5">';
		echo 'Bevor du deine Nachhilfe bezahlst, <a href="#" onclick="javascript:window.open(\'http://www.lernstar.com/mod/resource/view.php?id=368 \',\'istpcgeeignet\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=500, height=550\');">teste ob dein Computer für Online-Nachhilfe geeignet ist.</a></td></tr>';
		echo "</table>";
		echo '</form>';
		echo '<!-- PayPal Logo --><div style="text-align: left;"><a href="#" onclick="javascript:window.open(\'https://www.paypal.com/at/cgi-bin/webscr?cmd=xpt/Marketing/popup/OLCWhatIsPayPal-outside\',\'olcwhatispaypal\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=400, height=350\');"><img border="0" alt="Solution Graphics" src="pix/paypal.gif" /></a><br /></div>';
		echo '<h2>'.get_string('youhave', 'shop').'</h2>';
		if ($unitsbought = $DB->get_records('shop_transactions', array('shop' => $shop->id, 'buyerid' => $USER->id))){
			$units =0;
			foreach($unitsbought as $unitbought){
				if($unitbought->ispaid == 1){
					$units += $unitbought->units;
				}
			}
			if($usedunits = $DB->get_records('shop_usedunits', array('shop' => $shop->id, 'buyerid' => $USER->id))){
				foreach($usedunits as $usedunit){
					$units -= $usedunit->units;
				}
			}
		}
		if ($units !=1){
			$unitname = $DB->get_field('shop','unitplural', array('id' => $shop->id));
		}
		else {
			$unitname = $DB->get_field('shop','unit', array('id' => $shop->id));
		}
		echo $units.' '.$unitname.' '.$DB->get_field('shop','service', array('id' => $shop->id));
		echo '<h2>'.get_string('youalreadyhad', 'shop').'</h2>';
		echo '<p style="font-weight:bold">'.get_string('thismonth', 'shop').'</p>';
		if ( $thismonthpaid = $DB->get_records_select('shop_usedunits', "transactiontime >=$startofmonthunix AND transactiontime<=$endofmonthunix AND shop=$shop->id AND buyerid=$USER->id")) {
			foreach($thismonthpaid as $userrecord) {
				if ($userrecord->units !=1){
					$unitname = $DB->get_field('shop','unitplural', array('id' => $shop->id));
				}
				else {
					$unitname = $DB->get_field('shop','unit', array('id' => $shop->id));
				}
				$username = $DB->get_record('user', array('id' => $userrecord->buyerid));
				echo $userrecord->units.' '.$unitname.' '.$DB->get_field('shop','service', array('id' => $shop->id)).' '.get_string('date').': '.userdate($userrecord->transactiontime, get_string('strftimerecent')).' '.get_string('comment','shop').': '.$userrecord->comment.'<br />';
				$unitsearned->thismonth += $userrecord->units;
			}
		}
		echo '<p style="font-weight:bold">'.get_string('lastmonth', 'shop').'</p>';
		if ( $lastmonthpaid = $DB->get_records_select('shop_usedunits', "transactiontime >=".strtotime('-1 month',$startofmonthunix)." AND transactiontime<=".strtotime('-1 month',$endofmonthunix)." AND shop=$shop->id AND buyerid=$USER->id")) {
			foreach($lastmonthpaid as $userrecord) {
				if ($userrecord->units !=1){
					$unitname = $DB->get_field('shop','unitplural', array('id' => $shop->id));
				}
				else {
					$unitname = $DB->get_field('shop','unit', array('id' => $shop->id));
				}
				$username = $DB->get_record_select('user', array('id' => $USER->id));
				echo $userrecord->units.' '.$unitname.' '.$DB->get_field('shop','service', array('id' => $shop->id)).' '.get_string('date').': '.userdate($userrecord->transactiontime, get_string('strftimerecent')).' '.get_string('comment','shop').': '.$userrecord->comment.'<br />';
				$unitsearned->lastmonth += $userrecord->units;
			}
		}
		if ( $thisyearpaid = $DB->get_records_select('shop_usedunits', "transactiontime >=$thisyearstart AND transactiontime<=$thisyearend AND shop=$shop->id AND buyerid=$USER->id")) {
			foreach($thisyearpaid as $userrecord) {
				$unitspaid->thisyear += $userrecord->units;
			}
		}
		if ( $totalpaid = $DB->get_records('shop_usedunits', array('buyerid' => $USER->id))) {
			foreach($totalpaid as $userrecord) {
				$unitspaid->alltime += $userrecord->units;
			}
		}
		if ($unitspaid->thisyear !=1){
			$unitname = $DB->get_field('shop','unitplural', array('id' => $shop->id));
		}
		else {
			$unitname = $DB->get_field('shop','unit',array('id' => $shop->id));
		}
			if ($unitspaid->alltime !=1){
			$unitname1 = $DB->get_field('shop','unitplural',array('id' => $shop->id));
		}
		else {
			$unitname1 = $DB->get_field('shop','unit',array('id' => $shop->id));
		}
		echo '<p style="font-weight:bold">'.get_string('thisyear', 'shop').": ".$unitspaid->thisyear.' '.$unitname.'<br />'.get_string('alltime', 'shop').": $unitspaid->alltime $unitname1</p>";
	}
	 echo $OUTPUT->box_end();
}
// display users with form to subtract
//
/// Finish the page
echo $OUTPUT->footer();
?>
