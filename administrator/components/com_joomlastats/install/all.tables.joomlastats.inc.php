<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */



if( !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}



//  
//  BIGINT    UNSIGNED: 0 to 2^64     do not use arithmetic!
//  INT       UNSIGNED: 0 to 2^32   4 294 967 295
//  MEDIUMINT UNSIGNED: 0 to 2^24      16 777 215
//  SMALLINT  UNSIGNED: 0 to 2^16          65 535
//  TINYINT   UNSIGNED: 0 to 2^8              256
//  



/* ############### create tables and insert configuration ############# */



/**  deprecated since v2.5.0.313 (integrated with #__jstats_browsers table
$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_bots` ('
        . ' `bot_id` mediumint(9) NOT NULL auto_increment,'
        . ' `bot_string` varchar(50) NOT NULL default \'\','
        . ' `bot_fullname` varchar(50) NOT NULL default \'\','
        . ' PRIMARY KEY (`bot_id`),'
        . ' UNIQUE KEY `bot_string` (`bot_string`)'
        . ' ) TYPE=MyISAM';
*/

/** deprecated since v2.5.0.313 - new structure - see below
$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_browsers` ('
  		. ' `browser_id` mediumint(9) NOT NULL,'
  		. ' `browser_string` varchar(50) NOT NULL default \'\','
  		. ' `browser_fullname` varchar(50) NOT NULL default \'\','
        . ' `browser_type` tinyint(1) NOT NULL default \'0\','
        . ' `browser_img` varchar(12) NOT NULL default \'noimage\','
  		. ' PRIMARY KEY  (`browser_id`),'
  		. ' UNIQUE KEY `browser_string` (`browser_string`)'
  		. ' ) TYPE=MyISAM';
*/

/**
 *  This table hold browsers and BOTS
 *
 *  RANGES (browser_id):
 *        0 -   511  - JS defined internet browsers
 *      512 -  1023  - user defined internet browsers (user can add here own browsers)
 *     1024 -  2047  - JS defined bots/spiders/crawlers
 *     2048 - 65535  - user defined internet bots/spiders/crawlers (user can add here own bots/spiders/crawlers)
 */
