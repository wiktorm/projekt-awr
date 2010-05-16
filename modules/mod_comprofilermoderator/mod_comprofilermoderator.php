<?php
/**
* CB Moderator Module 1.2
* $Id: mod_comprofilermoderator.php 831 2010-01-26 11:04:24Z beat $
* 
* @version 1.2
* @package Community Builder 1.2
* @subpackage CB Moderator Module
* @Copyright (C) MamboJoe and Beat at www.joomlapolis.com
* @ All rights reserved
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

if ( ! ( $_CB_framework->myId() > 0 ) ) {
	return;
}
cbimport( 'cb.database' );
cbimport( 'language.front' );
cbimport( 'cb.acl' );

// $params is defined by include: ignore this warning:
if ( is_callable( array( $params, 'get' ) ) ) {				// Mambo 4.5.0 compatibility
	$class_sfx	=	$params->get( 'moduleclass_sfx', "");
	$pretext 	=	$params->get( 'pretext', "" );
	$posttext 	=	$params->get( 'posttext', "" );
} else {
	$class_sfx	=	'';
	$pretext	=	'';
	$posttext	=	'';
}

$results = null;
$andItemid		=	getCBprofileItemid( true );

$query			=	"SELECT banned FROM #__comprofiler WHERE id = " . $_CB_framework->myId();
$_CB_database->setQuery( $query);
$banStatus		=	$_CB_database->loadResult();
if ( $banStatus === null ) {
	trigger_error( $_CB_database->getErrorMsg(), E_USER_WARNING );
}
if ( $banStatus > 0 ) {
	$results .= "<div><a href='".cbSef("index.php?option=com_comprofiler".$andItemid)."' class='mod_login".$class_sfx."'>" . ( ( $banStatus == 1 ) ? _UE_PLEAE_CHECK_PROFILE : _UE_BANSTATUS_UNBAN_REQUEST_PENDING ) . "</a></div>";
}

if ( isModerator( $_CB_framework->myId() ) ) {
	$query = "SELECT count(*) FROM #__comprofiler  WHERE avatarapproved=0 AND approved=1 AND confirmed=1 AND banned=0";
	$_CB_database->setQuery($query);
	$totalimages = $_CB_database->loadResult();
	if ( $totalimages === null ) trigger_error( $_CB_database->getErrorMsg(), E_USER_WARNING );

	$query = "SELECT count(*) FROM #__comprofiler_userreports  WHERE reportedstatus=0";
	$_CB_database->setQuery($query);
	$totaluserreports = $_CB_database->loadResult();
	if ( $totaluserreports === null ) trigger_error( $_CB_database->getErrorMsg(), E_USER_WARNING );

	$query = "SELECT count(*) FROM #__comprofiler WHERE banned=2 AND approved=1 AND confirmed=1";
	$_CB_database->setQuery($query);
	$totalunban = $_CB_database->loadResult();
	if ( $totalunban === null ) trigger_error( $_CB_database->getErrorMsg(), E_USER_WARNING );

	$query = "SELECT count(*) FROM #__comprofiler WHERE approved=0 AND confirmed=1";
	$_CB_database->setQuery($query);
	$totaluserpendapproval = $_CB_database->loadResult();
	if ( $totaluserpendapproval === null ) trigger_error( $_CB_database->getErrorMsg(), E_USER_WARNING );

	if($totalunban > 0 || $totaluserreports > 0 || $totalimages > 0 || ($totaluserpendapproval > 0 && $ueConfig['allowModUserApproval'])) {
		
		if($totalunban > 0) $results .= "<div><a href='".cbSef("index.php?option=com_comprofiler&amp;task=moderateBans".$andItemid)."' class='mod_login".$class_sfx."'>".$totalunban." "._UE_UNBANREQUIREACTION."</a></div>";
		if($totaluserreports > 0) $results .= "<div><a href='".cbSef("index.php?option=com_comprofiler&amp;task=moderateReports".$andItemid)."' class='mod_login".$class_sfx."'>".$totaluserreports." "._UE_USERREPORTSREQUIREACTION."</a></div>";
		if($totalimages > 0) $results .= "<div><a href='".cbSef("index.php?option=com_comprofiler&amp;task=moderateImages".$andItemid)."' class='mod_login".$class_sfx."'>".$totalimages." "._UE_IMAGESREQUIREACTION."</a></div>";
		if($totaluserpendapproval > 0 && $ueConfig['allowModUserApproval']) $results .= "<div><a href='".cbSef("index.php?option=com_comprofiler&amp;task=pendingApprovalUser".$andItemid)."' class='mod_login".$class_sfx."'>".$totaluserpendapproval." "._UE_USERPENDAPPRACTION."</a></div>";
	}
}
if($ueConfig['allowConnections']) {
	
	// $query = "SELECT count(*) FROM #__comprofiler_members WHERE pending=1 AND memberid=". $_CB_framework->myId();
	$query = "SELECT COUNT(*)"
	. "\n FROM #__comprofiler_members AS m"
	. "\n LEFT JOIN #__comprofiler AS c ON m.referenceid=c.id"
	. "\n LEFT JOIN #__users AS u ON m.referenceid=u.id"
	. "\n WHERE m.memberid=" . (int) $_CB_framework->myId() . " AND m.pending=1"
	. "\n AND c.approved=1 AND c.confirmed=1 AND c.banned=0 AND u.block=0"
	;
	$_CB_database->setQuery($query);
	$totalpendingconnections = $_CB_database->loadResult();
	if ( $totalpendingconnections === null ) trigger_error( $_CB_database->getErrorMsg(), E_USER_WARNING );
	if($totalpendingconnections > 0) {
		$results .= "<div><a href='".cbSef("index.php?option=com_comprofiler&amp;task=manageConnections".$andItemid)."' class='mod_login".$class_sfx."'>".$totalpendingconnections." "._UE_CONNECTIONREQUIREACTION."</a></div>";
	}	
}

if($results==null) {
	echo _UE_NOACTIONREQUIRED;
} else {
	if($pretext != "") echo "<div>".$pretext."</div>";
	echo $results;
	if($posttext != "") echo "<div>".$posttext."</div>";
}
?>
