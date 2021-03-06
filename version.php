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
 * Defines the version of shop
 *
 * This code fragment is called by moodle_needs_upgrading() and
 * /admin/index.php
 *
 * @package   mod-shop
 * @copyright 2009 David Bogner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$plugin->version  = 2013072800;  // The current module version (Date: YYYYMMDDXX)
$plugin->component  = 'mod_shop';  // Component name
$plugin->requires = 2010112400;  // Requires this Moodle 2.X version
$plugin->release = '2.4';
$plugin->maturity = MATURITY_STABLE;
$plugin->cron     = 0;           // Period for cron to check this module (secs)
