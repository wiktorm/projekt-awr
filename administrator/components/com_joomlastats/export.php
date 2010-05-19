<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */


if( !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}

require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'access.php' );

/**
 *  This class contain set of static functions that allow export JS data to outside world
 *
 *  NOTICE: This class should contain only set of static, argument less functions that are called by task/action
 */
class js_JSExport
{
	/**
	 * Export JS to .csv (Coma Separated Values) file
	 *
	 * @access static, public
	 * @return error message if error occure or if success end of this function never is reached!!
	 */
	function exportJSToCsv() {
		$jsExport = new js_ExportJSToCsv();
		if( !$jsExport->export() ) {
			$error_codes = $jsExport->getErrorCodesString();
			return '<div class="adminform" class="jsError" style="color:red; width:100%; background-color:#FFEFEF; border-top:2px solid #FF0000; border-bottom:2px solid #FF0000; margin:5px; padding:5px;">'
			. JTEXT::sprintf( 'Export error %s', $error_codes )
			.'</div>';
		}
	}
}

/**
 * Joomla Stats Constant class
 *
 * This class define Constants that are used by js_Export class, and other functions that use js_Export. It is also used by Export Template.
 */
class js_ExportConst
{
	/**
	 * Support such data as: 'Table Hashes', 'Table Names' and 'Table Native Names'
	 *
	 * @access static, public
	 * @return array
	 */
	function getTableHashesAndNames() {

		$tableHashesAndNames = array(
			array( 'hash' => 1, 'name' => 'jstats_ipaddresses',    'native_name' => JTEXT::_( 'Visitor' ) ),
			array( 'hash' => 2, 'name' => 'jstats_keywords',	   'native_name' => JTEXT::_( 'Search engines' ) ),
			array( 'hash' => 3, 'name' => 'jstats_page_request',   'native_name' => JTEXT::_( 'Page hits actual' ) ),
			array( 'hash' => 4, 'name' => 'jstats_page_request_c', 'native_name' => JTEXT::_( 'Page hits summarized' ) ),
			array( 'hash' => 5, 'name' => 'jstats_pages',		   'native_name' => JTEXT::_( 'Visited pages' ) ),
			array( 'hash' => 6, 'name' => 'jstats_referrer',	   'native_name' => JTEXT::_( 'Referrer' ) ),
			array( 'hash' => 7, 'name' => 'jstats_visits',		   'native_name' => JTEXT::_( 'Visits' ) )
		);

		return $tableHashesAndNames;
	}

	/**
	 * Get default setting for export. Used at forms, when get value from request etc.
	 *
	 * This function return table hash.
	 *
	 * @access static, public
	 * @return array
	 */
	function getTableDefaultHash() {
		return 1; //'jstats_ipaddresses' table
	}
}

/**
 * Joomla Stats Export class
 *
 * This class realize exporting data from Joomla Stats to other systems and/or formats
 *
 * @abstract
 */
class js_ExportBase
{
	/**
	 * Store error strings
	 *
	 * Currently used error strings are:
	 *	'js_err_code__export_1' - 1 to 18 (inclusive)
	 *
	 * Strings must be long and unique at entire joomla scope to easy find when they were triggered
	 *
	 * @access private
	 * @var array
	 */
	var $__error_codes = array();

	/** database placeholder */
	var $db;


