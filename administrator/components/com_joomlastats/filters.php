<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */


if( !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}

require_once( dirname(__FILE__) .DS. 'base.classes.php' );



	
/**
 *	This file contain filters that are used in joomla backend to change searching criteria in statistics
 */


 

 
 
 /**
 *	This class makes working with time period filter easy.
 *	It can generate HTML code, create SQL query, read values from request.
 *
 *  THIS CLASS DOES NOT WORK UNDER PHP4
 *
 *
 *  code of THIS CLASS IS STRONLY DEPRECATED, even js_JSFilterTimePeriod is newer!
 */
class js_JSFilterDate
{
	var $year;
	var $month;
	var $day;
	
	var $prefix = '';
	var $sufix  = '';
	
	var $year_min = 2003;
	var $year_max = 2010;//will be overriden in constructor //new year appears in the last 2 weeks of current year
	
	/** we need sufix in case when we create list of date filters (eg. date to each row from sql query) */
	function js_JSFilterDate( $prefix='', $sufix='' ) {
		$after2weeks = mktime(0, 0, 0, date('m'), date('d')+14, date('Y')); //new year appears in the last 2 weeks of current year
		$this->year_max = date( 'Y', $after2weeks ); //we do not use js_date() for performance (it does not matter in this case)
		
		$this->prefix = $prefix;
		$this->sufix  = $sufix;

		$this->setDefaultDate();
	}

	/** set default values for this class */
	function setDefaultDate() {
	}

	function readDateFromRequest( $alternate_year='', $alternate_month='', $alternate_day='') {
		global $mainframe;

		if (strlen($alternate_year) == 0)
			$alternate_year = js_gmdate('Y');
		
		if (strlen($alternate_month) == 0)
			$alternate_month = js_gmdate('n');
			
		if (strlen($alternate_day) == 0)
			$alternate_day = js_gmdate('j');
			
		$this->year  = $mainframe->getUserStateFromRequest( 'year',  'year',  $alternate_year );
		$this->month = $mainframe->getUserStateFromRequest( 'month', 'month', $alternate_month );
		$this->day   = $mainframe->getUserStateFromRequest( 'day',   'day',   $alternate_day );
	}

	function getDateStr() {
		return $this->year .'-'. ((strlen($this->month)==1) ? '0' : '') . $this->month .'-'. ((strlen($this->day)==1) ? '0' : '') . $this->day;
	}
	

	/**
	 * Create the Day dropdown
	 *
	 * @access private
	 * @return string
	 */
	function CreateDayCmb() {

		$html = '';

		for( $i = 1; $i <= 31; $i++ ) {
			$html .= '<option value="' . $i . '"';
			if( $this->day == $i ) {
				$html .= ' selected="selected"';
			}
			$html .= '>' . $i . '</option>' . "\n";
		}

		return $html;
	}

	/**
	 * Creates the dropdown for months
	 *
	 * @access private
	 * @return string
	 */
	function CreateMonthCmb() {
		require_once( dirname(__FILE__) .DS. 'template.html.php' );

		$html = '';
		
		$JSUtil = new js_JSUtil();
		$JSTemplate = new js_JSTemplate();
		
		for( $i=1; $i<13; $i++ ) {
			$html .= '<option value="' . $i . '"';
			if( $this->month == $i ) {
				$html .= ' selected="selected"';
			}
			$html .= '>' . $JSTemplate->monthToString($i, true) . '</option>' . "\n";
		}

		return $html;
	}

	/**
	 * Creates the year drop down
	 *
	 * @access private
	 * @return string
	 */
	function CreateYearCmb() {

		$html		= '';

		for( $i = $this->year_min; $i <= $this->year_max; $i++ ) {
			$html .= '<option value="' . $i . '"';
			if( $this->year == $i ) {
				$html .= ' selected="selected"';
			}
			$html .= '>' . $i . '</option>' . "\n";
		}

		return $html;
	}

	/** $date: '2008-06-19' */
	function SetYMD( $date='now' ) {

		$data_arr = explode('-', $date);
		if (count($data_arr) == 3) {
			$this->year  = $data_arr[0];
			$this->month = $data_arr[1];
			$this->day   = $data_arr[2];
		} else {
			$JSNowTimeStamp = js_getJSNowTimeStamp();
			$this->year  = js_gmdate( 'Y', $JSNowTimeStamp );
			$this->month = js_gmdate( 'n', $JSNowTimeStamp );
			$this->day   = js_gmdate( 'j', $JSNowTimeStamp );
		}
	}

