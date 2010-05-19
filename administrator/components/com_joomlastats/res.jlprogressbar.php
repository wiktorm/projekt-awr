<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */


 
 
//JL
//require_once($mosConfig_live_site .'/components/com_joomlalib/jlcoreapi.inc');
		//echo "loadconfigresult= ";
		//echo JLCoreApi::loadConfig();
///JLCoreApi::import('jlprogressbar');



////////////////////////////////////////////////////////////////////////




/**
 * Progress bar class
 * 
 * Show your component user what and how long they are waiting for!
 * Based on the Gallery 2 progress bar.
 *
 * @package JL
 */

//defined('_JOOMLALIB') or die( 'Direct Access to this location is not allowed.' );
/**
 * Class to make progress bars easy
 *
 * @package JL
 * @subpackage JLProgressbar
 */
class JLProgressbar {
	/**
	 * Start time of last round, use in calculating lap time
	 *
	 * @var float
	 */
	var $startTime;
	/**
	 * Array containing our lap data.
	 *
	 * @var array
	 */
	var $lapData;
	/**
	 * Title to display
	 *
	 * @var string
	 */
	var $title;
	/**
	 * Array containing all the errors
	 *
	 * @var array
	 */
	var $errorBuffer = array();
	/**
	 * Url to display after succesfull completion
	 *
	 * @var string
	 */
	var $succesUrl;
	/**
	 * Url to display after unsuccesfull completion
	 *
	 * @var string
	 */
	var $errorUrl;
	
