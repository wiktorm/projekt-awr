<?php
/**
* Users Online Module 1.2
* $Id: mod_comprofileronline.php 844 2010-01-27 09:09:17Z beat $
* 
* @version 1.2
* @package Community Builder 1.2
* @Copyright (C) 2004-2010 Beat and 2000 - 2003 Miro International Pty Ltd
* @ All rights reserved
* @ Mambo Open Source is Free Software
* @ Released under GNU/GPL License : http://www.gnu.org/copyleft/gpl.html
**/

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * CB framework
 * @global CBframework $_CB_framework
 */
global $_CB_framework, $_CB_database, $ueConfig, $mainframe;
if ( defined( 'JPATH_ADMINISTRATOR' ) ) {
	if ( ! file_exists( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' ) ) {
		echo 'CB not installed';
		return;
	}
	include_once( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' );
} else {
	if ( ! file_exists( $mainframe->getCfg( 'absolute_path' ) . '/administrator/components/com_comprofiler/plugin.foundation.php' ) ) {
		echo 'CB not installed';
		return;
	}
	include_once( $mainframe->getCfg( 'absolute_path' ) . '/administrator/components/com_comprofiler/plugin.foundation.php' );
}
cbimport( 'cb.database' );
cbimport( 'language.front' );

function getNameFormatOnline($name,$uname,$format) {
	if ( $format != 3 ) {
		$name	=	str_replace( array("&amp;","&quot;","&#039;","&lt;","&gt;"), array("&","\"","'","<",">"), $name );
	}
	SWITCH ($format) {
		CASE 1 :
			$returnName = $name;
		break;
		CASE 2 :
			$returnName = $name." (".$uname.")";
		break;
		CASE 3 :
			$returnName = $uname;
		break;
		CASE 4 :
			$returnName = $uname." (".$name.")";
		break;
	}
	return $returnName;
}

// $params is defined by include: ignore this warning:
if (is_callable(array($params,"get"))) {				// Mambo 4.5.0 compatibility
	$class_sfx	=	$params->get( 'moduleclass_sfx');
	$pretext 	=	$params->get( 'pretext', "" );
	$posttext 	=	$params->get( 'posttext', "" );
} else {
	$class_sfx	=	'';
	$pretext	=	'';
	$posttext	=	'';
}

$query			=	"SELECT DISTINCT a.username, a.userid, u.name"
."\n FROM #__session AS a, #__users AS u"
."\n WHERE (a.userid = u.id) AND (a.guest = 0) AND "
.	( ( checkJversion() == 1 ) ? "(a.client_id = 0)" : "(NOT ( a.usertype is NULL OR a.usertype = ''))" )
."\n ORDER BY " . ( ( $ueConfig['name_format'] > 2 ) ? "a.username" : "u.name" ) . " ASC";
$_CB_database->setQuery($query);
$rows			=	$_CB_database->loadObjectList();

$result			=	'';
if ( count( $rows ) > 0) {
	$result		.=	"<ul class='mod_login".$class_sfx."'>\n";	// style='list-style-type:none; margin:0px; padding:0px; font-weight:bold;'
	foreach($rows as $row) {
		$result	.=	"<li><a href='" . cbSef( 'index.php?option=com_comprofiler&amp;task=userProfile&amp;user=' . $row->userid . getCBprofileItemid( true, false ) )
				.	"' class='mod_login".$class_sfx."'>".htmlspecialchars(getNameFormatOnline($row->name,$row->username,$ueConfig['name_format']))."</a></li>\n";
	}
	$result		.=	"</ul>\n";
	if ( $pretext != '' ) {
		$result	=	$pretext . "<br />\n" . $result;
	}
	$result		.=	$posttext;
} else {
	$result		.=	_UE_NONE;
}
echo $result;
?>
