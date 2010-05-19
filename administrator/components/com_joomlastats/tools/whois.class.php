<?php
/**
 * @version $Id: whois.class.php 000 2008-10-25 12:41:41Z mic $
 * @package JoomlaStats
 * @subpackage Tools Admin
 * @copyright Copyright (C) 2008 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL
 * @author mic [ http://www.joomx.com ]
 *
 * Note 1: if flags will be used, the have to b ein FRONT of the domainname, eg: -K www.xxx.yyyy.zzz
 * but NOT ALL flags are for all whois servers! (eg.g -K is NOT for arin, lacnic, phwois
 * denic.de see how to use flags: http://www.denic.de/de/domains/technik/denic_whois-server/index.html
 *
 * Note 2: this class is part of another package created by mic [http://www.joomx.com]
 * and should not be used without the explizit permission of the author
 */

if( !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}

$address_to_check = JRequest::getVar( 'address_to_check', '' );
$domainName = $address_to_check;

class whois
{
	// # internal vars
	// holder for debug mode - change if needed
	var $debug			= false;
	// returning back values
	var $retval			= '';
	//@private temporay found strings
	var $output			= '';
	//@private prevent to look further if search string is found
	var $found			= false;
	// this values are returned from the servers and indicating that the search was NOT succesful!
	//@private to this string the value of the server itself could be added [there are only common values, see specific at the servers
	var $blocked = '/RIPE-CIDR-BLOCK|TRANSFERRED TO|RIPE-213|EU \#/i'; // ERX-NETBLOCK|IANA-NETBLOCK-38
	// indicator for errors
	var $error			= false;
	// converted domainName into ip
	var $ip				= '';
	// is adress ip
	var $isip			= false;
	// type of address (ip,name,lookup)
	var $type			= '';
	// holder for internal errors
	var $errMsg			= '';

	//@private array of all whois servers
	var $whoisServer	= array();

	// ## generic server settings - can be overridden by each server itself ##
	// additional flags BEFORE the domain (e.g. -K domain.com or -T dn länder.de -> ONLY for .de domains!)
	var $preFlags	= '';
	// additional flags AFTER the domain  (e.g. domain.com -K)
	var $postFlags	= '';
	// standard server port
	var $port		= '43';
	// text to search for if domainname/ip.address not found
	var $notFound	= 'no match';
	// text to search for if not alloctaed at this server
	var $notAllocated	= 'not allocated';
	// values when found refuse the server response and dont add to output string
	var $refuse		= '/%|#|mnt-by|mnt-lower|remarks|<hr>|<hr \/>/i';
	// values which shall be shown
	var $accept		= array();
	// highlight special values
	var $highlight	= array();
	// prepared for later to use in select list
	var $checked		= false;
	// fsockopen connection time out
	var $conTimeOut	= 5;
	// socket time out (prepared for later for sock connection)
	var $sockTimeOut	= 30;
	// socket time out (prepared for later for sock connection)
	var $sockDelay	= 5;

