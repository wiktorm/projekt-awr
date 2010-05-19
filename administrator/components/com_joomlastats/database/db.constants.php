<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */


/**
 *  This file contain database constats and association them with human redable names
 *
 *  This file must be corelated with database structure and data!!
 *
 */

if( !defined( '_JS_STAND_ALONE' ) && !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}



// Naming convention - PHP constants
// name: JS prefix; PHP prefix; separator; human friendly variable name; separator; human friendly variable value; , value
// eg:   _JS        _PHP        _          _IMG                          _          _UNKNOWN                       , 'unknown'


define('_JS_PHP__IMG__UNKNOWN',                             'unknown');
define('_JS_PHP__PAGE_TITLE_FOR_PAGES_OUTSIDE_JOOMLA_CMS',  'Page outside Joomla CMS');






//
// JS table prefixes and shortcuts
//
// table name;   table symbol (used in SELECT syntax);   table symbol (used in defines)
//
//   #__jstats_browsers         b         BRWSR
//   #__jstats_browserstype               BRTYP
//   #__jstats_ipaddresses      w         IPADD    //will be renamed to #__jstats_visitor
//   #__jstats_iptocountry      c         
//   #__jstats_keywords         k
//   #__jstats_systems          o         OS       //will be renamed to #__jstats_os
//   #__jstats_ostype                     OSTYP
//   #__jstats_impressions      i                  
//   #__jstats_impression_sums  j                  
//   #__jstats_pages            p
//   #__jstats_referrer         r         
//   #__jstats_searchers        s         SERCH
//   #__jstats_visits           v
//   #__jstats_topleveldomains  t         TLD      





//Naming convention
// name: JS prefix; DB prefix; table prefix (see above); separator; column name; human friendly name; , database value
// eg:   _JS        _DB        _IPADD                    _          _TYPE        _BOTS                , 2


																//JTEXT::_('Visitor type')
define('_JS_DB_IPADD__TYPE_NOT_IDENTIFIED_VISITOR',  0);		//JTEXT::_('Not identified visitor')	//JTEXT::_('Not identified')
define('_JS_DB_IPADD__TYPE_REGULAR_VISITOR',         1);		//JTEXT::_('Regular visitor')			//JTEXT::_('Regular')
define('_JS_DB_IPADD__TYPE_BOT_VISITOR',             2);		//JTEXT::_('Bot visitor')				//JTEXT::_('Bot')

//below defines are used when we do not know if there was a bot or browser
define('_JS_DB_BRWSR__ID_UNKNOWN',                   0);
define('_JS_DB_BRWSR__KEY_UNKNOWN',                  '');
define('_JS_DB_BRWSR__NAME_UNKNOWN',                 'Unknown');
define('_JS_DB_BRWSR__IMG_UNKNOWN',                  'unknown');

//below defines are used when we know that there was a browser (not a bot), but this is unknown browser
define('_JS_DB_BRWSR__ID_BROWSER_UNKNOWN',           1);
define('_JS_DB_BRWSR__KEY_BROWSER_UNKNOWN',          'unknown internet browser');
define('_JS_DB_BRWSR__NAME_BROWSER_UNKNOWN',         'Unknown Internet Browser');
define('_JS_DB_BRWSR__IMG_BROWSER_UNKNOWN',          'unknown');

//below defines are used when we know that there was a bot (not a browser), but this is unknown bot
define('_JS_DB_BRWSR__ID_BOT_UNKNOWN',           1024);
define('_JS_DB_BRWSR__KEY_BOT_UNKNOWN',          'unknown bot');
define('_JS_DB_BRWSR__NAME_BOT_UNKNOWN',         'Unknown Bot');
define('_JS_DB_BRWSR__IMG_BOT_UNKNOWN',          'unknown');


define('_JS_DB_OS__ID_UNKNOWN',                      0);
define('_JS_DB_OS__KEY_UNKNOWN',                     '');
define('_JS_DB_OS__NAME_UNKNOWN',                    'Unknown');
define('_JS_DB_OS__IMG_UNKNOWN',                     'unknown');


define('_JS_DB_TLD__ID_UNKNOWN',                     0);
define('_JS_DB_TLD__TLD_UNKNOWN',                    'unknown');
define('_JS_DB_TLD__NAME_UNKNOWN',                   'Unknown');


define('_JS_DB_OSTYP__ID_UNKNOWN',                   0);
define('_JS_DB_OSTYP__ID_WINDOWS',                   1);
define('_JS_DB_OSTYP__ID_LINUX_UNIX_MAC',            2);//windows or unix or mac
define('_JS_DB_OSTYP__ID_PDA_PHONE',                 3);//pda or phone or mobile
define('_JS_DB_OSTYP__ID_OTHER',                     4);

