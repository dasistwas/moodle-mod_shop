<?php  // $Id: lib.php,v 1.12 2009/05/28 21:30:29 dasistwas Exp $

/**
 * Library of functions and constants for module shop
 * This file should have two well differenced parts:
 *   - All the core Moodle functions, neeeded to allow
 *     the module to work integrated in Moodle.
 *   - All the shop specific functions, needed
 *     to implement all the module logic. Please, note
 *     that, if the module become complex and this lib
 *     grows a lot, it's HIGHLY recommended to move all
 *     these module specific functions to a new php file,
 *     called "locallib.php" (see forum, quiz...). This will
 *     help to save some memory when Moodle is performing
 *     actions across all modules.
 */

//////////////////////////////////
/// CONFIGURATION settings


//////////////////////////////////
/// CONSTANTS and GLOBAL VARIABLES
$CFG->shoproot = "$CFG->dirroot/mod/shop";

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $shop An object from the form in mod_form.php
 * @return int The id of the newly inserted shop record
 */
function shop_add_instance($shop) {
	global $DB;
	$shop->timecreated = time();

	# You may have to add extra stuff in here #

	return $DB->insert_record('shop', $shop);
}


/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $shop An object from the form in mod_form.php
 * @return boolean Success/Fail
 */
function shop_update_instance($shop) {
	global $DB;
	$shop->timemodified = time();
	$shop->id = $shop->instance;

	# You may have to add extra stuff in here #

	return $DB->update_record('shop', $shop);
}


/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function shop_delete_instance($id) {
	global $DB;
	if (! $shop = $DB->get_record('shop', array('id' => $id))) {
		return false;
	}

	$result = true;

	# Delete any dependent records here #

	if (! $DB->delete_records('shop', array('id' => $shop->id))) {
		$result = false;
	}
	return $result;
}


/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 */
function shop_user_outline($course, $user, $mod, $shop) {
	return $return;
}


/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function shop_user_complete($course, $user, $mod, $shop) {
	return true;
}


/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in shop activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function shop_print_recent_activity($course, $isteacher, $timestart) {
	return false;  //  True if anything was printed, otherwise false
}


/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function shop_cron () {
	return true;
}


/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of shop. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $shopid ID of an instance of this module
 * @return mixed boolean/array of students
 */
function shop_get_participants($shopid) {
	return false;
}

function shop_supports($feature) {
	switch($feature) {
		case FEATURE_GROUPS:                  return false;
		case FEATURE_GROUPINGS:               return false;
		case FEATURE_GROUPMEMBERSONLY:        return false;
		case FEATURE_MOD_INTRO:               return true;
		case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
		case FEATURE_COMPLETION_HAS_RULES:    return false;
		case FEATURE_GRADE_HAS_GRADE:         return false;
		case FEATURE_GRADE_OUTCOMES:          return false;
		case FEATURE_BACKUP_MOODLE2:          return true;

		default: return null;
	}
}


/**
 * This function returns if a scale is being used by one shop
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $shopid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 */
function shop_scale_used($shopid, $scaleid) {
	$return = false;

	//$rec = get_record("shop","id","$shopid","scale","-$scaleid");
	//
	//if (!empty($rec) && !empty($scaleid)) {
	//    $return = true;
	//}

	return $return;
}


/**
 * Checks if scale is being used by any instance of shop.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any shop
 */
function shop_scale_used_anywhere($scaleid) {
	/**
	global $DB;
	if ($scaleid and $DB->record_exists('shop', array('grade' => -$scaleid))) {
		return true;
	} else {
		return false;
	}
	**/
	return false;
}


