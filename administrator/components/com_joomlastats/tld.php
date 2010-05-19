<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

if( !defined( '_VALID_MOS' )  && !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}

//if( !defined( '_JEXEC' ) ) {
//	die( 'JS: No Direct Access' );
//}


require_once( dirname( __FILE__ ) .DIRECTORY_SEPARATOR. 'tld.html.php' );


/**
 *  This class contain set of static functions that allow perform actions connected with TLD.
 *
 *  NOTICE: This class should contain only set of static, argument less functions that are called by task/action
 */
class js_JSTld
{
	/**
	 * Get content of TLD tab
	 *
	 * @access static, public
	 * @return HTML code
	 */
	function getTldTab() {
		
		$ip_tld_info = JRequest::getVar( 'ip_tld_info', '' );
		
		$JSTldTpl = new js_JSTldTpl();
		$html = $JSTldTpl->getTldTabTpl($ip_tld_info);
		return $html;
	}

	
	
	/**
	 * 
	 * 
	 *
	 * 
	 *
	 * @access public
	 */
	function getTldFromJSTable( $ip_to_check, &$tld_id, &$tld_str ) {
		global $mainframe;
						if( $tld === '' || $tld === 'eu' || strlen( $tld ) > 2 ) {
							$query = 'SELECT country_code2'
							. ' FROM #__jstats_iptocountry'
							. ' WHERE INET_ATON(\'' . $row->ip . '\') >= ip_from'
							. ' AND INET_ATON(\'' . $row->ip . '\') <= ip_to'
							;
							$JSDatabaseAccess->db->setQuery( $query );
							$country_code2 = $JSDatabaseAccess->db->loadResult();

							if( $country_code2 ) {
								$tld = strtolower( $country_code2 );
							}
						}
	
		return false;	
	}

		
	/**
	 * 
	 * 
	 *
	 * 
	 *
	 * @access private
	 */
	function updateTldInVisitorTable( $ip_to_update, $tld_id, $tld_str ) {
		$query = 'UPDATE #__jstats_ipaddresses'
		. ' SET tld = \''.$tld_str.'\''
		. ' WHERE ip = \'' . $row->ip . '\''
		;
		$JSDatabaseAccess->db->setQuery( $query );
		$JSDatabaseAccess->db->query();
		if( $JSDatabaseAccess->db->getErrorNum()) {
			return false;
		}
	
		return true;	
	}
	

	/**
	 *  This function try to find nslookups for all addresses that prevously nslookup fail
	 *
	 *  task: js_do_resolve_all_unknown_nslookups
	 *
	 *
	 *  This function call itself many times up to it try to solve all nslookups
	 */
	function doResolveAllUnknownNslookups() {
		
		//			$nslp	= gethostbyaddr( $ip_to_check);$ip_to_check
		 //, nslookup AS visitor_nslookup'

		
		return false;
	}
		
	/**
	 *  This function try to find TLD for all addresses that prevously were marked as unknown
	 *
	 *  task: js_do_resolve_all_unknown_tlds
	 *
	 *
	 *  This function call itself many times up to it try to solve all unknown addresses
	 */
	function doResolveAllUnknownTlds() {
		global $mainframe;
		
		$bResult = true;

		$start = (int)JRequest::getVar( 'start', 0 );
		$limit = (int)JRequest::getVar( 'limit', 30 );
		
		
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'access.php' );
		$JSDatabaseAccess = new js_JSDatabaseAccess();

		
		// get the list of all unresolved tlds ipaddresses
		$query = 'SELECT ip AS visitor_ip'
		. ' FROM #__jstats_ipaddresses'
		. ' WHERE'
		. ' tld=\'\' OR tld=\'unknown\'' 
		. ' GROUP BY ip'
		. ' LIMIT '.$start.', '.$limit
		;
		$JSDatabaseAccess->db->setQuery( $query );
		$rows = $JSDatabaseAccess->db->loadObjectList();
		if( $JSDatabaseAccess->db->getErrorNum()) {
			return false;
		}
		
		if (!$rows) {
			$msg = JTEXT::_( 'TLD-Lookup finished - no addresses to process' );

			if( isJ15() )
				$mainframe->redirect( 'index.php?option=com_joomlastats&task=js_view_tools', $msg );
			else
				mosRedirect( 'index2.php?option=com_joomlastats&task=js_view_tools', $msg );

			return true;			
		}
		
		$total = count( $rows );
		
		if ($total == 0) {
			$msg = JTEXT::_( 'TLD-Lookup finished - no addresses to process' );

			if( isJ15() )
				$mainframe->redirect( 'index.php?option=com_joomlastats&task=js_view_tools', $msg );
			else
				mosRedirect( 'index2.php?option=com_joomlastats&task=js_view_tools', $msg );

			return true;			
		}
		
		js_echoJSDebugInfo( 'Total nbr of unknowns TLDs: ', $total);

		
		foreach ($rows as $row) {
			$ip_to_check = $row->visitor_ip;
			
			if (isLocal($ip_to_check) == true) {
				js_echoJSDebugInfo( 'That IP is local address. There is no sense and ability to check TLD for it: ', $ip_to_check );
				continue;
			}
			
			$tld_id = 0;
			$tld_str = '';
			$tld_found_in_js_table = $this->getTldFromJSTable( $ip_to_check, $tld_id, $tld_str );
			
			if ($tld_found_in_js_table == true) {
				$bResult &= $this->updateTldInVisitorTable( $ip_to_check, $tld_id, $tld_str );
				continue;
			}
			
			$tld_str = '';
			$tld_found_in_ripe_server = getTldFromRipeServer( $ip_to_check, $tld_str );
			if ( $tld_found_in_ripe_server == true ) {
### MISSING CODE HERE
js_echoJSDebugInfo( 'MISSING CODE HERE', '' );
				$bResult &= $this->updateTldInVisitorTable( $ip_to_check, $tld_id, $tld_str );
				continue;
			}
			
			js_echoJSDebugInfo( 'Could not find TLD name for this IP: ', $ip_to_check );
		}
		
		
		if ($total == $limit) {
			$processed = $start + $limit;
			$msg = JTEXT::sprint( 'Processed %s and still continue...', $processed );
		
			if( isJ15() ) {
				$mainframe->redirect( 'index.php?option=com_joomlastats&task=js_view_tools&start='.$processed.'&limit='.$limit, $msg );
			}else{
				mosRedirect( 'index2.php?option=com_joomlastats&task=js_view_tools&start='.$processed.'&limit='.$limit, $msg );
			}
		}
		
		
		$msg = JTEXT::_( 'TLD-Lookup finished - no addresses to process' );
	
		if( isJ15() ) {
			$mainframe->redirect( 'index.php?option=com_joomlastats&task=js_view_tools', $msg );
		}else{
			mosRedirect( 'index2.php?option=com_joomlastats&task=js_view_tools', $msg );
		}
	}
	
}