	// official TLDs (first & second), see: http://en.wikipedia.org/wiki/Domain_name
	var $allowedTLD	= array( 'ac', 'ad', 'ae', 'af', 'ag', 'ai', 'al', 'am', 'an', 'ao', 'aq', 'ar', 'as', 'at', 'au', 'aw', 'ax', 'az', 'ba', 'bb', 'bd', 'be', 'bf', 'bg', 'bh', 'bi', 'bj', 'bm', 'bn', 'bo', 'br', 'bs', 'bt', 'bw', 'by', 'bz', 'ca', 'cc', 'cd', 'cf', 'cg', 'ch', 'ci', 'ck', 'cl', 'cm', 'cn', 'co', 'cr', 'cu', 'cv', 'cx', 'cy', 'cz', 'de', 'dj', 'dk', 'dm', 'do', 'dz', 'ec', 'ee', 'eg', 'er', 'es', 'et', 'eu', 'fi', 'fj', 'fk', 'fm', 'fo', 'fr', 'ga', 'gd', 'ge', 'gf', 'gg', 'gh', 'gi', 'gl', 'gm', 'gn', 'gp', 'gq', 'gr', 'gs', 'gt', 'gu', 'gw', 'gy', 'hk', 'hm', 'hn', 'hr', 'ht', 'hu', 'id', 'ie', 'il', 'im', 'in', 'io', 'iq', 'ir', 'is', 'it', 'je', 'jm', 'jo', 'jp', 'ke', 'kg', 'kh', 'ki', 'km', 'kn', 'kp', 'kr', 'kw', 'ky', 'kz', 'la', 'lb', 'lc', 'li', 'lk', 'lr', 'ls', 'lt', 'lu', 'lv', 'ly', 'ma', 'mc', 'md', 'me', 'mg', 'mh', 'mk', 'ml', 'mm', 'mn', 'mo', 'mp', 'mq', 'mr', 'ms', 'mt', 'mu', 'mv', 'mw', 'mx', 'my', 'mz', 'na', 'nc', 'ne', 'nf', 'ng', 'ni', 'nl', 'no', 'np', 'nr', 'nu', 'nz', 'om', 'pa', 'pe', 'pf', 'pg', 'ph', 'pk', 'pl', 'pn', 'pr', 'ps', 'pt', 'pw', 'py', 'qa', 're', 'ro', 'rs', 'ru', 'rw', 'sa', 'sb', 'sc', 'sd', 'se', 'sg', 'sh', 'si', 'sk', 'sl', 'sm', 'sn', 'sr', 'st', 'su', 'sv', 'sy', 'sz', 'tc', 'td', 'tf', 'tg', 'th', 'tj', 'tk', 'tl', 'tm', 'tn', 'to', 'tr', 'tt', 'tv', 'tw', 'tz', 'ua', 'ug', 'uk', 'us', 'uy', 'uz', 'va', 'vc', 've', 'vg', 'vi', 'vn', 'vu', 'wf', 'ws', 'ye', 'za', 'zm', 'zw', 'biz', 'com', 'info', 'name', 'net', 'org', 'pro', 'aero', 'asia', 'cat', 'coop', 'edu', 'gov', 'int', 'jobs', 'mil', 'mobi', 'museum', 'tel', 'travel', 'arpa' );

