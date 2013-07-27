<?php
/**
 * Description of shop restore task
 *
 * @package    mod_shop
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/shop/backup/moodle2/restore_shop_stepslib.php'); // Because it exists (must)

class restore_shop_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     *
     * @return void
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     *
     * @return void
     */
    protected function define_my_steps() {
        // Choice only has one structure step
        $this->add_step(new restore_shop_activity_structure_step('shop_structure', 'shop.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     *
     * @return array
     */
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('shop', array('intro'), 'shop');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     *
     * @return array
     */
    static public function define_decode_rules() {
        $rules = array();

        // List of shops in course
        $rules[] = new restore_decode_rule('SHOPINDEX', '/mod/shop/index.php?id=$1', 'course');

        // shop by cm->id
        $rules[] = new restore_decode_rule('SHOPVIEWBYID', '/mod/shop/view.php?id=$1', 'course_module');

        // shop by shop->id
        $rules[] = new restore_decode_rule('SHOPVIEWBYB', '/mod/shop/view.php?b=$1', 'shop');

        // Convert old shop links MDL-33362 & MDL-35007
        $rules[] = new restore_decode_rule('SHOPSTART', '/mod/shop/view.php?id=$1', 'course_module');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * shop logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * @return array
     */
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('shop', 'add', 'view.php?id={course_module}', '{shop}');
        $rules[] = new restore_log_rule('shop', 'update', 'view.php?id={course_module}', '{shop}');
        $rules[] = new restore_log_rule('shop', 'view', 'view.php?id={course_module}', '{shop}');
       // To convert old 'generateimscp' log entries
        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     *
     * @return array
     */
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('shop', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