	/**
     * Return the major and minor version of the JLProgressbar API.
     *
     * @return array major number, minor number
     */
    function getApiVersion() {
		return array(2, 0);
    }
	
    
	/**
	 * Construct
	 *
	 * @param string $title
	 * @param string $total
	 * @param string $succesUrl
	 * @param [string $errorUrl]
	 */
	function __construct($title, $succesUrl, $errorUrl = null) {
		$this->title = $title;
		$this->succesUrl = str_replace('&amp;', '&', $succesUrl);
		$this->errorUrl = empty($errorUrl) ? $succesUrl : str_replace('&amp;', '&', $errorUrl);
		/* load the html page but clear buffer first, load, flush, Make this a option */
		while (@ob_end_clean());
		ob_start();
		$this->_getHTML();
		flush();
		ob_flush();
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
	function JLProgressbar($title, $succesUrl, $errorUrl = null)
	{
		$args = func_get_args();
		call_user_func_array(array(&$this, '__construct'), $args);
	}	
    
	
	function startProgress($description = '&nbsp;'){
		$this->startTime = $this->_getTime();
		$this->lapData = array();
		$memoryInfo = $this->_getMemoryInfo();
		$str = '<script type="text/javascript">updateProgressBar("%s","%s","%s","%s","%s")</script>';
		$html = sprintf( $str, $this->title, $description, 0, '', $memoryInfo);
		$this->_print($html);
	}
	
	/**
	 * Call this function, with regular intervals
	 *
	 * @param int $current, where are we in the process
	 * @param string $desc, You can update the decription
	 * @todo test if 1.4 is a good value to switch between mean types.
	 */
	function updateProgress($complete, $description = '&nbsp;'){
		$time = $this->_getTime();
		$this->lapData[] = ($time - $this->startTime) ;
		$this->startTime = $time;
		
		if($complete > 0 && $complete < 1 && count($this->lapData) > 1){
    		$elapsed = $this->_geometricMean($this->lapData);
    		$timeRemaining = ($elapsed / $complete) - $elapsed;
    		$timeRemaining = sprintf('Estimate time remaining: %d:%02d', (int)($timeRemaining/60), $timeRemaining % 60);
    	} else {
    		$timeRemaining='';
    	}
    	 	
		$memoryInfo = $this->_getMemoryInfo();
		$complete = $this->_roundToString($complete, 2);
		
		$str = '<script type="text/javascript">updateProgressBar("%s","%s","%s","%s","%s")</script>';
		$html = sprintf( $str, $this->title, $description, $complete, $timeRemaining, $memoryInfo);
		$this->_print($html);	
	}
		
	/**
	 * Call when progress is finished.
	 *
	 */
	function doneProgress($description = '&nbsp;'){
		/* update to 100% */
		$url = (count($this->errorBuffer) > 0) ? $this->errorUrl : $this->succesUrl;
		$str = '<script type="text/javascript">updateProgressBar("%s","%s","%s","%s","%s");</script>';
		$html = sprintf( $str, $this->title, '', 1, '', $this->_getMemoryInfo());
		$html .= sprintf( '<script type="text/javascript">completeProgressBar("%s","%s");</script>', $url, $description);
		$this->_print($html);
	}
	
	/**
	 * Submit A error messages, plain text no line endings or <br />
	 *
	 * @param string $html
	 */
	function reportError($html){
		$this->errorMsg[] = $html;
		$str = '<script type="text/javascript">errorProgressBar("%s");</script>';
		$html = sprintf( $str, implode('<br />', $this->errorMsg));
		$this->_print($html);
	}
	
	/**
	 * Pads, print, and flush the string
	 *
	 * @todo try getting $minlen to be shorter on different browsers
	 * @param string $html
	 */
	function _print($html){
		/* pad ecerything with 1024 */
		if(strlen($html) < 1024){
			$html = sprintf("%-1024s\n", $html);
		}
		print $html;
		flush();
		ob_flush();
	}
	
	/**
	 * Get Time function with micro seconds
	 *
	 * @return float
	 */
	function _getTime(){
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
	
	/**
	 * geometric mean if some values are way off this will give a more accurate estimate but is a little slower.
	 *
	 * @param array $dataSet
	 * @return float elapsed 
	 */
	function _geometricMean($dataSet){
		$power = 1/count($dataSet);
		if(!function_exists('array_product')){
			$product = 1;
			foreach($dataSet as $n){
			   $product *= $n;
			}
		} else {
			$product = array_product($dataSet);
		}
		return exp($power * log($product)) * count($dataSet);
	}
	
	/**
	 * Normal mean if values are close together
	 *
	 * @param array $dataSet
	 * @return float elapsed time
	 */
	function _arithmeticMean($dataSet){
		return array_sum($dataSet);
	}
	
	/**
     * Round a float and convert to a string.
     * Replace , with . in case current locale uses comma as fraction separator.
     *
     * @param float value to round
     * @param int precision, defaults to zero
     * @return string rounded value
     */
    function _roundToString($floatValue, $precision=0) {
		return str_replace(',', '.', round($floatValue, $precision));
    }
    
    function _getMemoryInfo(){
    	$memoryUsed = (function_exists('memory_get_usage')) ? memory_get_usage() : 0;
		$memoryTotal = (0 < ini_get('memory_limit')) ? ini_get('memory_limit') : 0;
		return sprintf('Memory used: %s, total: %s', $memoryUsed, $memoryTotal);
    }
    
    function _getHTML(){
    	//require_once(dirname(__FILE__).'/../html/jlprogressbar.html');
    	require_once(dirname(__FILE__).'/res.jlprogressbar.html.php');
    }
}





class JLProgressbarDual extends JLProgressbar {
	function startStep($description = '&nbsp;', $complete = 0){
		$html = sprintf('<script type="text/javascript">updateStep("%s","%s");</script>', $description, $complete);
		$this->_print($html);
	}
	
	function doneStep($description = '&nbsp;'){
		$html  = sprintf('<script type="text/javascript">updateStep("%s","%s");</script>', $description, 1);
		$this->_print($html);
	}
	
	function updateStep($complete, $description = '&nbsp;'){
		$html = sprintf('<script type="text/javascript">updateStep("%s","%s");</script>', $description, $complete);
		$this->_print($html);
	}
	
	function _getHTML(){
    	//require_once(dirname(__FILE__).'/../html/jlprogressbarDual.html');
    	require_once(dirname(__FILE__).'/res.jlprogressbardual.html.php');
    	
    }
}
