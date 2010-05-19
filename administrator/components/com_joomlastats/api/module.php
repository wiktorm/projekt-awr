<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */



if( !defined( '_JS_STAND_ALONE' ) && !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}





/**
 *  This class contain API (application programming interface) to JoomlaStats specially for modules.
 *
 *  Eg. of including JoomlaStats API
 * 	  require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'joomla' .DIRECTORY_SEPARATOR. 'administrator' .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_joomlastats' .DIRECTORY_SEPARATOR. 'api' .DIRECTORY_SEPARATOR. 'module.php' );
 *
 *
 *  All methods are static
 */
class js_JSApiModule
{


	/** 
	 *  This function convert time period string from *.xml to SQL WHERE condition (in optimized way)
	 *
	 *
	 *  Below code for *.xml file:
	 *
        <param name="list_time_period"        type="list"     default="total"              label="List time period"         description="Choose time period to show country statistics for.&lt;br/>&lt;br/>eg.&lt;br/>&lt;b>For last week&lt;/b> - shows statistics for last week. If You have approximately constant number of visitors, value will be more or less constant.&lt;br/>&lt;b>This week&lt;/b> - shows statistics for current week. Monday is first day of week. If You have approximately constant number of visitors, value for 'This week' time period will change from 0 up to 'For last week'.">
            <option value="for_last_d-h-m_0-0-5">For last 5 minutes</option>
            <option value="for_last_d-h-m_0-0-10">For last 10 minutes</option>
            <option value="for_last_d-h-m_0-0-15">For last 15 minutes</option>
            <option value="for_last_d-h-m_0-0-20">For last 20 minutes</option>
            <option value="for_last_d-h-m_0-0-30">For last 30 minutes</option>
            <option value="for_last_d-h-m_0-1-0">For last hour</option>
            <option value="for_last_d-h-m_0-2-0">For last 2 hours</option>
            <option value="for_last_d-h-m_0-6-0">For last 6 hours</option>
            <option value="for_last_d-h-m_0-12-0">For last 12 hours</option>
            <option value="today">Today</option>
            <option value="this_week">This week</option>
            <option value="for_last_d-h-m_7-0-0">For last week</option>
            <option value="this_month">This month</option>
            <option value="for_last_d-h-m_31-0-0">For last month</option>
            <option value="for_last_d-h-m_92-0-0">For last 3 months</option>
            <option value="for_last_d-h-m_183-0-0">For last 6 months</option>
            <option value="this_year">This year</option>
            <option value="for_last_d-h-m_365-0-0">For last year</option>
            <option value="total">Total</option>
        </param>
	 *
	 *
	 *  NOTICE:
	 *    Function is complicated because SQL query is optimized!!
	 *
	 */
	function getConditionStringFromXmlTimePeriodList( $list_time_period ) {
		global $mainframe;

		if ($list_time_period == 'total')
			return '1=1';//total

		$sql_constr_time = '1=1';//total and wrong value


		if (substr($list_time_period, 0, 15) == 'for_last_d-h-m_') {
			$xml_day_minute_str = substr($list_time_period, 15);
			$day_minute_arr = explode('-', $xml_day_minute_str);
			$nbr_of_days = (int)$day_minute_arr[0];
			$nbr_of_hours = (int)$day_minute_arr[1];
			$nbr_of_minutes = (int)$day_minute_arr[2];

			if ($nbr_of_days == 0) 
				$sql_constr_time = 'v.visit_time >= DATE_SUB(NOW(), INTERVAL \''.$nbr_of_hours.':'.$nbr_of_minutes.'\' HOUR_MINUTE)';
			else if ( ($nbr_of_hours == 0) && ($nbr_of_minutes == 0) )
				$sql_constr_time = 'v.visit_date >= DATE_SUB(NOW(), INTERVAL \''.$nbr_of_days.'\' DAY)';
			else
				$sql_constr_time = 'CAST(CONCAT(v.visit_date, \' \', v.visit_time) AS DATETIME) >= DATE_SUB(NOW(), INTERVAL \''.$nbr_of_days.' '.$nbr_of_hours.':'.$nbr_of_minutes.'\' DAY_MINUTE)';
		} else {
			switch ($list_time_period)
			{
				case 'today':
					$today_date_str = $this->js_gmdate('Y-m-d');
					$sql_constr_time = 'v.visit_date = \''.$today_date_str.'\'';
					break;
				case 'this_week':
					$year_date_str = $this->js_gmdate('Y');
					$today_date_str = $this->js_gmdate('Y-m-d', $now_timestamp);
					$sql_constr_time = '( YEAR(v.visit_date) = \''.$year_date_str.'\' AND WEEK(v.visit_date,3) = WEEK(\''.$today_date_str.'\',3) )';
					break;
				case 'this_month':
					$year_date_str = $this->js_gmdate('Y');
					$month_date_str = $this->js_gmdate('m', $now_timestamp);
					$sql_constr_time = '( YEAR(v.visit_date) = \''.$year_date_str.'\' AND MONTH(v.visit_date) = \''.$month_date_str.'\' )';
					break;
				case 'this_year':
					$year_date_str = $this->js_gmdate('Y');
					$sql_constr_time = 'YEAR(v.visit_date) = \''.$year_date_str.'\'';
					break;
				default: //wrong value
					$sql_constr_time = '1=1';//total
					break;
			}
		}

		return $sql_constr_time;
	}


