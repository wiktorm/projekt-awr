<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

 
if( !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}




function js_UpdateJSDatabaseOnInstall( $JSDatabaseAccess, $JSUtil, $updateFromJSVersion, $JSConfDef, $installationErrorMsg ) {

	//in 2.3.0 we do not support update from version older than 2.2.3!!!     -do not remove below code, it help us to see full path of changes
	if ($JSUtil->JSVersionCompare( $updateFromJSVersion, '2.2.0', '<') == true) {
		$quer = array();
		
		//below update could be applayed earlier than in version '2.2.0' (in version '2.2.0' it was applayed for sure)
		$quer[] = "RENAME TABLE #__TFS_bots TO #__jstats_bots, #__TFS_browsers TO #__jstats_browsers, #__TFS_configuration TO #__jstats_configuration, #__TFS_ipaddresses TO #__jstats_ipaddresses, #__TFS_iptocountry TO #__jstats_iptocountry, #__TFS_keywords TO #__jstats_keywords, #__TFS_page_request TO #__jstats_page_request, #__TFS_page_request_c TO #__jstats_page_request_c, #__TFS_pages TO #__jstats_pages, #__TFS_referrer TO #__jstats_referrer, #__TFS_search_engines TO #__jstats_search_engines, #__TFS_systems TO #__jstats_systems, #__TFS_topleveldomains TO #__jstats_topleveldomains, #__TFS_visits TO #__jstats_visits";
	
		//below update could be applayed earlier than in version '2.2.0' (in version '2.2.0' it was applayed for sure)
		$quer[] = "RENAME TABLE #__tfs_bots TO #__jstats_bots, #__tfs_browsers TO #__jstats_browsers, #__tfs_configuration TO #__jstats_configuration, #__tfs_ipaddresses TO #__jstats_ipaddresses, #__tfs_iptocountry TO #__jstats_iptocountry, #__tfs_keywords TO #__jstats_keywords, #__tfs_page_request TO #__jstats_page_request, #__tfs_page_request_c TO #__jstats_page_request_c, #__tfs_pages TO #__jstats_pages, #__tfs_referrer TO #__jstats_referrer, #__tfs_search_engines TO #__jstats_search_engines, #__tfs_systems TO #__jstats_systems, #__tfs_topleveldomains TO #__jstats_topleveldomains, #__tfs_visits TO #__jstats_visits";
	
		//below update could be applayed earlier than in version '2.2.0' (in version '2.2.0' it was applayed for sure)
		$quer[] = "ALTER IGNORE TABLE #__jstats_pages ADD `page_title` VARCHAR( 255 )";
		
		// transfer what we have
		$JSDatabaseAccess->populateSQL( $quer );
	}
		

	//in 2.3.0 we do not support update from version older than 2.2.3!!!     -do not remove below code, it help us to see full path of changes
	if ($JSUtil->JSVersionCompare( $updateFromJSVersion, '2.2.0', '<') == true) {
		$quer = array();
		
		// we added the primairy key description later, because then we could keep the old configuration (in the past the config was reset on every update).
		//below update could be applayed earlier than in version '2.2.0' (in version '2.2.0' it was applayed for sure)
		$quer[] = "ALTER TABLE `#__jstats_configuration` ADD PRIMARY KEY (description)";
		
		// this index should realy speed up things...
		//below update could be applayed earlier than in version '2.2.0' (in version '2.2.0' it was applayed for sure) //index duplicated!
		$quer[] = "CREATE INDEX visits_id ON `#__jstats_page_request` (`ip_id`)";
		
		//below update could be applayed earlier than in version '2.2.0' (in version '2.2.0' it was applayed for sure) //index duplicated!
		$quer[] = "ALTER IGNORE TABLE `#__jstats_page_request` ADD INDEX `index_ip` (ip_id)";
		
		// added user awareness
		//below update could be applayed earlier than in version '2.2.0' (in version '2.2.0' it was applayed for sure)
		$quer[] = "ALTER IGNORE TABLE `#__jstats_visits` ADD userid INT NOT NULL AFTER ip_id";

		// before release 2.1.9 additional userid indexes where created unwanted, remove them.
		//below update could be applayed earlier than in version '2.2.0' (in version '2.2.0' it was applayed for sure)
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_2`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_3`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_4`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_5`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_6`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_7`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_8`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_9`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_10`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_11`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_12`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_13`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_14`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_15`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_16`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_17`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_18`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_19`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_20`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_21`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_22`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_23`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_24`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_25`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_26`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_27`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_28`";
		$quer[] = "ALTER TABLE `#__jstats_visits` DROP INDEX `userid_29`";
		$quer[] = "ALTER IGNORE TABLE `#__jstats_visits` ADD INDEX `userid` (userid)";//in database schema it is missing so we remove it later (v2.5.0.301 - details see there)
		
		// transfer what we have
		$JSDatabaseAccess->populateSQL( $quer );
	}

	$wereColumnsScreenAndWhoisCreated = true;//this is update install process optimization
	//@todo '2.3.0.130' is not checked (I can do this right now) - it should be checked in which version this was added
	if ($JSUtil->JSVersionCompare( $updateFromJSVersion, '2.3.0.130 dev', '<') == true) {
		$quer = array();
		
		// new since 2.3.0: we do not use anymore 'hourdiff' and 'language': delete them
		$quer[] = 'DELETE FROM `#__jstats_configuration` WHERE `description` = \'hourdiff\'';
		$quer[] = 'DELETE FROM `#__jstats_configuration` WHERE `description` = \'language\'';

		// new since 2.3.0: new field
		if ($wereColumnsScreenAndWhoisCreated == false) {
			//I know this code will be never performed in the future, but in the past it was - it should not be commented nor removed!
			$quer[] = "ALTER TABLE `#__jstats_ipaddresses` ADD `whois` TINYINT( 1 ) NOT NULL";
			$quer[] = 'ALTER TABLE `#__jstats_ipaddresses` ADD screen varchar(12) NOT NULL COMMENT \'screen resolution\'';
		} else {
			$wereColumnsScreenAndWhoisCreated = false;
		}

		// transfer what we have
		$JSDatabaseAccess->populateSQL( $quer );
	}
	
	if ($JSUtil->JSVersionCompare( $updateFromJSVersion, '2.3.0.167 dev', '<') == true) {
		$quer = array();
		
		// new since 2.3.0: we do not use anymore 'purgetime' nor 'last_purge' : delete them
		$quer[] = 'DELETE FROM `#__jstats_configuration` WHERE `description` = \'purgetime\'';
		$quer[] = 'DELETE FROM `#__jstats_configuration` WHERE `description` = \'last_purge\'';

		//rename show_bu to show_summarized
		$quer[] = 'UPDATE  IGNORE `#__jstats_configuration` SET `description` = \'show_summarized\' WHERE `description` = \'show_bu\'';

		//remove duplicated indexes				
		$quer[] = "ALTER TABLE `#__jstats_ipaddresses` DROP INDEX `id`";
		$quer[] = "ALTER TABLE `#__jstats_pages` DROP INDEX `page_id`";
		$quer[] = "ALTER TABLE `#__jstats_page_request` DROP INDEX `visits_id`";
		
		// transfer what we have
		$JSDatabaseAccess->populateSQL( $quer );
	}
	
	if ($JSUtil->JSVersionCompare( $updateFromJSVersion, '2.3.0.189 dev', '<') == true) {
		$quer = array();
		
		// below query is harmless //I do not know if it is necessary (unable to check this) but I want to be SURE that all data are also in column time //duplicated columns (year, month, day, hour) will be deleted in near future!
		$quer[] = "UPDATE `#__jstats_visits` SET `time` = CONCAT(`year`, '-', `month`, '-', `day`, ' ', `hour`, ':00:00') WHERE `time` = '0000-00-00 00:00:00'";
		
		// transfer what we have
		$JSDatabaseAccess->populateSQL( $quer );
	}
	
	if ($JSUtil->JSVersionCompare( $updateFromJSVersion, '2.3.0.201 dev', '<') == true) {
		$quer = array();
		
		//sys_img is not used - change it to sys_type. Add column image
		$quer[] = "ALTER TABLE `#__jstats_systems` CHANGE COLUMN `sys_img` `sys_type` tinyint(1) NOT NULL default '0'";
		$quer[] = 'ALTER TABLE `#__jstats_systems` ADD `sys_img` varchar(12) NOT NULL default \'noimage\'';
		
		//browser_img is not used - change it to browser_type. Add column image
		$quer[] = "ALTER TABLE `#__jstats_browsers` CHANGE COLUMN `browser_img` `browser_type` tinyint(1) NOT NULL default '0'";
		$quer[] = 'ALTER TABLE `#__jstats_browsers` ADD `browser_img` varchar(12) NOT NULL default \'noimage\'';
		
		// transfer what we have
		$JSDatabaseAccess->populateSQL( $quer );
	}
	
	if ($JSUtil->JSVersionCompare( $updateFromJSVersion, '2.3.0.216 dev', '<') == true) {
		$quer = array();

		//rename show_summarized to include_summarized
		$quer[] = 'UPDATE IGNORE `#__jstats_configuration` SET `description` = \'include_summarized\' WHERE `description` = \'show_summarized\'';

		//insert new parameter show_summarized. We must set it to 'false' (we do not know if include_summarized is set to true or to false)
		$quer[] = "INSERT IGNORE INTO #__jstats_configuration (description, value) VALUES ".
				  "('show_summarized', 'false') ";
		
		// transfer what we have
		$JSDatabaseAccess->populateSQL( $quer );
	}

	if ($JSUtil->JSVersionCompare( $updateFromJSVersion, '2.3.0.231 dev', '<') == true) {
		$quer = array();

		$quer[] = 'DROP TABLE `#__jstats_browsers`';

		//remove auto_increment option
		$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_browsers` ('
		  		. ' `browser_id` mediumint(9) NOT NULL,'
		  		. ' `browser_string` varchar(50) NOT NULL default \'\','
		  		. ' `browser_fullname` varchar(50) NOT NULL default \'\','
		        . ' `browser_type` tinyint(1) NOT NULL default \'0\','
		        . ' `browser_img` varchar(12) NOT NULL default \'noimage\','
		  		. ' PRIMARY KEY  (`browser_id`),'
		  		. ' UNIQUE KEY `browser_string` (`browser_string`)'
		  		. ' ) TYPE=MyISAM';

		$quer[] = 'DROP TABLE `#__jstats_systems`';

		//remove auto_increment option
		$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_systems` ('
		        . ' `sys_id` mediumint(9) NOT NULL,'
		        . ' `sys_string` varchar(25) NOT NULL default \'\','
		        . ' `sys_fullname` varchar(25) NOT NULL default \'\','
		        . ' `sys_type` tinyint(1) NOT NULL default \'0\','
		        . ' `sys_img` varchar(12) NOT NULL default \'noimage\','
		        . ' PRIMARY KEY (`sys_id`)'
		        . ' ) TYPE=MyISAM';

		$quer[] = 'DROP TABLE `#__jstats_topleveldomains`';

		//extend size od tld column, remove auto_increment option
		$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_topleveldomains` ('
		        . ' `tld_id` mediumint(9) NOT NULL,'
		        . ' `tld` varchar(9) NOT NULL default \'\','
		        . ' `fullname` varchar(255) NOT NULL default \'\','
		        . ' PRIMARY KEY (`tld_id`),'
		        . ' KEY `tld` (`tld`)'
		        . ' ) TYPE=MyISAM';

		
		// transfer what we have
		$JSDatabaseAccess->populateSQL( $quer );
	}

	if ($JSUtil->JSVersionCompare( $updateFromJSVersion, '2.3.0.232 dev', '<') == true) {
		if ($wereColumnsScreenAndWhoisCreated == true) {
			$quer = array();
	
			//see task "Remove 'screen' and 'whois' column" for details
			$quer[] = 'ALTER IGNORE TABLE `#__jstats_ipaddresses` DROP COLUMN `screen`';
			$quer[] = 'ALTER IGNORE TABLE `#__jstats_ipaddresses` DROP COLUMN `whois`';

			// transfer what we have
			$JSDatabaseAccess->populateSQL( $quer );
		}
	}







	// #__jstats_browsers, #__jstats_bots
	if ($JSUtil->JSVersionCompare( $updateFromJSVersion, '2.5.0.316 dev', '<') == true) {

		$quer = array();
		$quer[] = 'DROP TABLE `#__jstats_bots`';
		$quer[] = 'DROP TABLE `#__jstats_browsers`';

		$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_browsers` ('
		        . ' `browser_id` SMALLINT UNSIGNED NOT NULL,'
		        . ' `browsertype_id` TINYINT UNSIGNED NOT NULL,'
		        . ' `browser_key` varchar(50) NOT NULL,'
		        . ' `browser_name` varchar(50) NOT NULL,'
		        . ' `browser_img` varchar(12) NOT NULL default \'noimage\','
		        . ' PRIMARY KEY (`browser_id`)'
		        . ' ) TYPE=MyISAM';

		// transfer what we have
		$JSDatabaseAccess->populateSQL( $quer );
	}

	// #__jstats_page_request -> #__jstats_impressions
	if ($JSUtil->JSVersionCompare( $updateFromJSVersion, '2.5.0.316 dev', '<') == true) {

		$quer = array();
		//drop relevant columns, prepare more space in DB
		$quer[] = 'ALTER TABLE `#__jstats_page_request` DROP COLUMN `hour`';
		$quer[] = 'ALTER TABLE `#__jstats_page_request` DROP COLUMN `day`';
		$quer[] = 'ALTER TABLE `#__jstats_page_request` DROP COLUMN `month`';
		$quer[] = 'ALTER TABLE `#__jstats_page_request` DROP COLUMN `year`';
		$quer[] = 'OPTIMIZE TABLE `#__jstats_page_request`';

		// transfer what we have
		$JSDatabaseAccess->populateSQL( $quer );


		$quer = array();
		//create new table, transfer data, delete old table  (droping table is better than transforming it. If some extra indexes or columns exists we remove them)
		$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_impressions` ('
		        . ' `page_id` MEDIUMINT UNSIGNED NOT NULL,'
		        . ' `visit_id` MEDIUMINT UNSIGNED NOT NULL'
		        //. ' `impression_length` SMALLINT UNSIGNED NOT NULL COMMENT \'How long page was viewed. In seconds\',' //curently not implemented
		        //. ' KEY `page_id` (`page_id`),'
		        //. ' KEY `visit_id` (`visit_id`)'
		        . ' ) TYPE=MyISAM';
		$quer[] = 'INSERT INTO `#__jstats_impressions` (`page_id`,`visit_id`)'
  				. ' SELECT `page_id`, `ip_id`'
  				. ' FROM `#__jstats_page_request`';
		$quer[] = 'DROP TABLE `#__jstats_page_request`';

		// transfer what we have
		$JSDatabaseAccess->populateSQL( $quer );
	}

	// #__jstats_visits
	if ($JSUtil->JSVersionCompare( $updateFromJSVersion, '2.5.0.316 dev', '<') == true) {

		$quer = array();
		$quer[] = 'ALTER TABLE `#__jstats_visits` DROP COLUMN `hour`';
		$quer[] = 'ALTER TABLE `#__jstats_visits` DROP COLUMN `day`';
		$quer[] = 'ALTER TABLE `#__jstats_visits` DROP COLUMN `month`';
		$quer[] = 'ALTER TABLE `#__jstats_visits` DROP COLUMN `year`';
		$quer[] = 'DELETE FROM `#__jstats_visits` WHERE `time` = \'0000-00-00 00:00:00\'';
		$quer[] = 'OPTIMIZE TABLE `#__jstats_visits`';
		$quer[] = 'RENAME TABLE `#__jstats_visits` TO `#__jstats_visits_old`';

		// transfer what we have
		$JSDatabaseAccess->populateSQL( $quer );

		//rename columns to new names
		$quer = array();
		$quer[] = 'CREATE TABLE IF NOT EXISTS #__jstats_visits ('
				. ' `visit_id` MEDIUMINT UNSIGNED NOT NULL auto_increment,'
				. ' `visitor_id` MEDIUMINT UNSIGNED NOT NULL,'
				//. ' `joomla_userid` MEDIUMINT NOT NULL COMMENT \'Joomla CMS UserId\','
				. ' `joomla_userid` MEDIUMINT NOT NULL,'
				//. ' `visit_date` DATE NOT NULL COMMENT \'visit date in Joomla Local time zone\', '
				. ' `visit_date` DATE NOT NULL, '
				//. ' `visit_time` TIME NOT NULL COMMENT \'visit time in Joomla Local time zone\', '
				. ' `visit_time` TIME NOT NULL, '
				. ' PRIMARY KEY (visit_id),'
				. ' KEY `visit_date` (`visit_date`),'
				. ' KEY `visitor_id` (`visitor_id`)'
				. ' ) TYPE=MyISAM';
		$quer[] = 'INSERT INTO `#__jstats_visits` (`visit_id`,`visitor_id`,`joomla_userid`,`visit_date`,`visit_time`)'
  				. ' SELECT `id`, `ip_id`, `userid`, CONCAT(YEAR(`time`), \'-\', MONTH(`time`), \'-\', DAYOFMONTH(`time`)), CONCAT(HOUR(`time`), \':\', MINUTE(`time`), \':\', SECOND(`time`))'
  				. ' FROM `#__jstats_visits_old`';
		$quer[] = 'DROP TABLE `#__jstats_visits_old`';

		// transfer what we have
		$JSDatabaseAccess->populateSQL( $quer );
	}


	if ($JSUtil->JSVersionCompare( $updateFromJSVersion, '3.0.0.372 dev', '<') == true) {
		$quer = array();
		
		$quer[] = 'ALTER TABLE `#__jstats_keywords` ADD `visit_id` MEDIUMINT UNSIGNED NOT NULL';
		$quer[] = 'ALTER TABLE `#__jstats_referrer` ADD `visit_id` MEDIUMINT UNSIGNED NOT NULL';
		
		// transfer what we have
		$JSDatabaseAccess->populateSQL( $quer );
	}

	if ($JSUtil->JSVersionCompare( $updateFromJSVersion, '3.0.0.382 dev', '<') == true) {
		$quer = array();
		
		$quer[] = 'DROP TABLE `#__jstats_search_engines`';

		$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_searchers` ('
		        . ' `searcher_id` MEDIUMINT UNSIGNED NOT NULL,'//not auto_increment!
		        . ' `searcher_name` varchar(100) NOT NULL,'
		        . ' `searcher_domain` varchar(100) NOT NULL,'
		        . ' `searcher_key` varchar(50) NOT NULL,'
		        . ' PRIMARY KEY (`searcher_id`)'
		        . ' ) TYPE=MyISAM';

		$quer[] = "ALTER TABLE `#__jstats_keywords` CHANGE COLUMN `searchid` `searcher_id` mediumint(9) NOT NULL default '0'";

		$quer[] = 'UPDATE `#__jstats_keywords` SET `searcher_id` = 90 WHERE `searcher_id` = 2';
		$quer[] = 'UPDATE `#__jstats_keywords` SET `searcher_id` = 91 WHERE `searcher_id` = 3';
		$quer[] = 'UPDATE `#__jstats_keywords` SET `searcher_id` = 92 WHERE `searcher_id` = 5';
		$quer[] = 'UPDATE `#__jstats_keywords` SET `searcher_id` = 5  WHERE `searcher_id` = 4';
		$quer[] = 'UPDATE `#__jstats_keywords` SET `searcher_id` = 3  WHERE `searcher_id` = 1';

		// transfer what we have
		$JSDatabaseAccess->populateSQL( $quer );
	}

	if ($JSUtil->JSVersionCompare( $updateFromJSVersion, '3.0.0.393 dev', '<') == true) {
		$quer = array();

		//there was bug in version (about) 3.0.0.372 dev - now we remove duplicated rows (the same row is duplicated in two tables). This query fix this problem in 100% and leave db unharmed //only harm is addiotional rows in #__jstats_referrer table
		$quer[] = 'DELETE IGNORE FROM `#__jstats_referrer` WHERE `visit_id`>0 AND EXISTS (SELECT * FROM `#__jstats_keywords` WHERE #__jstats_keywords.visit_id = #__jstats_referrer.visit_id)';

		// transfer what we have
		$JSDatabaseAccess->populateSQL( $quer );
	}

	if ($JSUtil->JSVersionCompare( $updateFromJSVersion, '3.0.1.488 dev', '<') == true) {
		$quer = array();

		//there are some more entries, but it is hard to define which are wrong. See "[#18970] Keywords are incorrectly recognized" and "[#18344] *** glibc detected *** double free or corruption (fasttop) with AOL search results links"
		$quer[] = 'DELETE IGNORE FROM `#__jstats_keywords` WHERE CHAR_LENGTH(`keywords`)<=1';
		$quer[] = 'UPDATE IGNORE `#__jstats_keywords` SET `keywords` = SUBSTRING(`keywords`, 2, CHAR_LENGTH(`keywords`)-2) WHERE LEFT(`keywords`, 1)=\'\\\'\' AND RIGHT(`keywords`, 1)=\'\\\'\'';
		//$quer[] = 'DELETE IGNORE FROM `#__jstats_keywords` WHERE CHAR_LENGTH(`keywords`)<=1'; it will be performed in '3.0.1.495 dev' section
		$quer[] = 'DELETE IGNORE FROM `#__jstats_keywords` WHERE LEFT(LTRIM(`keywords`),1)=\'&\'';
		//$quer[] = 'DELETE IGNORE FROM `#__jstats_keywords` WHERE `keywords` LIKE \'%site:%\''; NO we should not remove those entries
		//$quer[] = 'DELETE IGNORE FROM `#__jstats_keywords` WHERE `keywords` LIKE \'%http://%\''; NO we should not remove those entries

		// transfer what we have
		$JSDatabaseAccess->populateSQL( $quer );
	}

	if ($JSUtil->JSVersionCompare( $updateFromJSVersion, '3.0.1.495 dev', '<') == true) {
		$quer = array();

		//there are some more entries, but it is hard to define which are wrong. See "[#18970] Keywords are incorrectly recognized" and "[#18344] *** glibc detected *** double free or corruption (fasttop) with AOL search results links"
		$quer[] = 'DELETE IGNORE FROM `#__jstats_keywords` WHERE CHAR_LENGTH(`keywords`)<=2';
		$quer[] = 'DELETE IGNORE FROM `#__jstats_keywords` WHERE CHAR_LENGTH(`keywords`)<=3 AND LEFT(`keywords`, 1)=\'h\'';

		// transfer what we have
		$JSDatabaseAccess->populateSQL( $quer );
	}

	if ($JSUtil->JSVersionCompare( $updateFromJSVersion, '3.0.3.588 dev', '<') == true) {
		$quer = array();

		//column comments where accidentialy added in version '2.5.0.316 dev'. Columns comments are not supported by MySQL v3.23 - Here we remove them
		$quer[] = 'ALTER TABLE `#__jstats_visits` CHANGE COLUMN `joomla_userid` `joomla_userid` MEDIUMINT NOT NULL';
		$quer[] = 'ALTER TABLE `#__jstats_visits` CHANGE COLUMN `visit_date` `visit_date` DATE NOT NULL';
		$quer[] = 'ALTER TABLE `#__jstats_visits` CHANGE COLUMN `visit_time` `visit_time` TIME NOT NULL';

		// transfer what we have
		$JSDatabaseAccess->populateSQL( $quer );
	}


	//migrate table #__jstats_page_request_c to #__jstats_impressions_sums
	if ($JSUtil->JSVersionCompare( $updateFromJSVersion, '3.0.3.604 dev', '<') == true) {

		$quer = array();

		$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_impressions_sums` ('
		        . ' `page_id` MEDIUMINT UNSIGNED NOT NULL,'
		        //. ' `impression_date` date NOT NULL COMMENT \'Impressions date in Joomla Local time zone\','
		        . ' `impression_date` date NOT NULL,'
		        //. ' `impression_number` SMALLINT UNSIGNED default NULL COMMENT \'How many times page was viewed\','
		        . ' `impression_number` SMALLINT UNSIGNED default NULL,'
		        //. ' `impression_length_sum` SMALLINT UNSIGNED NOT NULL COMMENT \'How long page was viewed. In seconds\','  //curently not implemented
		        . ' `impression_length_sum` SMALLINT UNSIGNED NOT NULL'  //curently not implemented
		        //. ' KEY `impression_date` (`impression_date`)'we should check if we should use this index (KEY)
		        . ' ) TYPE=MyISAM';

		$quer[] = 'INSERT INTO `#__jstats_impressions_sums` (`page_id`,`impression_date`,`impression_number`,`impression_length_sum`)'
  				. ' SELECT `page_id`, CAST( CONCAT(s.`year`, \'-\', s.`month`, \'-\', s.`day`) AS DATE) AS ddd, SUM(`count`) AS sss, 0'
  				. ' FROM `#__jstats_page_request_c` AS s GROUP by `page_id`, ddd ORDER BY ddd ASC';

		$quer[] = 'DROP TABLE `#__jstats_page_request_c`';

		// transfer what we have
		$JSDatabaseAccess->populateSQL( $quer );
	}

	//add first_installation_date
	if ($JSUtil->JSVersionCompare( $updateFromJSVersion, '3.0.3.604 dev', '<') == true) {

		$quer_summ = 'SELECT CAST( CONCAT(s.`impression_date`, \' 0:0:0\') AS DATETIME) AS summ FROM `#__jstats_impressions_sums` AS s ORDER BY summ ASC LIMIT 1';

		$quer_imp = 'SELECT CAST( CONCAT(v.`visit_date`, \' \', v.`visit_time`) AS DATETIME) AS imp FROM `#__jstats_visits` AS v ORDER BY imp ASC LIMIT 1';

		//$first_installation_date = '1971-01-01 01:01:01';
		//$fid_ts = strtotime( $first_installation_date );
		$fid_ts = gmmktime();

		$JSDatabaseAccess->db->setQuery( $quer_summ );
		$summ_date = $JSDatabaseAccess->db->loadResult();
		if ($JSDatabaseAccess->db->getErrorNum() > 0)
			$installationErrorMsg .= 'Error: Could not update version to 3.0.3.604 dev #1';
		if ($summ_date) {
			$summ_ts = strtotime( $summ_date );
			$summ_ts = $summ_ts - 14*60*60; //stored date could be in local time (we do not know time zone), so we move 14h back to be sure that we are in GMT
			if ( ($summ_ts !== false) && ($summ_ts != -1) && ($summ_ts < $fid_ts) )
				$fid_ts = $summ_ts;
		}

		$JSDatabaseAccess->db->setQuery( $quer_imp );
		$imp_date = $JSDatabaseAccess->db->loadResult();
		if ($JSDatabaseAccess->db->getErrorNum() > 0)
			$installationErrorMsg .= 'Error: Could not update version to 3.0.3.604 dev #2';
		if ($imp_date) {
			$imp_ts = strtotime( $imp_date );
			$imp_ts = $imp_ts - 14*60*60; //stored date could be in local time (we do not know time zone), so we move 14h back to be sure that we are in GMT
			if ( ($imp_ts !== false) && ($imp_ts != -1) && ($imp_ts < $fid_ts) )
				$fid_ts = $imp_ts;
		}

		if ( ($fid_ts === false) || ($fid_ts == -1) )
			$first_installation_date = '1971-01-01 01:01:01'; //this line never should be executed, but better safe than sorry
		else
			$first_installation_date = gmdate('Y-m-d H:i:s', $fid_ts);

		$quer = array();
		$quer[] = 'INSERT IGNORE INTO `#__jstats_configuration` (`description`, `value`) VALUES (\'first_installation_date\', \''.$first_installation_date.'\')';
		$JSDatabaseAccess->populateSQL( $quer );
	}

	if ($JSUtil->JSVersionCompare( $updateFromJSVersion, '3.0.3.632 dev', '<') == true) {
		$quer = array();

		$quer[] = 'DELETE IGNORE FROM `#__jstats_impressions_sums` WHERE `impression_number` IS NULL OR `impression_number`=0';
		$quer[] = 'ALTER TABLE `#__jstats_impressions_sums` CHANGE COLUMN `impression_number` `impression_number` SMALLINT UNSIGNED NOT NULL';

		// transfer what we have
		$JSDatabaseAccess->populateSQL( $quer );
	}


	//  !!!                                                                     !!!
	//  !!!    fix from 3.0.0.393 dev should be performed once again in MySQL5  !!!
	//  !!!                                                                     !!!


	{//update domain and Joomla CMS portal title in table #__jstats_searchers
		//update is performed on status page (just after install) because domain and Joomla CMS portal title could change at any time
	}


	{//update version and notification about update
		$quer = array();
	
		$quer[] = "UPDATE #__jstats_configuration SET value = '".$JSConfDef->JSVersion."' WHERE description = 'version'";
		
		$date_str = gmdate("Y-m-d_H:i:s");  //since v3.0.3.604 date() is replaced by gmdate()
		$quer[] = "INSERT IGNORE INTO #__jstats_configuration (description, value) VALUES ".
				  "('db_update_".$date_str."_to_version', '".$JSConfDef->JSVersion."') ";
		
		// transfer what we have
		$JSDatabaseAccess->populateSQL( $quer );
	}
}

