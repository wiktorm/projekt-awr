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
 * Object of this class generate HTML code
 *
 * Here should be HTML code that is common for many JS pages
 *
 * require_once( dirname(__FILE__) .DS. 'template.html.php' );
 *
 * All methods should be static functions!
 */
class js_JSTemplate
{
	
	/**
	 * Translates month number to string
	 *
	 * Code from joomla 1.5.9 (thanks) (file with below translation is always included in joomla backend and frontend)
	 *
	 * @access public
	 * @param int $month The numeric month of the year
	 * @param bool $abbr Return the abreviated month string?
	 * @return string month string
	 */
	function monthToString($month, $abbr = false)
	{
		switch ($month)
		{
			case 1:  return $abbr ? JText::_('JANUARY_SHORT')   : JText::_('JANUARY');
			case 2:  return $abbr ? JText::_('FEBRUARY_SHORT')  : JText::_('FEBRUARY');
			case 3:  return $abbr ? JText::_('MARCH_SHORT')     : JText::_('MARCH');
			case 4:  return $abbr ? JText::_('APRIL_SHORT')     : JText::_('APRIL');
			case 5:  return $abbr ? JText::_('MAY_SHORT')       : JText::_('MAY');
			case 6:  return $abbr ? JText::_('JUNE_SHORT')      : JText::_('JUNE');
			case 7:  return $abbr ? JText::_('JULY_SHORT')      : JText::_('JULY');
			case 8:  return $abbr ? JText::_('AUGUST_SHORT')    : JText::_('AUGUST');
			case 9:  return $abbr ? JText::_('SEPTEMBER_SHORT')  : JText::_('SEPTEMBER');
			case 10: return $abbr ? JText::_('OCTOBER_SHORT')   : JText::_('OCTOBER');
			case 11: return $abbr ? JText::_('NOVEMBER_SHORT')  : JText::_('NOVEMBER');
			case 12: return $abbr ? JText::_('DECEMBER_SHORT')  : JText::_('DECEMBER');
		}
	}

	/**
	 * Translates day of week number to string
	 *
	 * Code from joomla 1.5.9 (thanks) (file with below translation is always included in joomla backend and frontend)
	 *
	 * @access public
	 * @param int $day The numeric day of the week
	 * @param bool $abbr Return the abreviated day string?
	 * @return string day string
	 */
	function dayToString($day, $abbr = false)
	{
		switch ($day)
		{
			case 0: return $abbr ? JText::_('SUN') : JText::_('SUNDAY');
			case 1: return $abbr ? JText::_('MON') : JText::_('MONDAY');
			case 2: return $abbr ? JText::_('TUE') : JText::_('TUESDAY');
			case 3: return $abbr ? JText::_('WED') : JText::_('WEDNESDAY');
			case 4: return $abbr ? JText::_('THU') : JText::_('THURSDAY');
			case 5: return $abbr ? JText::_('FRI') : JText::_('FRIDAY');
			case 6: return $abbr ? JText::_('SAT') : JText::_('SATURDAY');
		}
	}
	