	/**
	 * creates a javascript and dropdowns for date selection
	 *
	 * @since 2.3.x: if all months are selected, also all days will be checked
	 * @return string
	 */
	function getHtmlDateFilterCode() {

		$html  = '';

		$html .= '<select name="day">' . $this->CreateDayCmb() . '</select>';//<!-- combo day here -->
		$html .= '&nbsp;';
		$html .= '<select name="month">' . $this->CreateMonthCmb() . '</select>';//<!-- combo month here -->
		$html .= '&nbsp;';
		$html .= '<select name="year">' . $this->CreateYearCmb() . '</select>';//<!-- combo year here -->

		return $html;
	}
}

 
 

 
 
 

  
  
/**
 *	This class makes working with time period filter easy.
 *	It can generate HTML code, create SQL query, read values from request.
 *
 *	NOTICE: Calendar should be added to this class.
 */
class js_JSFilterTimePeriod
{
	/** default values are set in constuctor (because it is date aligned with JSNowTimeStamp */
	var $d;
	var $m;
	var $y;

	function __construct() {
		$this->setDefaultTimePeriod();
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
	function js_JSFilterTimePeriod()
	{
		$args = func_get_args();
		call_user_func_array(array(&$this, '__construct'), $args);
	}	

	/** set default values for this class */
	function setDefaultTimePeriod() {
		$this->setDMY2Now();
	}

	/* This function read values from request. If values are not set in request they are stay unchnged. This is for purpose.
	 * Default values are set in constructor or by calling setDefault*() function */
	function readTimePeriodFromRequest( $startdayormonth ) {

		// new mic: security
		$d = JRequest::getVar( 'd', '' );
		$m = JRequest::getVar( 'm', '' );
		$y = JRequest::getVar( 'y', '' );

		if ($d != '') {
			$this->d = $d;
		} else {
			if( $startdayormonth == 'm' )
				$this->d = 'all';
		}

		if ($m != '')
			$this->m = $m;

		if ($y != '')
			$this->y = $y;
	}

	
	/**
	 * Returns selected values. '%' char is returned when user selcect 'all' option
	 *
	 * @access public
	 * @return boolean  false when user select 'all' at least once. If true is returned user select one particular day
	 */
	function getDMY( &$day, &$month, &$year ) {
		$bResult = true;
		
		$day = $this->d;
		if( $this->d == 'all' ) {
			$day = '%';
			$bResult = false;
		}
		
		$month = $this->m;
		if( $this->m == 'all' ) {
			$month = '%';
			$bResult = false;
		}
		
		$year = $this->y;
		if( $this->y == 'all' ) {
			$year = '%';
			$bResult = false;
		}
		
		return $bResult;
	}

	function getTimePeriodsDates( &$date_from, &$date_to ) {
		$day_from = $this->d;
		$day_to = $this->d;
		if( $this->d == 'all' ) {
			$day_from = '1';
			$day_to = '31';
		}
		$month_from = $this->m;
		$month_to = $this->m;
		if( $this->m == 'all' ) {
			$month_from = '1';
			$month_to = '12';
		}
		$year_from = $this->y;
		$year_to = $this->y;
		if( $this->y == 'all' ) {
			$date_from = '';
			$date_to = '';
			return;
		}

		$date_from = $year_from.'-'.$month_from.'-'.$day_from;
		$date_to = $year_to.'-'.$month_to.'-'.$day_to;
	}
	
	/**
	 * Create the Day dropdown
	 *
	 * @access private
	 * @return string
	 */
	function CreateDayCmb() {

		$html = '';

		$html .= '<option value="all"';
		if( $this->d == 'all' )
			 $html .= ' selected="selected"';
		$html .= '>' . JTEXT::_( 'All' ) . '</option>' . "\n";

		for( $i = 1; $i <= 31; $i++ ) {
			$html .= '<option value="' . $i . '"';
			if( $this->d == $i ) {
				$html .= ' selected="selected"';
			}
			$html .= '>' . $i . '</option>' . "\n";
		}

		return $html;
	}

	/**
	 * Creates the dropdown for months
	 *
	 * @access private
	 * @return string
	 */
	function CreateMonthCmb() {
		require_once( dirname(__FILE__) .DS. 'template.html.php' );

		$html = '';
		
		$JSUtil = new js_JSUtil();
		$JSTemplate = new js_JSTemplate();

		$html .= '<option value="all"';
		if( $this->m == 'all' )
			 $html .= ' selected="selected"';
		$html .= '>' . JTEXT::_( 'All' ) . '</option>' . "\n";
		
		for( $i=1; $i<13; $i++ ) {
			$html .= '<option value="' . $i . '"';
			if( $this->m == $i ) {
				$html .= ' selected="selected"';
			}
			$html .= '>' . $JSTemplate->monthToString($i, true) . '</option>' . "\n";
		}

		return $html;
	}

	/**
	 * Creates the year drop down
	 *
	 * @access private
	 * @return string
	 */
	function CreateYearCmb() {

		$html		= '';
		$date_min	= 2003;
		$after2weeks = mktime(0, 0, 0, date('m'), date('d')+14, date('Y')); //new year appears in the last 2 weeks of current year
		$date_max = date( 'Y', $after2weeks ); //we do not use js_date() for performance (it does not matter in this case)

		$html .= '<option value="all"';
		if( $this->y == 'all' )
			 $html .= ' selected="selected"';
		$html .= '>' . JTEXT::_( 'All' ) . '</option>' . "\n";

		for( $i = $date_min; $i <= $date_max; $i++ ) {
			$html .= '<option value="' . $i . '"';
			if( $this->y == $i ) {
				$html .= ' selected="selected"';
			}
			$html .= '>' . $i . '</option>' . "\n";
		}

		return $html;
	}

	/**
	 * Function to set $this->d; $this->m; $this->y values to now (to JSNowTimeStamp)
	 */
	function setDMY2Now() {
		$JSNowTimeStamp = js_getJSNowTimeStamp();
		$this->setDMYFromJSTimeStamp($JSNowTimeStamp);
	}

	/**
	 * Function to set $this->d; $this->m; $this->y values to $JSNowTimeStamp
	 *
	 * $JSNowTimeStamp is timestamp with appropriate offset. See function js_getJSNowTimeStamp() for details.
	 */
	function setDMYFromJSTimeStamp($JSNowTimeStamp) {
		$this->d = js_gmdate( 'j', $JSNowTimeStamp );
		$this->m = js_gmdate( 'n', $JSNowTimeStamp );
		$this->y = js_gmdate( 'Y', $JSNowTimeStamp );
	}

	/**
	 * creates a javascript and dropdowns for date selection
	 *
	 * @todo mic: javascript should be outside into the header and NOT direct in the code
	 * @AT: of course not - on page could be 2 date filters
	 *
	 * @return string
	 */
	function getHtmlDateFilterCode() {

		$html  = '';

		$html .= '
			<script type="text/javascript">
				/* <![CDATA[ */
				function SelectDay(Value) {
					for (index=0; index<document.adminForm.d.length; index++) {
						/* walk the list */
						if (document.adminForm.d[index].value == Value) {
							/* if the day is the day we want to select */
							document.adminForm.d.selectedIndex = index;
							/* then mark it selected */
						}
					}
				};

				function onDChange() {
					if (document.adminForm.d.value == "all") {
					} else {
						if (document.adminForm.m.value == "all")
							document.adminForm.m[1].selected = true;
						if (document.adminForm.y.value == "all")
							document.adminForm.y[1].selected = true;
					}
				};
				
				function onMChange() {
					if (document.adminForm.m.value == "all") {
						document.adminForm.d.value = "all";
					} else {
						if (document.adminForm.y.value == "all")
							document.adminForm.y[1].selected = true;
					}
				};
				
				function onYChange() {
					if (document.adminForm.y.value == "all") {
						document.adminForm.d.value = "all";
						document.adminForm.m.value = "all";
					} else {
					}
				};
				
				/* ]]> */
			</script>
		';

		$html .= ''
		. '<select name="d" onChange="onDChange();">' . $this->CreateDayCmb() . '</select>' 
		. '&nbsp;&nbsp;'
		. '<select name="m" onChange="onMChange();">' . $this->CreateMonthCmb() . '</select>'
		. '&nbsp;&nbsp;'
		. '<select name="y" onChange="onYChange();">' . $this->CreateYearCmb() . '</select>'
		. '&nbsp;&nbsp;'
		. '<input type="submit" name="Submit" id="Submit" value="'.JTEXT::_('Go').'" /> '
		;

		return $html;
	}
}




/**
 *	This class makes working with domain filter easy.
 *	It can generate HTML code, create SQL query, read values from request.
 */
class js_JSFilterDomain
{
	/** This membes hold user entered (user selected) string
	 *	This string is used when database is queried
	 *	@access private */
	var $_domain_string = '';
	
