<?php
/**
 * Define all the backup steps that will be used by the backup_shop_activity_task
 *
 * @package    mod_shop
 * @copyright  2010 David Bogner {@link http://www.edulabs.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Structure step to backup one shop activity
 */
class backup_shop_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // Define each element separated
        $shop = new backup_nested_element('shop', array('id'), array('name', 'intro', 'introformat','timecreated', 'timemodified', 'price', 'service', 'unit','unitplural','paypalmail'));

        // Define sources
        $shop->set_source_table('shop', array('id' => backup::VAR_ACTIVITYID));

        // Define file annotations
        $shop->annotate_files('mod_shop', 'intro', null); // This file area hasn't itemid
 
        // Return the root element (shop), wrapped into standard activity structure
        return $this->prepare_activity_structure($shop);
    }
}
