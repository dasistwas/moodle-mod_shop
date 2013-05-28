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
 * The main shop configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package   mod-shop
 * @copyright 2009 David Bogner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_shop_mod_form extends moodleform_mod {

    function definition() {

        global $COURSE;
        $mform =& $this->_form;

//-------------------------------------------------------------------------------
    /// Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

    /// Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('shopname', 'shop'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

    /// Adding the required "intro" field to hold the description of the instance
        $this->add_intro_editor(true, get_string('opendesktopintro', 'opendesktop'));
                //$mform->setHelpButton('intro', array('writing', 'richtext'), false, 'editorhelpbutton');

//-------------------------------------------------------------------------------
    /// Adding the rest of shop settings, spreeading all them into this fieldset
    /// or adding more fieldsets ('header' elements) if needed for better logic


        $mform->addElement('header', 'shopfieldset', get_string('shopfieldset', 'shop'));
        $mform->addElement('text', 'price', get_string('price', 'shop'),'maxlength="4" size="4"');
        $mform->setType('price', PARAM_INT);
        $mform->addRule('price', null, 'numeric', null, 'client');
        $mform->addRule('price', get_string('required'), 'required', null, 'client');
                // Here is how you can add help (?) icons to your field labels
        //$mform->setHelpButton('price', array('price', 'priceinfo', 'shop'));
        $mform->addElement('text', 'service', get_string('service', 'shop'),'maxlength="100" size="30"');
        $mform->setType('service', PARAM_TEXT);
        $mform->addRule('service', get_string('required'), 'required', null, 'client');     
        $mform->addElement('text', 'unit', get_string('unit', 'shop'),'maxlength="100" size="30"');
        $mform->setType('unit', PARAM_TEXT);
        $mform->addRule('unit', get_string('required'), 'required', null, 'client');     
        $mform->addElement('text', 'unitplural', get_string('unitplural', 'shop'),'maxlength="100" size="40"');
        $mform->setType('unitplural', PARAM_TEXT);
        $mform->addRule('unitplural', get_string('required'), 'required', null, 'client');    
        $mform->addElement('text', 'paypalmail', get_string('paypalmail', 'shop'),'maxlength="100" size="40"');
        $mform->setType('paypalmail', PARAM_TEXT);
        $mform->addRule('paypalmail', get_string('required'), 'required', null, 'client');          
//-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
//-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();

    }
}
