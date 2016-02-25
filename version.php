<?php
/*
 * copyright exabis
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2016022301;		// The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2015051100;		// Requires this Moodle version 2.6
$plugin->component = 'block_exacomp'; 	// Full name of the plugin (used for diagnostics)
$plugin->cron = 259200; 				//259200sec = 3 days

$plugin->maturity = MATURITY_STABLE;
$plugin->release = 'v2.6.5-r1'; 			// This is our first release for Moodle 2.7.x branch.
