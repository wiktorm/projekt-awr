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
require_once( dirname(__FILE__) .DS. 'template.html.php' );

/**
 * Object of hold templates to configuration options
 */
class js_JSConfigurationTpl
{

	/**
	 * This function shows a configuration page with tabs (common)
	 */
	function viewJSConfigurationPageTpl( $JSConf, $LastSummarizationDate ) {

		jimport('joomla.html.pane');
		$pane =& JPane::getInstance( 'tabs' );

		$JSTemplate = new js_JSTemplate();
		$JSTemplate->jsLoadToolTip();

		echo $JSTemplate->generateBeginingOfAdminForm();
?>
		<table width="100%" border="0" cellpadding="2" cellspacing="0" class="adminForm">
		<tr>
			<td>
			<?php
				echo $pane->startPane( 'js_configuration_pane' );

				echo $pane->startPanel( JTEXT::_( 'Common' ), 'general' );
				$this->viewJSConfigurationPageTplCommonTab( $JSConf );
				echo $pane->endPanel();

				echo $pane->startPanel( JTEXT::_( 'Performance' ), 'performance' );
				echo $this->viewJSConfigurationPageTplPerformanceTab( $JSConf, $LastSummarizationDate );
				echo $pane->endPanel();

				echo $pane->endPane();
			?>
			</td>
		</tr>
		</table>
		<?php
		echo $JSTemplate->generateEndOfAdminForm();
	}

	/**
	 * This function show Common tab in Configuration Page
	 *
	 * @todo: This function should return string instead of echo content
	 */
	function viewJSConfigurationPageTplCommonTab( $JSConf ) {

		$JSTemplate = new js_JSTemplate();
		?>
		<div style="font-size: 1px;">&nbsp;</div>
		<?php /*<!-- This div is needed to show content of tab correctly in 'IE 7.0' in 'j1.5.6 Legacy'. Tested in: FF, IE, j1.0.15, j1.5.6 and works OK --> */ ?>
		<table class="adminform" width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td nowrap="nowrap"><?php echo JTEXT::_( 'Onlinetime' ); ?></td>
			<td>
				<select name="onlinetime">
				<?php
				echo '<option value=  "10"'. ($JSConf->onlinetime ==   10 ? ' selected="selected"' : '') .'>10 '
				. JTEXT::_( 'Min' ) . '</option>' . "\n";
				echo '<option value=  "15"'. ($JSConf->onlinetime ==   15 ? ' selected="selected"' : '') .'>15 '
				. JTEXT::_( 'Min' ) . '</option>' . "\n";
				echo '<option value=  "20"'. ($JSConf->onlinetime ==   20 ? ' selected="selected"' : '') .'>20 '
				. JTEXT::_( 'Min' ) . '</option>' . "\n";
				echo '<option value=  "25"'. ($JSConf->onlinetime ==   25 ? ' selected="selected"' : '') .'>25 '
				. JTEXT::_( 'Min' ) . '</option>' . "\n";
				echo '<option value=  "30"'. ($JSConf->onlinetime ==   30 ? ' selected="selected"' : '') .'>30 '
				. JTEXT::_( 'Min' ) . '</option>' . "\n";
				echo '<option value=  "60"'. ($JSConf->onlinetime ==   60 ? ' selected="selected"' : '') .'>60 '
				. JTEXT::_( 'Min' ) . '</option>' . "\n";
				echo '<option value=  "90"'. ($JSConf->onlinetime ==   90 ? ' selected="selected"' : '') .'>90 '
				. JTEXT::_( 'Min' ) . '</option>' . "\n";
				echo '<option value= "120"'. ($JSConf->onlinetime ==  120 ? ' selected="selected"' : '') .'>2 '
				. JTEXT::_( 'Hrs' ) . '</option>' . "\n";
				echo '<option value= "240"'. ($JSConf->onlinetime ==  240 ? ' selected="selected"' : '') .'>4 '
				. JTEXT::_( 'Hrs' ) . '</option>' . "\n";
				echo '<option value= "480"'. ($JSConf->onlinetime ==  480 ? ' selected="selected"' : '') .'>8 '
				. JTEXT::_( 'Hrs' ) . '</option>' . "\n";
				echo '<option value= "720"'. ($JSConf->onlinetime ==  720 ? ' selected="selected"' : '') .'>12 '
				. JTEXT::_( 'Hrs' ) . '</option>' . "\n";
				echo '<option value="1440"'. ($JSConf->onlinetime == 1440 ? ' selected="selected"' : '') .'>24 '
				. JTEXT::_( 'Hrs' ) . '</option>' . "\n"; ?>
				</select>
			</td>
			<td width="100%">
				<?php
				echo $JSTemplate->jsToolTip( JTEXT::_( 'Timeout in minutes' ) ); ?>
			</td>
		</tr>
		<tr>
			<td nowrap="nowrap"><?php echo JTEXT::_( 'Startoption' ); ?></td>
			<td>
				<select name="startoption">
					<?php

					$select_html = '';
					require_once( dirname(__FILE__) .DS. 'statistics.common.php' );

					$JSStatisticsCommon = new js_JSStatisticsCommon( $JSConf );
					$MenuArrIdAndText = $JSStatisticsCommon->MenuArrIdAndText;
					foreach( $MenuArrIdAndText as $id => $description ) {
						$select_html .= '<option value="'.$id.'"'. ( $JSConf->startoption == $id ? ' selected="selected"' : '' ) . '>'
						. $description . '</option>' . "\n";
					}

					echo $select_html;

					?>
				</select>
			</td>
			<td>
				<?php
				echo $JSTemplate->jsToolTip( JTEXT::_( 'Startoption for JoomlaStats' ) ); ?>
			</td>
		</tr>
		<tr>
			<td nowrap="nowrap"><?php echo JTEXT::_( 'Start by day or month' ); ?></td>
			<td>
				<select name="startdayormonth">
					<?php
					echo "<option value='d'". ( $JSConf->startdayormonth == 'd' ? ' selected="selected"' : '' ) .'>'
					. JTEXT::_( 'Day' ) .'</option>' ."\n";
					echo "<option value='m'". ( $JSConf->startdayormonth == 'm' ? ' selected="selected"' : '' ) .'>'
					. JTEXT::_( 'Month' ) .'</option>' ."\n";
					?>
				</select>
			</td>
			<td>
				<?php
				echo $JSTemplate->jsToolTip( JTEXT::_( 'Select first view' ) ); ?>
			</td>
		</tr>
		<tr>
			<td nowrap="nowrap"><label for="enable_whois"><?php echo JTEXT::_( 'WHOIS Support' ); ?></label></td>
			<td>
				<input type="checkbox" name="enable_whois" id="enable_whois" <?php echo ( $JSConf->enable_whois ? ' checked="checked"' : '' ); ?> />
			</td>
			<td>
				<?php
				echo $JSTemplate->jsToolTip( JTEXT::_( 'Do a WHOIS query' ) ); ?>
			</td>
		</tr>
		<tr>
			<td nowrap="nowrap"><label for="enable_i18n"><?php echo JTEXT::_( 'I18n Support' ); ?></label></td>
			<td>
				<input type="checkbox" name="enable_i18n" id="enable_i18n" <?php echo ( $JSConf->enable_i18n ? ' checked="checked"' : '' ); ?> /></td>
			<td>
				<?php
				echo $JSTemplate->jsToolTip( JTEXT::_( 'Multiple translations as one' ) ); ?>
			</td>
		</tr>
		</table>
		<?php
	}