	/**
	 * This membes hold hint that is displayed on search mouse over action
	 * eg. 'Domain (google.com/.eu/.com)'
	 * @access private
	 */
	var $_domain_hint = '';
	

	/**
	 * This membes decide when domain filter should be shown. Set it to 'true' if You want have domain filter visible
	 *	@access public
	 */
	var $show_domain_filter = true;
	
	/** set default values for this class */
	function setDefaultDomain() {
		$def = new js_JSFilterDomain();
		$this->_domain_string     = $def->_domain_string;
		$this->_domain_hint       = $def->_domain_hint;
		$this->show_domain_filter = $def->show_domain_filter;
	}

	/** gets var from request string */
	function readDomainStringFromRequest() {
		$this->_domain_string = JRequest::getVar( 'dom' );
	}

	/** return 'Domain String' */
	function getDomainString() {
		return $this->_domain_string;
	}
	
	/**
	 * Set hint that is displayed on search mouse over action
	 * eg. 'Domain (google.com/.eu/.com)'
	 * @param string
	 */
	function setDomainHint( $domain_hint ) {
		$this->_domain_hint = $domain_hint;
	}
	
	/**
	 * builds a hidden field holding the search item
	 *
	 * @return string
	 */
	function getHtmlDomainFilterHiddenCode() {
		if ($this->show_domain_filter == false)
			return '<input type="hidden" name="dom" id="dom" value="' . $this->_domain_string . '" />';
		else
			return '';
	}
	