define('_JS_DB_OSTYP__NAME_UNKNOWN',                 'Unknown');
define('_JS_DB_OSTYP__IMG_UNKNOWN',                  'unknown');


define('_JS_DB_BRTYP__ID_UNKNOWN',                   0);
define('_JS_DB_BRTYP__ID_INTERNET_EXPLORER',         1);
define('_JS_DB_BRTYP__ID_FIREFOX',                   2);
define('_JS_DB_BRTYP__ID_OPERA',                     3);
define('_JS_DB_BRTYP__ID_PDA_PHONE',                 4);
define('_JS_DB_BRTYP__ID_OTHER',                     5);

define('_JS_DB_BRTYP__NAME_UNKNOWN',                 'Unknown');
define('_JS_DB_BRTYP__IMG_UNKNOWN',                  'unknown');


define('_JS_DB_SERCH__ID_UNKNOWN',                   0);
define('_JS_DB_SERCH__ID_SEARCH_JOOMLA_CMS',         1);
define('_JS_DB_SERCH__ID_JOOMLA_CMS',                2);





//#############################################################
//
//              V I R T U A L   T A B L E S
//
//#############################################################


//
// There is no need to create such tables in DB
// They are to small and database queries will slow JS
//




/**
 *  #__jstats_ostype
 *
 *  Table #__jstats_os_type, holds info about Operating System Types
 *
 *  NOTICE: In future we integrate #__jstats_os and #__jstats_ostype together
 */
//  defines used below: _JS_DB_OSTYP__NAME_UNKNOWN, _JS_DB_OSTYP__IMG_UNKNOWN

$__jstats_ostype = array(
	_JS_DB_OSTYP__ID_UNKNOWN        => array('ostype_id' => _JS_DB_OSTYP__ID_UNKNOWN,        'ostype_name' => 'Unknown',            'ostype_img' => 'unknown'),
	_JS_DB_OSTYP__ID_WINDOWS        => array('ostype_id' => _JS_DB_OSTYP__ID_WINDOWS,        'ostype_name' => 'Windows',            'ostype_img' => 'windowsxp'),
	_JS_DB_OSTYP__ID_LINUX_UNIX_MAC => array('ostype_id' => _JS_DB_OSTYP__ID_LINUX_UNIX_MAC, 'ostype_name' => 'Linux, Unix or Mac', 'ostype_img' => 'linux'),
	_JS_DB_OSTYP__ID_PDA_PHONE      => array('ostype_id' => _JS_DB_OSTYP__ID_PDA_PHONE,      'ostype_name' => 'PDA or Phone',       'ostype_img' => 'pda'),
	_JS_DB_OSTYP__ID_OTHER          => array('ostype_id' => _JS_DB_OSTYP__ID_OTHER,          'ostype_name' => 'Other',              'ostype_img' => 'other'),
);

define('_JS_DB_TABLE__OSTYPE',                     serialize($__jstats_ostype));
unset($__jstats_ostype);





/**
 *  #__jstats_browserstype
 *
 *  Table #__jstats_browserstype, holds info about Browser Types
 *
 *  NOTICE: This class will NOT be integrated with #__jstats_browsers beacuse #__jstats_browsers table conatin also bots
 */

$__jstats_browserstype = array(
	_JS_DB_BRTYP__ID_UNKNOWN            => array('ostype_id' => _JS_DB_BRTYP__ID_UNKNOWN,           'browsertype_name' => 'Unknown',           'browsertype_img' => 'unknown'),
	_JS_DB_BRTYP__ID_INTERNET_EXPLORER  => array('ostype_id' => _JS_DB_BRTYP__ID_INTERNET_EXPLORER, 'browsertype_name' => 'Internet Explorer', 'browsertype_img' => 'explorer'),
	_JS_DB_BRTYP__ID_FIREFOX            => array('ostype_id' => _JS_DB_BRTYP__ID_FIREFOX,           'browsertype_name' => 'Fire Fox',          'browsertype_img' => 'firefox'),
	_JS_DB_BRTYP__ID_OPERA              => array('ostype_id' => _JS_DB_BRTYP__ID_OPERA,             'browsertype_name' => 'Opera',             'browsertype_img' => 'opera'),
	_JS_DB_BRTYP__ID_PDA_PHONE          => array('ostype_id' => _JS_DB_BRTYP__ID_PDA_PHONE,         'browsertype_name' => 'PDA or Phone',      'browsertype_img' => 'pda'),
	_JS_DB_BRTYP__ID_OTHER              => array('ostype_id' => _JS_DB_BRTYP__ID_OTHER,             'browsertype_name' => 'Other',             'browsertype_img' => 'other'),
);

define('_JS_DB_TABLE__BROWSERSTYPE',                serialize($__jstats_browserstype));
unset($__jstats_browserstype);