/**
 * Execute post-install custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function shop_install() {
	return true;
}


/**
 * Execute post-uninstall custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function shop_uninstall() {
	return true;
}


//////////////////////////////////////////////////////////////////////////////////////
/// Any other shop functions go here.  Each of them must have a name that
/// starts with shop_
/// Remember (see note in first lines) that, if this section grows, it's HIGHLY
/// recommended to move all funcions below to a new "localib.php" file.

function shop_profile_display_fields($userid) {
	global $CFG, $USER, $DB;
	 
	if ($categories = $DB->get_records('user_info_category', '', 'sortorder ASC')) {
		foreach ($categories as $category) {
			if ($fields = $DB->get_records_select('user_info_field', "categoryid=$category->id",'', 'sortorder ASC')) {
				foreach ($fields as $field) {
					require_once($CFG->dirroot.'/user/profile/field/'.$field->datatype.'/field.class.php');
					$newfield = 'profile_field_'.$field->datatype;
					$formfield = new $newfield($field->id, $userid);
					if ($formfield->field->name == "Lernsterne" and !$formfield->is_empty()) {
						return $formfield->display_data();
					}
				}
			}
		}
	}
}
function shop_getlastdayofmonth($month, $year)
{
	return idate('d', mktime(0, 0, 0, ($month + 1), 0, $year));
}
function shop_logpayment($shopid, $units, $buyerid, $amount) {	
	global $DB;
	$transferdata = new object;
	//$transferdata->id ="";
	$transferdata->shop = $shopid;
	$transferdata->buyerid = $buyerid;
	$transferdata->moneypaid = $amount;
	$transferdata->transactiontime = time();
	$transferdata->ispaid = 0;
	$transferdata->units = $units;			
	$recordid = $DB->insert_record('shop_transactions', $transferdata, true);
	return $recordid;	
	// einfach nur eine neue zeile in die transaction tabelle schreiben
	// hierbei wird das paid feld automatisch auf false gesetzt
}

function shop_logpaymentpaid($recordid) {
	global $DB;
	$data = $DB->get_record('shop_transactions', array('id' => $recordid));
	$data->ispaid = 1;
	$DB->update_record('shop_transactions',$data);
}

function shop_earned($sql,$shop) {
	global $DB;
	$unitsearned = null;
	if ($earned = $DB->get_records_select('shop_transactions', $sql)) {
		$output = '<table cellpadding="6" style="background-color:#ffeeff;"><tr><th>'.get_string('amount','shop').'</th><th>'.get_string('service','shop').'</th><th>'.get_string('user').'</th><th>'.get_string('date').'</th></tr>';
		
		foreach($earned as $userrecord) {
			$output .= '<tr>';
			$username = $DB->get_record('user', array('id' => $userrecord->buyerid));
			if($userrecord->units != 1) {
				$unitsadapted = $DB->get_field('shop','unitplural', array('id' => $shop->id));
			}
			else {
				$unitsadapted = $DB->get_field('shop','unit', array('id' =>$shop->id));
			}
			$output .= '<td style="background-color:#eeeeee;">'.$userrecord->moneypaid.'€</td>';
			$output .= '<td>'.$userrecord->units.' '.$unitsadapted.' '.$DB->get_field('shop','service', array('id' => $shop->id)).'</td>';
			$output .= '<td>'.$username->firstname.' '.$username->lastname.'</td>';
			$output .= '<td>'.userdate($userrecord->transactiontime, get_string('strftimerecent')).'</td>';
			
			//echo '<div style="float:left;">'.$userrecord->moneypaid.'€ '.get_string('for','shop').' '.$userrecord->units.' '.$unitsadapted.' '.get_field('shop','service','id',$shop->id).' '.get_string('paidfrom', 'shop').' '.$username->firstname.' '.$username->lastname.'; '.get_string('date').': '.userdate($userrecord->transactiontime, get_string('strftimerecent')).'</div>';
			//echo '<div style="clear:felft;">&nbsp;</div>';
			$output .= "</tr>";
			$unitsearned += $userrecord->moneypaid;
		}
		$output .= '<tr><td style="font-weight:bold;">'.$unitsearned.'€</td><td colspan="3" style="text-align: left;">'.get_string('total', 'shop').'</td></tr>';
		$output .= "</table>";
	}
	else {
		$output = "";
	}
	return $output;
}

function shop_adminview($sql,$cmid,$month){
	global $CFG, $DB;
	$output = '';
	if ($thismonthtransactions = $DB->get_records_select('shop_transactions', $sql)) {
		$output = '<div style="padding: 5px;"></div>';
		$output .= "<h1>Admin overview $month</h1>";
		// get all shop ids for transactions of this month
		$shopids = array();
		$count = 0;
		foreach($thismonthtransactions as $transaction) {
			$shopids[$count] = $transaction->shop;
			$count++;
		}
		$shopidsunique = array_unique($shopids);
		$output .= '<table cellpadding="6"><tr><th>'.get_string('amount','shop').'</th><th>'.get_string('moduleid','shop').'</th><th>'.get_string('user').'</th><th>'.get_string('course').'</th></tr>';
		foreach ($shopidsunique as $uniqueid){
			if ($thismonthbymodule = $DB->get_records_select('shop_transactions', $sql)) {
				$earned = 0;
				foreach ($thismonthbymodule as $singlerecord){
					$earned += $singlerecord->moneypaid;
				}
				$courseid = $DB->get_field('shop', 'course', array('id' => $uniqueid));
				$context = get_context_instance(CONTEXT_MODULE, $cmid);
				$teacher ="";
				if ($courseteachers = get_users_by_capability($context, 'mod/shop:getpayment', 'u.id, u.firstname, u.lastname')) {
					foreach($courseteachers as $courseteacher){
						$teacher .= '<a href="http://testing.edulabs.org/user/view.php?id='.$courseteacher->id.'">'.$courseteacher->firstname.' '.$courseteacher->lastname.'</a> <br /> ';
					}
				}
				$output .= '<tr><td>'.$earned.' </td><td> '.$uniqueid.' </td> <td>'.$teacher.'</td><td><a href="'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'">'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'</a></td>';
			}
				
		}
		$output .= '</table>';
	}
	return $output;
}