	/**
	 * builds an input field for search
	 *
	 * @return string
	 */
	function getHtmlDomainFilterVisibleCode() {

		if ($this->show_domain_filter == true) {
			$hint = ( $this->_domain_hint == '' ) ? '' : ( ' title="' . $this->_domain_hint . '"' );
	
			$html  = JTEXT::_( 'Domain' )
			. ':&nbsp;'
			. '<input type="text" name="dom" id="dom" value="' . $this->_domain_string . '"'
			. ' class="text_area" onChange="document.adminForm.limitstart.value=0;document.adminForm.submit();"' . $hint . ' />';
			
			return $html;
		} else {
			return '';
		}
	}
}




/**
 *	This class makes working with search filter easy.
 *	It can generate HTML code, create SQL query, read values from request.
 */
class js_JSFilterSearch
{
	/**
	 * This membes hold user entered sting to search input
	 * This string is used when database is queried
	 * @access private
	 */
	var $_search_string = '';

	/**
	 * This membes hold hint that is displayed on search mouse over action
	 * eg. 'Search (IP/TLD/NS-Lookup/OS)'
	 * @access private
	 */
	var $_search_hint = '';

	/**
	 * This membes decide when search filter should be shown. Set it to 'true' if You want have search filter visible
	 *	@access public
	 */
	var $show_search_filter = true;

	function setDefaultDomain() {
		$def = new js_JSFilterSearch();
		$this->_search_string     = $def->_search_string;
		$this->_search_hint       = $def->_search_hint;
		$this->show_search_filter = $def->show_search_filter;
	}
	
	function readSearchStringFromRequest() {
		global $mainframe;
		global $option;

		$this->_search_string = $mainframe->getUserStateFromRequest("search{$option}", 'search', '');
	}

	/** return 'Search String' */
	function getSearchString() {
		return $this->_search_string;
	}

	/**
	 * Set hint that is displayed on search mouse over action
	 * eg. 'Search (IP/TLD/NS-Lookup/OS)'
	 * @param string
	 */
	function setSearchHint( $search_hint ) {
		$this->_search_hint = $search_hint;
	}

	/**
	 * builds an input field for search
	 *
	 * @return string
	 */
	function getHtmlSearchFilterVisibleCode() {

		if ($this->show_search_filter == true) {
			$hint = ( $this->_search_hint == '' ) ? '' : ( ' title="' . $this->_search_hint . '"' );
	
			$html  = JTEXT::_( 'Search' )
			. ':&nbsp;'
			. '<input type="text" name="search" id="search" value="' . $this->_search_string . '"'
			. ' class="text_area" onChange="document.adminForm.limitstart.value=0;document.adminForm.submit();"' . $hint . ' />';
			
			return $html;
		} else {
			return '';
		}
	}

	/**
	 * builds a hidden field holding the search item
	 *
	 * @return string
	 */
	function getHtmlSearchFilterHiddenCode() {
		if ($this->show_search_filter == false)
			return '<input type="hidden" name="search" id="search" value="' . $this->_search_string . '" />';
		else
			return '';
	}
}
	