	/**
	 * This function show Common tab in Configuration Page
	 */
	function viewJSConfigurationPageTplPerformanceTab( $JSConf, $LastSummarizationDate ) {
		$JSTemplate = new js_JSTemplate();
		$html = '
		<div style="font-size: 1px;">&nbsp;</div><!-- This div is needed to show content of tab correctly in \'IE 7.0\' in \'j1.5.6 Legacy\'. Tested in: FF, IE, j1.0.15, j1.5.6 and works OK -->
		<table class="adminform" width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td nowrap="nowrap"><label for="include_summarized">' . JTEXT::_( 'Count including summarized data' ) . '</label></td>
			<td>
				<input type="checkbox" name="include_summarized" id="include_summarized"' . ( $JSConf->include_summarized ? ' checked="checked"' : '' ) . ' onclick="if (document.adminForm.include_summarized.checked == true) document.adminForm.show_summarized.checked = true; else document.adminForm.show_summarized.checked = false;" />
			</td>
			<td width="100%">
				' . $JSTemplate->jsToolTip( JTEXT::_( '<b>If off summarization works like purge option (summarized data not visible nor counted).<br/>If agreed statistics will be counted with summarized data.</b><br/><br/>Eg.<br/>if On:<br/><b>35 [21]</b> - current + summarized [summarized]<br/><br/>if Off:<br/><b>14</b> - only current' ) ) . '
				&nbsp;
				<em>
					' . ( $LastSummarizationDate ? JTEXT::_( 'Last summarization' ) . ':&nbsp;'	. $LastSummarizationDate : JTEXT::_( 'No summarized data availiable' ) ) . '
				</em>
			</td>
		</tr>
		<tr>
			<td nowrap="nowrap"><label for="show_summarized">' . JTEXT::_( 'Show summarized data' ) . '</label></td>
			<td>
				<input type="checkbox" disabled="disabled" name="show_summarized" id="show_summarized"' . ( $JSConf->show_summarized ? ' checked="checked"' : '' ) . ' />
			</td>
			<td>
				' . $JSTemplate->jsToolTip( JTEXT::_( 'This option apply only if <i>Count including summarized data</i> is On<br/><br/>It show/hide value in rectangle brackets.<br/><br/>Eg.<br/>show / hide<br/><b>35 [21]</b> / <b>35</b>' ) ) . '
			</td>
		</tr>
		</table>
		';
		
		return $html;
	}
}