	/**
	 *  This function transform user provided translation string to associative array
	 *    that can be easy used to translate
	 * 
	 *
	 *  Example of using:
	 *        <param name="tld_translation_tool"      type="textarea"   default=""                     label="Country translation tool" description="Enter translation here for 'country names'. Country names will be replaced by texts defined here.&lt;br/>&lt;br/>You can also use 'Translation tool' to provide shortcuted country name: 'United States of America' => 'USA'&lt;br/>&lt;br/>eg.&lt;br/>&lt;b>de=Deutschland; nl=Niederlande; us=Vereinigte Staaten&lt;/b>&lt;br/>'Germany' will be replaced by 'Deutschland', 'Netherlands' by 'Niederlande' and 'United States' by 'Vereinigte Staaten'" rows="3" cols="35" />
	 *
	 *		$tld_translation_arr     = create_translation_arr($tld_translation_tool);
	 *		$tld_name = $Visitor->Tld->tld_name; //oryginal name
	 *		if ( isset($tld_translation_arr[$Visitor->Tld->tld]) )
	 *			$tld_name = $tld_translation_arr[$Visitor->Tld->tld]; //replace by translated name
	 *
	 *
	 *  @param string in  $translation_tool_str 	eg.: "de=Deutschland; nl=Niederlande; us=Vereinigte Staaten" (semicolon is separator (not space))
	 *  @return array								eg.: array( 'de'=>'Deutschland', 'nl'=>'Niederlande', 'us'='Vereinigte Staaten')
	 *
	 *  @since: v3.0.1.446
	 */
	function create_translation_arr($translation_tool_str) {
		if (strlen($translation_tool_str) == 0)
			return array();

		$translation_arr = array();
		$trans_arr = explode(';', $translation_tool_str);
		foreach ($trans_arr as $trans) {
			$var_val_arr = explode('=', $trans);
			if ( !isset($var_val_arr[0]) || !isset($var_val_arr[1]) )
				continue;
			$var = trim($var_val_arr[0]);
			$val = trim($var_val_arr[1]);
			if ( (strlen($var_val_arr[0]) == 0) || (strlen($var_val_arr[1]) == 0) )
				continue;
			$translation_arr[$var] = $val;
		}

		return $translation_arr;
	}


	//this is copy of function from base.classes.php file
	/** This function return timezone for JoomlaStats.
	 *  Returned time zone is for anonymous front page users!
	 *  @return double (eg. 1, 2, -9.5, 10.5)
	 *
	 *  Timezone should be always get through this function.
	 *  For details see http://www.joomlastats.org:8080/display/JS/FAQ+Wrong+time+in+JoomlaStats and http://www.joomlastats.org:8080/display/JS/FAQ+Time+and+Time+Zones+in+JoomlaStats
	 */
	function js_getJSTimeZone() {
	
		$TZOffset = 0;
		
		//one of this HAVE TO be defined - if not this is serious bug
		if( defined( '_JEXEC' ) ) {
			// Joomla! 1.5
			global $mainframe;
			$TZOffset = $mainframe->getCfg( 'offset' );
	
			//// code from JDate
			//$_date = strtotime(gmdate("M d Y H:i:s", time()));
			//$date_a = $_date + $offset*3600;
			//$date_str = date('Y-m-d H:i:s', $date_a);
			//js_echoJSDebugInfo('Loc:', $date_str);
			//
			//$gm_date = gmdate("M d Y H:i:s", time());
			//js_echoJSDebugInfo('GMT time:', $gm_date);
		} else if( defined( '_JS_STAND_ALONE' ) ) {
			//stand alone
			require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'stand.alone.configuration.php' );
			$JSStandAloneConfiguration = new js_JSStandAloneConfiguration();
			$TZOffset = $JSStandAloneConfiguration->JConfigArr['offset'];
		}
	
		return $TZOffset;
	}
	
	//this is copy of function from base.classes.php file    //REMEMBER to add "$this->"
	/** This function return timestamp for now for JoomlaStats.
	 *  Current time should be always get through this function.
	 *
	 *  Returned timestamp is in timezone for anonymous front page users!
	 *
	 *  For details see http://www.joomlastats.org:8080/display/JS/FAQ+Wrong+time+in+JoomlaStats and http://www.joomlastats.org:8080/display/JS/FAQ+Time+and+Time+Zones+in+JoomlaStats
	 */
	function js_getJSNowTimeStamp() {
		return (time() + ($this->js_getJSTimeZone() * 3600));
	}
	
	
	//this is copy of function from base.classes.php file    //REMEMBER to add "$this->"
	/** Use this function insted of PHP gmdate() to format date!!! 
	 *
	 *  This function is connected with js_getJSNowTimeStamp() and js_getJSTimeZone()
	 *  and provided to easier and reliable change in case of replace gmdate() to date() etc.
	 */
	function js_gmdate($format, $timestamp=null) {
		if ($timestamp===null)
			return gmdate($format, $this->js_getJSNowTimeStamp());
	
		return gmdate($format, $timestamp);
	}


	/** Use this function to get JoomlaStats configuration
	 *
	 *  For details about formats, meaning, default values and examples
	 *  see description of class js_JSConfDef in file "(...)\joomla15\administration\base.classes.php"
	 *
	 */
	
	function getJSConfiguration($return_times_in_joomla_cms_default_time_zone = true) {
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. '..' .DIRECTORY_SEPARATOR. 'base.classes.php' );
		$JSConf = new js_JSConf();

		if ($return_times_in_joomla_cms_default_time_zone == true) {
			 //$JSConf->first_installation_date = $JSConf->first_installation_date - $this->js_getJSTimeZone() * 3600; @bug @todo			 
		}

		return $JSConf;
	}
	
}

