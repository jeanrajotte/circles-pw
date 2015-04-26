<?php

/**
 * ProcessWire Configuration File
 *
 * Site-specific configuration for ProcessWire
 * 
 * Please see the file /wire/config.php which contains all configuration options you may
 * specify here. Simply copy any of the configuration options from that file and paste
 * them into this file in order to modify them. 
 *
 * ProcessWire 2.x 
 * Copyright (C) 2014 by Ryan Cramer 
 * Licensed under GNU/GPL v2, see LICENSE.TXT
 * 
 * http://processwire.com
 *
 */

if(!defined("PROCESSWIRE")) die();

/*** SITE CONFIG *************************************************************************/

/**
 * Enable debug mode?
 *
 * Debug mode causes additional info to appear for use during dev and debugging.
 * This is almost always recommended for sites in development. However, you should
 * always have this disabled for live/production sites.
 *
 * @var bool
 *
 */
$config->debug = false;

/**
 * Prepend template file
 *
 * PHP file in /site/templates/ that will be loaded before each page's template file.
 * Example: _init.php
 *
 * @var string
 *
 */
$config->prependTemplateFile = '_init.php';

/**
 * Append template file
 *
 * PHP file in /site/templates/ that will be loaded after each page's template file.
 * Example: _main.php
 *
 * @var string
 *
 */
$config->appendTemplateFile = '_main.php';




/*** INSTALLER CONFIG ********************************************************************/

/**
 * Installer: Database Configuration
 * 
 */
$config->dbHost = 'localhost';
$config->dbName = 'circles-pw';
$config->dbUser = 'circles-pw';
$config->dbPass = '1entry2circles-pw';
$config->dbPort = '3306';

/**
 * Installer: User Authentication Salt 
 * 
 * Must be retained if you migrate your site from one server to another
 * 
 */
$config->userAuthSalt = '71df8122bafa3b5fdc4eb0873a12c650'; 

/**
 * Installer: File Permission Configuration
 * 
 */
$config->chmodDir = '0755'; // permission for directories created by ProcessWire
$config->chmodFile = '0644'; // permission for files created by ProcessWire 

/**
 * Installer: Time zone setting
 * 
 */
$config->timezone = 'America/New_York';


/**
 * Installer: HTTP Hosts Whitelist
 * 
 */
$config->httpHosts = array('circles-pw.dev', 'www.circles-pw.dev');