	function __construct() {
		$JSDatabaseAccess = new js_JSDatabaseAccess();
		$this->db = $JSDatabaseAccess->db;
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
	function js_ExportBase()
	{
		$args = func_get_args();
		call_user_func_array(array(&$this, '__construct'), $args);
	}	

	
	/**
	 * Main function that export data to user
	 *
	 * That function should be overwritten in inherited classes
	 *
	 * @abstract
	 * @access public
	 * @return boolean - true on succes
	 */
	function export() {
		$this->_appendErrorCode( 'js_err_code__export_1' );
		return false;
	}

	/**
	 * Return table name (without joomla database prefix) from hash
	 *
	 * SECURITY NOTICE:
	 *	  Remember to use $tableHash not $tableName.
	 *	  Html forms should pass table Hashes, if not User can be able to send e.g. 'users'
	 *	  and he get content jos_users table (all data with passwords!!!)
	 *
	 * @access private
	 * @return boolean - true on success
	 */
	function __getTableNameFromHash( $tableHash, &$tableName, &$nativeTableName ) {
		$js_ExportConst = new js_ExportConst();
		$tableHashesAndNames = $js_ExportConst->getTableHashesAndNames();

		foreach( $tableHashesAndNames as $tableHAN ) {
			if( $tableHAN['hash'] == $tableHash ) {
				$tableName		 = $tableHAN['name'];
				$nativeTableName = $tableHAN['native_name'];

				return true;
			}
		}

		$this->_appendErrorCode( 'js_err_code__export_2' );
		return false;
	}

	/**
	 * Return objects - each object is a row from table
	 *
	 * @access private
	 * @return boolean - true on success
	 */
	function __getDataFormTable( $dbTableName, &$dataList ) {

		$query = 'SELECT *'
		. ' FROM ' . $dbTableName
		;
		$this->db->setQuery( $query );
		//$dataList = $database->loadObjectList();
		$dataList = $this->db->loadRowList();
		//$dataList = $database->loadResultArray();
		if( $this->db->getErrorNum()) {
			$this->_appendErrorCode( 'js_err_code__export_3' );
			return false;
		}

		return true;
	}

	/**
	 * Return objects
	 *
	 * @AT I am not sure that all types of databases supports command SHOW COLUMNS
	 *	   In the future joomla will support more databases than MySql
	 *
	 * NOTICE:
	 *   I know that column names we can posses form simple SELECT request. But in the future
	 *   maybe we want to have column names translated. This is place where it should be done.
	 *
	 * @todo We can make that function language depended (now it returns always english strings)
	 *
	 * @access private
	 * @return boolean - true on success
	 */
	function __getColumnTitles( $dbTableName, &$columnTitleList ) {

		// table: header field names
		$query = 'SHOW COLUMNS'
		. ' FROM ' . $dbTableName
		;
		$this->db->setQuery( $query );
		$columDescList = $this->db->loadObjectList();
		if( $this->db->getErrorNum()) {
			$this->_appendErrorCode( 'js_err_code__export_4' );
			return false;
		}

		$columnTitleList = array();
		foreach( $columDescList as $columDesc ) {
			$columnTitleList[] = $columDesc->Field;
		}

		return true;
	}

	/**
	 * Return objects - each object is a row from table
	 *
	 * @access protected
	 * @return boolean - true on success
	 */
	function _getDataFormTableWithColumTitles( $tableHash, &$dataWithTitleList ) {
		global $mainframe;

		$tableName			= '';
		$nativeTableName	= '';
		$columnTitleList	= array();

		if ( !$this->__getTableNameFromHash( $tableHash, $tableName, $nativeTableName ) ) {
			$this->_appendErrorCode( 'js_err_code__export_14' );
			return false;
		}
		$dbTableName = $mainframe->getCfg( 'dbprefix' ) . $tableName;

		if( !$this->__getColumnTitles( $dbTableName, $columnTitleList ) ) {
			$this->_appendErrorCode( 'js_err_code__export_5' );
			return false;
		}
		$dataWithTitleList[] = $columnTitleList;

		//echo '<br />dataWithTitleList: '.print_r($dataWithTitleList, true).'<br />';

		$tit_nbr = count( $dataWithTitleList );

		$dataList = array();
		if (!$this->__getDataFormTable( $dbTableName, $dataList ) ) {
			$this->_appendErrorCode( 'js_err_code__export_6' );
			return false;
		}
		foreach( $dataList as $row ) {
			$dataWithTitleList[] = $row;
		}

		//echo '<br />dataWithTitleList2: '.print_r($dataWithTitleList, true).'<br />';

		return true;
	}

	/**
	 * Get string that should be used as file name (without extension)
	 *
	 * @todo remove special characters that can not be in file name (like \ etc)
	 * @access private
	 * @return boolean - true on success
	 */
	function __getExportFileName( $tableHash, $extension, &$fileName ) {
		global $mainframe;

		$tableName			= '';
		$nativeTableName	= '';

		if( !$this->__getTableNameFromHash( $tableHash, $tableName, $nativeTableName ) ) {
			$this->_appendErrorCode( 'js_err_code__export_9' );
			return false;
		}

		$now			= js_gmdate( 'Ymd_His' );
		$fileName		= substr( $mainframe->getCfg( 'sitename' ), 0, 12 ) . '_' . $now . '_' . $nativeTableName . $extension;
		$wrong_chars	= array( ' ', "\\", '/', '"', '\'');
		$fileName		= str_replace( $wrong_chars, '_', $fileName );

		return true;
	}

	/**
	 * Get Joomla Stats version
	 * old name getdbversion()
	 *
	 * @todo This function should be removed. Version should be possessed from JSConfiguration Object
	 * @access private
	 * @return boolean - true on success
	 */
	function __getJSVersion( &$JSVersion ) {

		$query = 'SELECT *'
		. ' FROM #__jstats_configuration'
		;
		$this->db->setQuery( $query );
		$rows = $this->db->loadAssocList();
		if( $this->db->getErrorNum()) {
			$this->_appendErrorCode( 'js_err_code__export_16' );
			return false;
		}

		$JSVersion = '';
		foreach( $rows as $row ) {
			if( $row['description'] == 'version' ) {
				$JSVersion = $row['value'];
			}
		}

		if( $JSVersion == '' ) {
			$this->_appendErrorCode( 'js_err_code__export_17' );
			return false;
		}

		return true;
	}

	/**
	 * Get string that should be witten to exported file (usualy at first line)
	 *
	 * @access private
	 * @return boolean - true on success
	 */
	function __getExportDescription( $tableHash, $fileName, &$desc ) {
		global $mainframe;

		$tableName			= '';
		$nativeTableName	= '';

		if ( !$this->__getTableNameFromHash( $tableHash, $tableName, $nativeTableName ) ) {
			$this->_appendErrorCode( 'js_err_code__export_11' );
			return false;
		}

		$JSVersion = '';
		if( !$this->__getJSVersion($JSVersion) ) {
			$this->_appendErrorCode( 'js_err_code__export_18' );
			return false;
		}

		$now = js_gmdate( 'Y-m-d H:i:s' );

		$desc  = ''
		. 'Data dump from site: \'' . $mainframe->getCfg( 'sitename' ) . '\','
		. ' at address: \'' . $mainframe->getCfg( 'live_site' ) .'\'. '
		. 'dumped from Joomla CMS, by JoomlaStats component version: \'' . $JSVersion . '\'.'
		. ' See website of JomlaStats: http://www.joomlastats.org for more details. '
		. 'File name: \'' . $fileName . '\'. '
		. 'Data dumped at: \'' . $now . '\' joomla local time. '
		. 'Data dumped at: \'' . gmdate( 'Y-m-d H:i:s' ) . '\' GMT (Greenwich Mean Time) time zone. '
		. 'File contains: \'' . $nativeTableName . '\' data. '
		. 'File contain data from table: \'' . $mainframe->getCfg( 'dbprefix') . $tableName . '\'.'
		;

		return true;
	}

	/**
	 * Get strings:
	 * @var fileName	  - should be used as file name (without extension)
	 * @var desc		  - should be witten to exported file (usualy at first line)
	 *
	 * @access protected
	 * @return boolean - true on success
	 */
	function _getExportFileNameAndDesc( $tableHash, $extension, &$fileName, &$desc ) {

		$fileName = '';

		if( !$this->__getExportFileName( $tableHash, $extension, $fileName) ) {
			$this->_appendErrorCode( 'js_err_code__export_12' );
			return false;
		}

		if( !$this->__getExportDescription( $tableHash, $fileName, $desc ) ) {
			$this->_appendErrorCode( 'js_err_code__export_13' );
			return false;
		}

		return true;
	}


	/**
	 * Append error to error array
	 *
	 * NOTICE:
	 *	 Before adding error string, check if it is unique (see description of $this->__error member)
	 *
	 * @access protected
	 * @var string
	 * @return boolean - true on success
	 */
	function _appendErrorCode( $errorString ) {
		$this->__error_codes[] = $errorString;
	}

	/**
	 * Generate string filled by Jommla Stats Error Codes separated by coma, enclosed by brackets
	 *
	 * @access public
	 * @return string with Jommla Stats Error Codes
	 */
	function getErrorCodesString() {
		if( count( $this->__error_codes ) == 0 ) {
			return '';
		}

		return '['.implode( ', ', $this->__error_codes ) . ']';
	}
}

/**
 * This class implement export to CSV format (Microsoft Excel could read this format)
 */
class js_ExportJSToCsv extends js_ExportBase
{
	/** constructor initialize base class */
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Escape special characters that could not be as value in cell (like ")
	 *
	 * @todo Check how it works (it was never checked)
	 * @todo Should we escape \ character?
	 * @access private
	 * @var string
	 * @return string
	 */
	function __escapeCellData( $cellData ) {
		$wrong_chars   = array( '"',  "\\" );
		$correct_chars = array( '\"', "\\\\" );

		return str_replace( $wrong_chars, $correct_chars, $cellData );
	}


	/**
	 * This function send *.csv file to user
	 *
	 * @access public
	 * @return boolean - true on succes
	 */
	function export() {

		$js_ExportConst = new js_ExportConst();

		$tableDefaultHash	= $js_ExportConst->getTableDefaultHash();
		$tableHash			= JRequest::getVar( 'js_table_hash', $tableDefaultHash );
		$extension			= '.csv';
		$fileName			= '';
		$desc				= '';

		if( !$this->_getExportFileNameAndDesc( $tableHash, $extension, $fileName, $desc ) ){
			$this->_appendErrorCode( 'js_err_code__export_15' );
			return false;
		}

		$dataWithTitleList = array();
		if( $this->_getDataFormTableWithColumTitles( $tableHash, $dataWithTitleList ) ) {
			// NOTICE: If You are looking for 8 spaces at begining of .csv file. They are not here :(
			$csv = '';
			$csv = '"' . $this->__escapeCellData( $desc ) ."\"\n";

			foreach( $dataWithTitleList as $row ) {
				foreach( $row as $cell ) {
					$csv .= '"' . $this->__escapeCellData( $cell ) .'",';
				}

				$csv[strlen( $csv ) -1] = "\n";
			}

			header( 'Content-type: application/vnd.ms-excel; charset=UTF-8' );
			header( 'Content-disposition: csv; filename="'.$fileName.'"; size="'.strlen( $csv ).'"' );
			echo $csv;
			exit(); // important!!
			//@todo we should send message to user that file was send - mic: he sees that, so why?
		}

		$this->_appendErrorCode( 'js_err_code__export_8' );
		return false;
	}
}