	/**
	 * It was tested in FF 2.0 and IE 7.0 - it is working!!!
	 * $MsgArr - array with messages to display
	 * $NoMsgInfoMsg - text displayed when there is no messages in $MsgArr
	 * FrameDataConst - Do not change colors - they are strictly connected with message types!
	 * 'b_clr' - border color; 't_b_clr' - title background color; 'c_b_clr' - contnt background color;
	 *
	 * @param unknown_type $type
	 * @param array $MsgArr
	 * @param string $NoMsgInfoMsg
	 * @param string $extraHtmlContent
	 * @return string
	 */
	function generateMsgColorInfoFrame( $type, $MsgArr, $NoMsgInfoMsg, $extraHtmlContent='' ) {
		$FDConstArray =	array(
			'error' 	=> array(
				'title'		=> JTEXT::_( 'Errors' ),          //red
				'b_clr'		=> '#FF0000',
				't_b_clr'	=> '#FF9999',
				'c_b_clr'	=> '#FFEEEE' ),
			'warning' 	=> array(
				'title'		=> JTEXT::_( 'Warnings' ),        //red
				'b_clr'		=> '#AF0A37',
				't_b_clr'	=> '#F7A5A5',
				'c_b_clr'	=> '#FDF3F3' ),
			'recommend'	=> array(
				'title'		=> JTEXT::_( 'Recommendations' ), //orange
				'b_clr'		=> '#F58E0E',
				't_b_clr'	=> '#FFE9C4',
				'c_b_clr'	=> '#FFF4E0' ),
			'info'		=> array(
				'title'		=> JTEXT::_( 'Information' ),     //blue
				'b_clr'		=> '#0000FF',
				't_b_clr'	=> '#A0A0FF',
				'c_b_clr'	=> '#F0F0FF' ),
			'db'		=> array(
				'title'		=> JTEXT::_( 'Database' ),       //green
				'b_clr'		=> '#00FF00',
				't_b_clr'	=> '#A0FFA0',
				'c_b_clr'	=> '#F0FFF0' )
		);

		$FDConst = $FDConstArray[$type];
		if ( !array_key_exists( $type, $FDConstArray ) ) {
			$HtmlCode  = 'js_template_err01 - function js_JSTemplate::generateMsgColorInfoFrame in file template.html.php. There is no $type\''.$type.'\'.';
			$FDConst = $FDConstArray['error'];
		}

		$HtmlCode = '<div class="status">' . "\n"
		. '<div style="margin-top: 25px"></div>' . "\n"
		. '<div style="margin-left: 15px">
		<span style="border-width: 2px 0px 0px 0px; border-style: solid; border-color: '.$FDConst['b_clr'].'; padding: 0px 10px 0px 10px; background-color: '.$FDConst['t_b_clr'].'; font-weight: bold; font-size: larger;">'.$FDConst['title'].'</span>'
		. '</div>' . "\n"
		. '<div style="text-align: justify; border-width: 2px; border-style: solid; border-color: '.$FDConst['b_clr'].'; padding: 4px; background-color: '.$FDConst['c_b_clr'].';">';

		if( ( count( $MsgArr ) == 0 ) && ( $extraHtmlContent == '' ) ) {
			// text displayed when there is no messages
			$HtmlCode .= $NoMsgInfoMsg;
		}else{
			$isFirst = true;
			foreach( $MsgArr as $msg ) {
				if( $isFirst == true ) {
					$isFirst = false;
				}else{
					// space from previous message
					$HtmlCode .= '<br/><br/>';
				}

				$HtmlCode .= '<div style="padding-bottom: 3px;">'
				. '<span style="border-bottom: 3px double ' . $FDConst['b_clr'] . ';">' . $msg['name'] . '</span>'
				. '</div>' . "\n"
				. '<div style="padding-left: 1em;">' . $msg['description'] . '</div>' . "\n";
			}
		}

	 	if ( ( count( $MsgArr ) > 0 ) && ( $extraHtmlContent != '' ) ) {
	 		// generate space between info msgs and extra content
 			$HtmlCode .= '<div style="clear:both; margin-top: 15px"></div>' . "\n";
	 	}

	 	if ( $extraHtmlContent != '' ) {
	 		// show extra content
			$HtmlCode .= $extraHtmlContent;
	 	}

		$HtmlCode .= '</div>' . "\n" . '</div>' . "\n"; //end of content frame

		return $HtmlCode;
	}


	/**
	 * Use this function to make tool bar works
	 * if $task=='' all icons on tool bar must set task!
	 *
	 * @param string $task
	 * @return string
	 */
	function generateAdminForm( $task = '' ) {
		$form  = $this->generateBeginingOfAdminForm( $task ) . $this->generateEndOfAdminForm();

		return $form;
	}

	/**
	 * if $task=='' all icons on tool bar must set task!
	 *
	 * @param unknown_type $task
	 * @return unknown
	 */
	function generateBeginingOfAdminForm( $task = '' ) {
		$form  = '<form name="adminForm" id="adminForm" method="post" action="index.php" style="display: inline; margin: 0px; padding: 0px;" onsubmit="return true;">' . "\n"
		. '<input type="hidden" name="option" value="com_joomlastats" />' . "\n"
		. '<input type="hidden" name="task" value="' . $task . '" />' . "\n";
		//$form .= '<input type="hidden" name="boxchecked" value="0" />' . "\n"; // @at Do we use this?
		// >> mic: YES WE NEED THIS - but only IF - we have a form AND have some checkboxes to click on
		return $form;
	}

	/**
	 * writes the final </form> tag
	 *
	 * @return string
	 */
	function generateEndOfAdminForm() {
		$form = '</form>' . "\n";

		return $form;
	}

	/**
	 * loads correct tooltip library
	 *
	 */
	function jsLoadToolTip() {
		JHTML::_('behavior.tooltip');
	}
	
	/**
	 * helper function for displaying tooltip
	 *
	 * @since 2.3.x
	 * @param string $tip
	 */
	function jsToolTip( $tip ) {
		return JHTML::tooltip( $tip );
	}
}