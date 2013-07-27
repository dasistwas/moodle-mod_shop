<?php

/**
 * Description of shop backup task
 *
 * @package    mod_shop
 * @copyright  2010-2013 David Bogner {@link http://www.edulabs.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/mod/shop/backup/moodle2/backup_shop_stepslib.php');    // Because it exists (must)
require_once($CFG->dirroot.'/mod/shop/backup/moodle2/backup_shop_settingslib.php'); // Because it exists (optional)

class backup_shop_activity_task extends backup_activity_task {

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
        // shop only has one structure step
        $this->add_step(new backup_shop_activity_structure_step('shop_structure', 'shop.xml'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     *
     * @param string $content
     * @return string encoded content
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

        // Link to the list of shops
        $search  = "/($base\/mod\/shop\/index.php\?id=)([0-9]+)/";
        $content = preg_replace($search, '$@SHOPINDEX*$2@$', $content);

        // Link to shop view by moduleid
        $search  = "/($base\/mod\/shop\/view.php\?id=)([0-9]+)(&|&amp;)chapterid=([0-9]+)/";
        $content = preg_replace($search, '$@SHOPVIEWBYIDCH*$2*$4@$', $content);

        $search  = "/($base\/mod\/shop\/view.php\?id=)([0-9]+)/";
        $content = preg_replace($search, '$@SHOPVIEWBYID*$2@$', $content);

        // Link to shop view by shopid
        $search  = "/($base\/mod\/shop\/view.php\?b=)([0-9]+)(&|&amp;)chapterid=([0-9]+)/";
        $content = preg_replace($search, '$@SHOPVIEWBYBCH*$2*$4@$', $content);

        $search  = "/($base\/mod\/shop\/view.php\?b=)([0-9]+)/";
        $content = preg_replace($search, '$@SHOPVIEWBYB*$2@$', $content);

        return $content;
    }
}
