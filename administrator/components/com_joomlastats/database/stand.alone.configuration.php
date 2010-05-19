<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

 
//It is stand alone version!
defined('_JS_STAND_ALONE') or die ('JoomlaStats Stand Alone version: Direct Access to this location is not allowed.');
 



class js_JSStandAloneConfiguration
{
	/** array that contain configuration from Joomla CMS (hold only those values that are needed by JS) */
    var $JConfigArr = array(

    //Settings from Joomla CMS (copy values from joomla 'configuration.php' file)
    //  j1.5.6 name    value                comment                                 j1.5.9 name         j1.0.15 name
        'dbtype'    => 'mysql',             // Usually mysql, could be mysqli       $dbtype
        'host'      => 'localhost',         // Usually localhost                    $host               mosConfig_host
        'user'      => 'j159',              // MySQL username                       $user               mosConfig_user
        'password'  => 'password',          // MySQL password                       $password           mosConfig_password
        'db'        => 'j159_2009-03-26',   // MySQL database name                  $db                 mosConfig_db
        'database'  => 'j159_2009-03-26',   // MySQL database name                  $db                 mosConfig_db
        'dbprefix'  => 'jos_',              // prefix for Joomla CMS tables in DB   $dbprefix           mosConfig_dbprefix
        'offset'    => 0,                   // time offset (in hours)               $offset             mosConfig_offset_user (not mosConfig_offset)
        'debug'     => 1                    // set to 1 to see debug messages       $debug
    );

    //JoomlaStats settings


	function __construct() {
		$this->initializeFromJoomlaCmsConfigurationFile();
	}
	
	/**
	 * A hack to support __construct() on PHP 4
	 *
	 * Hint: descendant classes have no PHP4 class_name() constructors,
	 * so this constructor gets called first and calls the top-layer __construct()
	 * which (if present) should call parent::__construct()
	 *
	 * code from Joomla CMS 1.5.10 (thanks!)
	 *
	 * @access	public
	 * @return	Object
	 * @since	1.5
	 */
	function js_JSStandAloneConfiguration()
	{
		$args = func_get_args();
		call_user_func_array(array(&$this, '__construct'), $args);
	}



	function initializeFromJoomlaCmsConfigurationFile() {
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. '..' .DIRECTORY_SEPARATOR. '..' .DIRECTORY_SEPARATOR. '..' .DIRECTORY_SEPARATOR. '..' .DIRECTORY_SEPARATOR. 'configuration.php' );
		$vJConfig = new JConfig();

		$this->JConfigArr['dbtype']   = $vJConfig->dbtype;
		$this->JConfigArr['host']     = $vJConfig->host;
		$this->JConfigArr['user']     = $vJConfig->user;
		$this->JConfigArr['password'] = $vJConfig->password;
		$this->JConfigArr['db']       = $vJConfig->db;
		$this->JConfigArr['database'] = $vJConfig->db;
		$this->JConfigArr['dbprefix'] = $vJConfig->dbprefix;
		$this->JConfigArr['offset']   = $vJConfig->offset;
		$this->JConfigArr['debug']    = $vJConfig->debug;
	}
}


