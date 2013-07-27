<?php
/**
 * Define all the restore steps that will be used by the restore_shop_activity_task
 *
 * @package    mod_shop
 * @copyright  2013 David Bogner {@link http://www.edulabs.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Structure step to restore one shop activity
 */
class restore_shop_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();

        $paths[] = new restore_path_element('shop', '/activity/shop');

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process shop tag information
     * @param array $data information
     */
    protected function process_shop($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $newitemid = $DB->insert_record('shop', $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process chapter tag information
     * @param array $data information
     */
    protected function process_shop_chapter($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->shopid = $this->get_new_parentid('shop');

    }

    protected function after_execute() {
        global $DB;

        // Add shop related files
        $this->add_related_files('mod_shop', 'intro', null);
    }
}
