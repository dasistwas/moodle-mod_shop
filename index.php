<?php 
/**
 * This page lists all the instances of shop in a particular course
 *
 * @package    mod
 * @subpackage shop
 * @author  David Bogner <davidbogner@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT);   // course

if (! $course = $DB->get_record('course', array('id' => $id))) {
    error('Course ID is incorrect');
}
$coursecontext = context_course::instance($id);
require_login($course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/mod/shop/index.php', array('id'=>$id));

add_to_log($course->id, "shop", "view all", "index.php?id=$course->id", "");

/// Get all required stringsshop

$strshops = get_string('modulenameplural', 'shop');
$strshop  = get_string('modulename', 'shop');
$PAGE->set_title($strshops);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();

/// Get all the appropriate data

if (! $shops = get_all_instances_in_course('shop', $course)) {
    notice('There are no shops', "../../course/view.php?id=$course->id");
    die;
}

/// Print the list of instances (your module will probably extend this)

$timenow  = time();
$strname  = get_string('name');
$strweek  = get_string('week');
$strtopic = get_string('topic');

if ($course->format == 'weeks') {
    $table->head  = array ($strweek, $strname);
    $table->align = array ('center', 'left');
} else if ($course->format == 'topics') {
    $table->head  = array ($strtopic, $strname);
    $table->align = array ('center', 'left', 'left', 'left');
} else {
    $table->head  = array ($strname);
    $table->align = array ('left', 'left', 'left');
}

foreach ($shops as $shop) {
    if (!$shop->visible) {
        //Show dimmed if the mod is hidden
        $link = "<a class=\"dimmed\" href=\"view.php?id=$shop->coursemodule\">$shop->name</a>";
    } else {
        //Show normal if the mod is visible
        $link = "<a href=\"view.php?id=$shop->coursemodule\">$shop->name</a>";
    }

    if ($course->format == 'weeks' or $course->format == 'topics') {
        $table->data[] = array ($shop->section, $link);
    } else {
        $table->data[] = array ($link);
    }
}

print_heading($strshops);
print_table($table);

// Finish the page.
echo $OUTPUT->footer();

?>