$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_browsers` ('
        . ' `browser_id` SMALLINT UNSIGNED NOT NULL,'
        . ' `browsertype_id` TINYINT UNSIGNED NOT NULL,'
        . ' `browser_key` varchar(50) NOT NULL,'
        . ' `browser_name` varchar(50) NOT NULL,'
        . ' `browser_img` varchar(12) NOT NULL default \'noimage\','
        . ' PRIMARY KEY (`browser_id`)'
        . ' ) TYPE=MyISAM';


$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_configuration` ('
        . ' `description` varchar(250) NOT NULL default \'-\','
        . ' `value` varchar(250) default NULL,'
        . ' PRIMARY KEY (`description`)'
        . ' ) TYPE=MyISAM';

/** #__jstats_ipaddresses table will be replaced by #__jstats_visitors table - see below */
$quer[] = 'CREATE TABLE IF NOT EXISTS #__jstats_ipaddresses ('
		. ' ip varchar(50) NOT NULL default \'\','
		. ' nslookup varchar(255) default NULL,'
		. ' tld varchar(10) NOT NULL default \'unknown\','
		. ' useragent varchar(255) default NULL,'
		. ' system varchar(50) NOT NULL default \'\','
		. ' browser varchar(50) NOT NULL default \'\','
		. ' id mediumint(9) NOT NULL auto_increment,'
		. ' type tinyint(1) NOT NULL default \'0\','
		. ' exclude tinyint(1) NOT NULL default \'0\','
		. ' PRIMARY KEY (id),'
		. ' KEY type (type),'
		. ' KEY tld (tld)'
		. ' ) TYPE=MyISAM';

/* '`' characters should be applyied //varchar columns should be moved to end of table!
$quer[] = 'CREATE TABLE IF NOT EXISTS #__jstats_visitors ('
		. ' visitor_id mediumint NOT NULL auto_increment,'//mediumint? in PostgreSQL INT is faster. Should we use int?
		. ' visitor_ip varchar(50) NOT NULL default \'\',' //50? Is it to much? //ip as string? we should use approprate type! (int or something else)
		. ' visitor_nslookup varchar(255) default NULL,' //do we need this column? we always could get this value from gethostbyaddress(). Column is very long and probably it is never searched nor queries. //columns with vary length should be moved to end of table - for performance //in PHP documentation it is called 'Internet host name'
		. ' tld_id SMALLINT NOT NULL default \'0\','
		. ' os_id SMALLINT NOT NULL default \'0\','
		. ' browser_id SMALLINT NOT NULL default \'0\','//maybe we should concatenate tables __bots and __browsers? (eg. bots id above > 1024) Joins will be easier for everyone. Then we can remove column type.
		//. ' visitor_type TINYINT NOT NULL default \'0\',' no visitor type! browser_id say about this!
		. ' visitor_exclude TINYINT NOT NULL default \'0\','
		. ' visitor_useragent varchar(255) default NULL,'
		. ' PRIMARY KEY (id),'
		//. ' KEY type (visitor_type),' //I am not sure but I think making index from int column do not speed up database
		//. ' KEY tld (tld)'//not needed due to applying tld_id
		. ' ) TYPE=MyISAM';
*/

$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_iptocountry` ('
        . ' `IP_FROM` bigint(20) NOT NULL default \'0\','
        . ' `IP_TO` bigint(20) NOT NULL default \'0\','
        . ' `COUNTRY_CODE2` char(2) NOT NULL default \'\','
        . ' `COUNTRY_NAME` varchar(50) NOT NULL default \'\','
        . ' PRIMARY KEY (`IP_FROM`)'
        . ' ) TYPE=MyISAM';

/** #__jstats_keywords table will be replaced by #__jstats_keywords - see below
 *
 *  NOTICE: visit_id was introduced in v3.0.0.372 - old data were NOT converted to this value, so it can not be used!! It is introduced to collect data for the future!! (not all data could be converted to new format, that is why now we duplicate data!)
 *
 *  since v3.0.0.382 searchid => searcher_id
 */
$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_keywords` ('
        . ' `kwdate` date NOT NULL default \'0000-00-00\','
        . ' `searcher_id` mediumint(9) NOT NULL default \'0\','
        . ' `keywords` varchar(255) NOT NULL default \'\','
		. ' `visit_id` MEDIUMINT UNSIGNED NOT NULL'
        . ' ) TYPE=MyISAM';
/*
searchid 	=> searcher_id
kwdate		=> #visit_id#
keywords	=> since version '3.0.1.488 dev' two additional single quote characters were removed (on begining and on end of line)
$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_keywords` ('
        . ' `visit_id` MEDIUMINT UNSIGNED NOT NULL,'
        . ' `searcher_id` MEDIUMINT UNSIGNED NOT NULL,'
        . ' `keywords` varchar(255) NOT NULL'
        . ' ) TYPE=MyISAM';
*/

/** #__jstats_page_request table will be replaced by #__jstats_impressions ("page impression" is the same as "page view", impresion is better bacause we have 'visitor' and 'visit' (all 'v'). http://en.wikipedia.org/wiki/Page_view */
/** do not drop hour column - we will use it in graphs! */
/** 
 *         !!! ip_id IS visit_id NOT visitor_id !!!
 *  deprecated since v2.5.0.313
$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_page_request` ('
        . ' `page_id` mediumint(9) NOT NULL default \'0\','
        . ' `hour` tinyint(4) NOT NULL default \'0\','
        . ' `day` tinyint(4) NOT NULL default \'0\','
        . ' `month` tinyint(4) NOT NULL default \'0\','
        . ' `year` smallint(6) NOT NULL default \'0\','
        . ' `ip_id` mediumint(9) default NULL,'
        . ' KEY `page_id` (`page_id`),'
        . ' KEY `monthyear` (`month`,`year`),'
        . ' KEY `index_ip` (`ip_id`)'
        . ' ) TYPE=MyISAM';
 */
        
/**
 *  This is the biggest table (greatest number of rows), structure should be small as it is posible.
 *
 *  Indexes are not required. If someone need them we can treate them on user request (Tools->Performance tab)
 *  Indexes significaly increase this table size. They are used only in few places. In this case size is more required than performance.
 *
 *  removing `hour`, `day`, `month`, `year` - dates could be taken from #__jstats_visits table. This is the biggest table (greatest number of rows), structure should be small as it is posible.
 *
 *  Not using defaults - we exacly know what we insert. Moreover inserting with 0 make rows that are not connected with other data and make only confusion. 0 create unusable data 
 *  new from v2.5.0.313
 */
$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_impressions` ('
        . ' `page_id` MEDIUMINT UNSIGNED NOT NULL,'
        . ' `visit_id` MEDIUMINT UNSIGNED NOT NULL'
        //. ' `impression_length` SMALLINT UNSIGNED NOT NULL COMMENT \'How long page was viewed. In seconds\',' //curently not implemented
        //. ' KEY `page_id` (`page_id`),'
        //. ' KEY `visit_id` (`visit_id`)'
        . ' ) TYPE=MyISAM';
       
        
/** Previous implementation of table #__jstats_impressions_sums.
 *
 * #__jstats_page_request_c -> #__jstats_impressions_sums
 * page_id -> page_id
 * hour    - removed (At end of 2008 JS Team decide to remove this column. See documentation for details)
 * day     -> impression_date
 * month   -> impression_date
 * year    -> impression_date
 * count   -> impression_number
 *         -> impression_length_sum (new column)
 *
 *  Deprecated since v3.0.3.604
$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_page_request_c` ('
        . ' `page_id` mediumint(9) NOT NULL default \'0\','
        . ' `hour` tinyint(4) NOT NULL default \'0\','
        . ' `day` tinyint(4) NOT NULL default \'0\','
        . ' `month` tinyint(4) NOT NULL default \'0\','
        . ' `year` smallint(6) NOT NULL default \'0\','
        . ' `count` mediumint(9) default NULL,'
        . ' KEY `page_id` (`page_id`),'
        . ' KEY `monthyear` (`month`,`year`)'
        . ' ) TYPE=MyISAM';
*/



/* 
 * NOTICE:
 *   There could be that not summarized data are later then summarized data (in v3.0.2.604 and all previous) */
$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_impressions_sums` ('
        . ' `page_id` MEDIUMINT UNSIGNED NOT NULL,'
        //. ' `impression_date` date NOT NULL COMMENT \'Impressions date in Joomla Local time zone\','
        . ' `impression_date` date NOT NULL,'
        //. ' `impression_number` SMALLINT UNSIGNED NOT NULL COMMENT \'How many times page was viewed\','
        . ' `impression_number` SMALLINT UNSIGNED NOT NULL,'
        //. ' `impression_length_sum` SMALLINT UNSIGNED NOT NULL COMMENT \'How long page was viewed. In seconds\','  //curently not implemented
        . ' `impression_length_sum` SMALLINT UNSIGNED NOT NULL'  //curently not implemented
        //. ' KEY `impression_date` (`impression_date`)'we should check if we should use this index (KEY)
        . ' ) TYPE=MyISAM';



$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_pages` ('
        . ' `page_id` mediumint(9) NOT NULL auto_increment,'
        . ' `page` text NOT NULL,'
        . ' `page_title` varchar(255) default NULL,'
        . ' PRIMARY KEY (`page_id`)'
        . ' ) TYPE=MyISAM';

/** #__jstats_referrer table will be replaced by #__jstats_referrers - see below 
 *
 *  NOTICE: visit_id was introduced in v3.0.0.372 - old data were NOT converted to this value, so it can not be used!! It is introduced to collect data for the future!! (not all data could be converted to new format, that is why now we duplicate data!)
 */
$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_referrer` ('
        . ' `referrer` varchar(255) NOT NULL default \'\','
        . ' `domain` varchar(100) NOT NULL default \'unknown\','
        . ' `refid` mediumint(9) NOT NULL auto_increment,'
        . ' `day` tinyint(4) NOT NULL default \'0\','
        . ' `month` tinyint(4) NOT NULL default \'0\','
        . ' `year` smallint(6) NOT NULL default \'0\','
		. ' `visit_id` MEDIUMINT UNSIGNED NOT NULL,'
        . ' PRIMARY KEY (`refid`),'
        . ' KEY `referrer` (`referrer`),'
        . ' KEY `monthyear` (`month`,`year`)'
        . ' ) TYPE=MyISAM';
/*
refid				=> referrer_id  - We do use this column. Column removed, we use visit_id instead of referrer_id
day, month, year 	=> #visit_id#
referrer			=> referrer_url
domain				=> referrer_domain
NOTE: during convertion to new table, remember to delete all WHERE domain = 'unknown'!
$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_referrers` ('
        . ' `visit_id` MEDIUMINT UNSIGNED NOT NULL,'
        . ' `referrer_domain` varchar(100) NOT NULL,'
        . ' `referrer_url` varchar(255) NOT NULL,'
        . ' PRIMARY KEY (`referrer_id`),'
        . ' KEY `referrer_url` (`referrer_url`)'//should we index this column? Do We use this index anywhere?
        . ' ) TYPE=MyISAM';
*/
/** 
  #__jstats_search_engines replaced by #__jstats_searchers since v3.0.0.382 

searchid 		=> searcher_id
description		=> searcher_name
search			=> searcher_domain
searchvar		=> searcher_key

$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_search_engines` ('
        . ' `searchid` mediumint(9) NOT NULL auto_increment,'
        . ' `description` varchar(100) NOT NULL default \'\','
        . ' `search` varchar(100) NOT NULL default \'\','
        . ' `searchvar` varchar(50) NOT NULL default \'\','
        . ' PRIMARY KEY (`searchid`)'
        . ' ) TYPE=MyISAM';
*/

$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_searchers` ('
        . ' `searcher_id` MEDIUMINT UNSIGNED NOT NULL,'//not auto_increment!
        . ' `searcher_name` varchar(100) NOT NULL,'
        . ' `searcher_domain` varchar(100) NOT NULL,'
        . ' `searcher_key` varchar(50) NOT NULL,'
        . ' PRIMARY KEY (`searcher_id`)'
        . ' ) TYPE=MyISAM';
        
        
/** #__jstats_systems table will be replaced by #__jstats_os - see below */
$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_systems` ('
        . ' `sys_id` mediumint(9) NOT NULL,'
        . ' `sys_string` varchar(25) NOT NULL default \'\','
        . ' `sys_fullname` varchar(25) NOT NULL default \'\','
        . ' `sys_type` tinyint(1) NOT NULL default \'0\','
        . ' `sys_img` varchar(12) NOT NULL default \'noimage\','
        . ' PRIMARY KEY (`sys_id`)'
        . ' ) TYPE=MyISAM';
/*
$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_os` ('
        . ' `os_id` SMALLINT NOT NULL,'//not auto_increment!
        . ' `ostype_id` TINYINT NOT NULL default \'0\','
        . ' `os_key` varchar(25) NOT NULL default \'\','
        . ' `os_name` varchar(25) NOT NULL default \'\','
        . ' `os_img` varchar(12) NOT NULL default \'noimage\','
        . ' PRIMARY KEY (`os_id`)'
        . ' ) TYPE=MyISAM';
*/

//
//  VIRTUAL TABLE  - this table exist, but it is in php code (for performance)
//
//  NOTICE: Tables #__jstats_os and #__jstats_ostype will be merged together!!
//$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_ostype` ('
//        . ' `ostype_id` TINYINT NOT NULL,'//not auto_increment!
//        . ' `ostype_name` varchar(25) NOT NULL default \'\','
//        . ' `ostype_img` varchar(12) NOT NULL default \'noimage\','
//        . ' PRIMARY KEY (`ostype_id`)'
//        . ' ) TYPE=MyISAM';

/** #__jstats_topleveldomains table will be replaced by #__jstats_tlds - see below */
$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_topleveldomains` ('
        . ' `tld_id` mediumint(9) NOT NULL,'
        . ' `tld` varchar(9) NOT NULL default \'\','
        . ' `fullname` varchar(255) NOT NULL default \'\','
        . ' PRIMARY KEY (`tld_id`),'
        . ' KEY `tld` (`tld`)'
        . ' ) TYPE=MyISAM';
/* (localhost is the longest tld)
$quer[] = 'CREATE TABLE IF NOT EXISTS `#__jstats_tlds` ('
        . ' `tld_id` SMALLINT NOT NULL,' // not auto_increment!
        . ' `tld` varchar(9) NOT NULL default \'\',' //I have no idea how to call this :(  
        . ' `tld_name` varchar(255) NOT NULL default \'\','
        . ' PRIMARY KEY (`tld_id`),'
        . ' KEY `tld` (`tld`)'
        . ' ) TYPE=MyISAM';
*/


/**
 *  Structure of this table changed in version v2.5.0.301 (this table is updated to DB v3.0.0)
 *
 *  visit_id          - prevoiusly id
 *  visitor_id        - prevoiusly ip_id
 *  joomla_userid     - prevoiusly userid  //Joomla CMS use type INT (I know, it is not nice, mediumint is enough) //User ID if user is logged into 'Joomla CMS'. If user is not logged value is 0
 *
 *  visit_date        - Yes, without default!
 *  visit_time        - Yes, without default!   time never should be indexed!!! - it has no sense
 *
 *  Not using defaults - we exacly know what we insert. Moreover inserting with 0 make rows that are not connected with other data and make only confusion. 0 create unusable data 
 *  new from v2.5.0.313
 */
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

/* deprecated since version v2.5.0.313
$quer[] = 'CREATE TABLE IF NOT EXISTS #__jstats_visits ('
		. ' id mediumint(9) NOT NULL auto_increment,'
		. ' ip_id mediumint(9) NOT NULL default \'0\','
		. ' userid int(11) NOT NULL default \'0\','
		. ' hour tinyint(4) NOT NULL default \'0\','
		. ' day tinyint(4) NOT NULL default \'0\','
		. ' month tinyint(4) NOT NULL default \'0\','
		. ' year smallint(6) NOT NULL default \'0\','
		. ' time datetime NOT NULL default \'0000-00-00 00:00:00\','
		. ' PRIMARY KEY (id),'
		. ' KEY time (time),'
		. ' KEY ip_id (ip_id),'
		. ' KEY monthyear (month,year),'
		. ' KEY daymonthyear (day,month,year),'
		. ' KEY `userid` (`userid`)'
		. ' ) TYPE=MyISAM';
*/

		
		
		
				
		
		
// Insert other configuration if they don't exist (if the descriptions exist, they are kept save by primairy key 'description')
$quer[]  =  "INSERT IGNORE INTO #__jstats_configuration (description, value) VALUES".
			"('version', '".$JSConfDef->JSVersion."'),".
			"('onlinetime','".$JSConfDef->onlinetime."'),".
			"('startoption','".$JSConfDef->startoption."'),".
			"('startdayormonth','".$JSConfDef->startdayormonth."'),".
			"('enable_whois','".(($JSConfDef->enable_whois)?'true':'false')."'),".
			"('enable_i18n','".(($JSConfDef->enable_i18n)?'true':'false')."'),".
			"('include_summarized','".(($JSConfDef->include_summarized)?'true':'false')."'),".
			"('show_summarized','".(($JSConfDef->show_summarized)?'true':'false')."'),".
			"('db_installed_from_version', '".$JSConfDef->JSVersion."')";
			