	function __construct() {

		/**
		 * whois server:
		 * ARIN		- America (USA):							http://www.arin.net
		 * - outside ARIN:
		 * AfriNIC 	- Africa: 									http://www.afrinic.net
		 * APNIC	- Asia Pacific:								http://www.apnic.net
		 * LACNIC	- Latin America and parts of Caribbean:		http://www.lacnic.net
		 * RIPE NCC	- Europe, Middel East, parts Central Asia:	http://www.ripe.net
		 */
		$this->whoisServer[0]['server']		= 'whois.ripe.net';
		$this->whoisServer[0]['allowed']	= array( 'ip' );
		$this->whoisServer[0]['notFound']	= 'error:101';
		$this->whoisServer[0]['checked']	= true;

		$this->whoisServer[1]['server']		= 'whois.apnic.net';
		$this->whoisServer[1]['allowed']	= array( 'ip', 'name' );
		$this->whoisServer[1]['notFound']	= 'error:101';

		$this->whoisServer[2]['server']		= 'whois.arin.net';
		$this->whoisServer[2]['allowed']	= array( 'ip', 'name' );
		$this->whoisServer[2]['notAllocated']	= 'allocated to';

		$this->whoisServer[3]['server']		= 'whois.lacnic.net';
		$this->whoisServer[3]['allowed']	= array( 'ip', 'name' );

		$this->whoisServer[4]['server']		= 'whois.afrinic.net';
		$this->whoisServer[4]['allowed']	= array( 'ip', 'name' );
		$this->whoisServer[4]['notFound']	= 'error:101';
		$this->whoisServer[4]['notAllocated']	= 'eu # Country';

		// pwhois.org is a very special if the previuos fails
		$this->whoisServer[5]['server']		= 'whois.pwhois.org';
		$this->whoisServer[5]['allowed']	= array( 'ip' );
		$this->whoisServer[5]['notFound']	= 'sorry, i don';

		// geektools.com is a very special if all previous fails
		$this->whoisServer[6]['server']		= 'whois.geektools.com';
		$this->whoisServer[6]['allowed']	= array( 'ip', 'name' );
		$this->whoisServer[6]['notFound']	= 'no information for that domain';

		$this->whoisServer[7]['server']		= 'whois.inname.net';
		$this->whoisServer[7]['allowed']	= array( 'name' );
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
	function whois()
	{
		$args = func_get_args();
		call_user_func_array(array(&$this, '__construct'), $args);
	}	

	/**
	 * checks if a specific server setting does exist, otherwise takes the generic (standard) setting
	 *
	 * @access private
	 * @param string $set	which value[setting] to check
	 * @return string
	 */
	function _checkValue( $set ) {
		if( isset( $this->value[$set] ) ) {
			return $this->value[$set];
		}else{
			return $this->$set;
		}
	}

	/**
	 * determines which type (ip, name, lookup) is the address/host
	 *
	 * @return string
	 */
	function _getType() {

		if( $this->debug ) {
			echo 'DEBUG _isIP host [' . $this->host . ']<br />';
		}

		$this->isip = false;

		if( preg_match( '/[a-z]|[A-Z]|ä|ö|ü|Ä|Ö|Ü|-/', $this->host ) ) {
			if( !preg_match( '/[a-z]|[A-Z]|ä|ö|ü|Ä|Ö|Ü|-/', $this->host ) ) {

				if( $this->debug ) {
					echo 'DEBUG host is IP<br />';
				}

				$this->type = 'ip';
				$this->isip = true;
			}else{
				if( !preg_match( '/[0-9]/', $this->host ) ) {

					if( $this->debug ) {
						echo 'DEBUG host is NAME<br />';
					}

					$this->type = 'name';
				}else{

					if( $this->debug ) {
						echo 'DEBUG host is LOOKUP<br />';
					}

					$this->type = 'lookup';
				}
			}
		}

		return $this->isip;
	}

	/**
	 * checks if domain is a number (ip.address v4)
	 * converts it if NOT an ip.address
	 *
	 * @access private
	 */
	function _name2ip() {

		switch( $this->type ) {
			case 'ip':
				$this->ip = $this->host;
				$this->domain = gethostbyaddr( $this->ip );

				if( $this->debug ) {
					echo 'DEBUG case "ip" - assigned host to ip - performed gethostbyaddr<br />';
				}

				break;

			case 'name':
				if( $this->debug ) {
					echo 'DEBUG case "name" - gethostbyname performed<br />';
				}

			case 'lookup':
				$this->ip = gethostbyname( $this->host );
				$this->isip = true;
				$this->host = $this->ip;

				if( $this->debug ) {
					echo 'DEBUG case "lookup" - action gethostbyname performed<br />'
					. 'ip is [' . $this->ip . ']<br />';
				}

				break;
		}
	}

	/**
	 * adds pre & post flags to the domainname
	 *
	 * @access private
	 */
	function _addFlags() {
		$this->host = ( $this->_checkValue( 'preFlags' ) ? $this->_checkValue( 'preFlags' ) . ' ' : '' )
		. $this->host
		. ( $this->_checkValue( 'postFlags' ) ? ' ' . $this->_checkValue( 'postFlags' ) : '' );
	}

	function _getTLD() {

		if( $this->debug ) {
			echo 'DEBUG _getTLD type [' . $this->type . ']<br />';
		}

		if( $this->type == 'ip' ) {
			return;
		}

		if( $this->debug ) {
			echo 'DEBUG _getTLD host [' . $this->host . ']<br />';
		}

		$this->sld	= '';
		$this->tld	= '';
		$pos		= strrpos( $this->host, '.' );

		if( $pos != -1 ) {
			$this->sld = substr( $this->host, 0, $pos );
			$this->tld = substr( $this->host, $pos + 1 );
		}else{
			$this->tld = $this->host;
		}

		// check again if we have a subdomain
		$pos		= strrpos( $this->sld, '.' );
		if( $pos ) {

			if( $this->debug ) {
				echo 'DEBUG - seems to be a SUBdomain -> sld [' . $this->sld . ']<br />';
			}

			$this->sld = substr( $this->sld, $pos + 1 );
		}

		if( $this->debug ) {
			echo 'DEBUG - sld after cleaning [' . $this->sld . ']<br />';
		}

		// we want only TLDs no subdomains!
		//$this->tld = str_replace( '.', '', $this->tld );

		if( $this->debug ) {
			echo 'DEBUG final - sld [' . $this->sld . ']<br />'
			. 'tld [' . $this->tld . ']<br />';
		}

		$this->host = $this->sld . '.' . $this->tld;
		$this->domain = $this->host;
	}

	/**
	 * checks a given domainname for validity
	 *
	 * @param string $domainName
	 */
	function _isValid() {

		$this->_getType();
		if( $this->type != 'name' ) {
			$this->domain = $this->host;
			return;
		}

		if( $this->debug ) {
			echo 'DEBUG _isValid - type [' . $this->type . ']<br />';
		}

		// do some checks and eleminate unwanted strings
		if ( function_exists('mb_strtolower') )
			$this->host = mb_strtolower( $this->host, 'UTF-8' );
		else
			$this->host = strtolower( $this->host );

		if( $this->type != 'name' ) {
			$this->host = $this->_cleanString( $this->host );
		}

		$this->host = str_ireplace( 'http://', '', $this->host );
		$this->host = str_ireplace( 'www.', '', $this->host );

		$this->domain	= $this->host;

		$this->_getTLD();
		$this->tld = $this->_cleanString( $this->tld );
		$this->sld = $this->_cleanString( $this->sld );

		if( $this->sld == '' ) {
			$this->errMsg = 'You must enter a domain to be checked';
		}

		if( strlen( $this->sld ) < 3 ) {
			$this->errMsg = 'The domain name [' . $this->sld . '] is too short';
		}

		if( strlen( $this->sld ) > 57 ) {
			$this->errMsg = 'The domain name [' . $this->sld . '] is too long';
		}

		if( @ereg( "^-|-$", $this->sld ) ) {
			$this->errMsg = 'Domains cannot begin or end with a hyphen';
		}

		if( !preg_match( '/[a-z]|[A-Z]|[0-9]|ä|ö|ü|Ä|Ö|Ü|-/', $this->sld ) ) {
			$this->errMsg = 'Domain names cannot contain special characters';
		}

		if( !in_array( $this->tld, $this->allowedTLD ) ) {
			$this->errMsg = 'Sorry, but this domain extension [' . $this->tld . '] is not allowed!';
		}

		if( $this->errMsg ) {
			$this->errMsg = '<div class="error">' . $this->errMsg . '</div>';
		}
	}

	/**
	 * cleans a given var
	 * 1. before and after
	 * 2. inside the var
	 *
	 * @param unknown_type $var
	 * @return unknown
	 */
	function _cleanString( $var ) {

		// delete all from ascii 0 until 32 (space) at begin and end
		$var = trim( $var, "\x00..\x20" );
		// delete all unwanted at begin and and
		$var = trim( $var, '.:;^°[]{}´`,-_<>|/\\!"§$%&()=?*+#\'' );
		// delete unwanted inside
		$allowed = '/[^a-z0-9\\.\\-\\_\ä\ö\ü]/i';
  		$var = preg_replace( $allowed, '', $var );

		return $var;
	}

	function _checkOutput() {

		// reset value
		$this->found = false;

	    if( preg_match( $this->blocked, $this->output ) ) {
	    	if( $this->debug ) {
				echo '<br /><strong style="color:red">DEBUG: ['
				. $this->value['server']
				. '] result does NOT contain search key</strong><br />';
	    	}
	    	$this->error = true;

	    }elseif( strpos( $this->output, 'RIPE Database Reference Manual' ) !== false ) {
	    	$this->message = '<div class="serverError">'
	    	. '[1] ' . JTEXT::_( 'There was an error in the query - please contact the developer' )
	    	. '</div>' . "\n";
	    	$this->error = true;

	    }elseif( preg_match( '/error/i', strtolower( $this->output ) ) ) {
			$this->message = '<div class="serverError">'
			. '[2] ' . JTEXT::_( 'There was an error in the query - please contact the developer' )
	    	. '</div>' . "\n";
			$this->error = true;

	    }elseif( strpos( strtolower( $this->output ), $this->_checkValue( 'notFound' ) ) !== false ) {
	    	$this->message .= '<div class="noMatch">'
	    	. JTEXT::sprintf( 'No match for the requested domain <strong>%s</strong>', $this->host )
	    	. '</div>' . "\n";
	    	$this->found = false;

	    }elseif( strpos( strtolower( $this->output ), $this->_checkValue( 'notAllocated' ) ) !== false ) {
	    	$this->message .= '<div class="noMatch">'
	    	. JTEXT::sprintf( 'Requested domain <strong>%s</strong> not allocated at this server', $this->host )
	    	. '</div>' . "\n";

		}else{
			$this->found = true;
			$this->error = false;
		}

		return $this->found;
	}

	/**
	 * accessing the whois.servers and output plain what is recieved before
	 * this access is only for a SINGLE domain name! and use fsockopen
	 *
	 * @param string	$domainName		domainname or ip.address
	 * @return string
	 */
	function whoisSock( $domainName ) {

		if( $this->debug ) {
			echo 'DEBUG domainName [' . $domainName . ']<br />';
		}

		$this->host = $domainName;
		$this->_isValid();
		$this->_name2ip();

		if( !$this->errMsg ) {
		    foreach( $this->whoisServer as $this->value ) {

		    	if( $this->debug ) {
		    		echo 'DEBUG server values:<br />';
			    	print_r( $this->value );
			    	echo '<hr />';
		    	}

		    	if( $this->found == false ) {
		    		$ip = ( ( $this->domain != $this->ip ) ? ( $this->ip ? $this->ip : '' ) : '' );

		    		$this->message = '<div class="serverAccess">'
		    		. JTEXT::sprintf( 'Accessing Server [%s] for domain [%s] ip [%s]', $this->value['server'], $this->domain, $ip )
		    		. '</div>' . "\n";

		    		if( $this->debug ) {
		    			echo 'DEBUG info:<br />' . $this->message . '<br />';
		    		}

		    		$this->_addFlags();

				    $connection = fsockopen(
				    	$this->value['server'],
				    	$this->_checkValue( 'port' ),
				    	$errno,
				    	$errstr,
				    	$this->_checkValue( 'conTimeOut' )
				    );

				    if( !$connection ) {
				        $this->output = '<div class="Access">'
				        . JTEXT::sprintf( 'Sorry, could not connect to the server [%s]. Please try again later', $this->value['server'] )
				        . '</div>' . "\n";
				        $this->error = true;
				    }else{
						//send query to server
				        fwrite( $connection, $this->host . "\r\n" );

				        //catch server reply
				        while( !feof( $connection ) ) {
				            $this->output .= fgets( $connection );
				        }
				        fclose( $connection );
				    }

				    if( $this->debug ) {
					    echo '<br />DEBUG output raw WHOIS result:<br />' . $this->output;
					    echo '<hr style="width:80%" />';
				    }
		    	}

		    	// empty temporary output if searched item not found
				if( !$this->_checkOutput( $this->output ) ) {
					$this->output = '';
				}
		    }

		    $this->retval = str_replace( "\n", "<br />\n", $this->output ) . '<hr />';

		    // test mic: trying to strip vars we dont need
		    $lines = explode( "\n", $this->retval );

		    // strip non relevant infos - maybe as feature by JS-Config ?
		    $showMe = '';
		    foreach( $lines as $line ) {
				$matcher = '';
				$matcher = explode( ':', $line );

				if( !preg_match( $this->refuse, $matcher[0] ) ) {
					$css = ( isset( $matcher[1] ) ? 'var' : 'vartext1' );

					$showMe .= '<div class="' . $css . '">'
					. (
						( ( ereg( '([a-z]|[A-Z]|[0-9])', $matcher[0] ) )
							? trim( $matcher[0] . ( isset( $matcher[1] ) ? ': ' : '' ) )
							: '<br />'
						)
						. '</div>'
					)
					. ( ( isset( $matcher[1] ) && ereg( '([a-z]|[A-Z]|[0-9])', $matcher[1] ) )
						? '<div class="vartext">' . trim( $matcher[1] ) . '</div>' . "\n"
						: ''
					)
					. '<div class="clear"></div>' . "\n";
				}
		    }
	    	$this->retval = $this->message . $showMe;
		}else{
			$this->retval = $this->errMsg;
		}

		return $this->retval;
	}
}