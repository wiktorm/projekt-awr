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
 * Object of hold templates to 'tools options'
 */
class js_JSToolsTpl
{
	/**
	 * Outputs the html code for page maintenance
	 *
	 * @param integer $pr_sum
	 */
	function viewJSToolsPageTpl( $pr_sum, $LastSummarizationDate ) {
	
		jimport('joomla.html.pane');
		$pane =& JPane::getInstance( 'tabs' );

		$JSTemplate = new js_JSTemplate();
		$JSTemplate->jsLoadToolTip();

		echo $JSTemplate->generateBeginingOfAdminForm();
		echo JTEXT::_( 'Tools options - DETAILED DESCRIPTION' );
		echo '<br/><br/>';
?>
		<table width="100%" border="0" cellpadding="2" cellspacing="0" class="adminForm">
		<tr>
			<td>
			<?php
				echo $pane->startPane( 'js_maintenance_pane' );

				echo $pane->startPanel( JTEXT::_( 'Maintenance' ), 'maintenance' );
					require_once( dirname(__FILE__) .DS. 'maintenance.html.php' );
					$JSConf = new js_JSConf();
					$JSMaintenanceTpl = new js_JSMaintenanceTpl();
					echo $JSMaintenanceTpl->getMaintenanceTabTpl();
				echo $pane->endPanel();

				echo $pane->startPanel( JTEXT::_( 'Export' ), 'export' );
				include_once( dirname( __FILE__ ) .DS. 'export.html.php' );
				echo $pane->endPanel();

				/* working code - temporary removed due to release */
				echo $pane->startPanel( JTEXT::_( 'WHOIS/TLD' ), 'tld' );
				include_once( dirname( __FILE__ ) .DS. 'tld.php' );
				$JSTld = new js_JSTld();
				echo $JSTld->getTldTab();
				echo $pane->endPanel();
				
				/* no working options on backup tab, removed due to release
				echo $pane->startPanel( JTEXT::_( 'Backup' ), 'backup' );
				include_once( dirname( __FILE__ ) .DS. 'backup.php' );
				$JSBackup = new js_JSBackup();
				echo $JSBackup->getBackupTab();
				echo $pane->endPanel();
				*/
				
				echo $pane->endPane();
			?>
			</td>
		</tr>
		</table>
		<?php
		echo $JSTemplate->generateEndOfAdminForm();
	}

	/**
	 * shows messages after installing JoomlaStats
	 *
	 * @param string $warningMsg
	 * @param string $recommendationMsg
	 * @param string $infoMsg
	 */
	function viewJSUninstallPageTpl( $warningMsg, $recommendationMsg, $infoMsg ) {

		$JSTemplate = new js_JSTemplate();

		echo '<div style="text-align: left;"><!-- needed by j1.0.15 -->';
?>
		<div class="jsInfo" style="width:90%; background-color: #FFFFF6; border-top: 2px solid #FEFF5F; border-bottom: 2px solid #FEFF5F; margin: 5px auto 5px auto; padding 5px">
			<div style="margin:5px"><?php echo JTEXT::_( 'Click above button for a full uninstall' ); ?></div>
		</div>
		<?php
		echo $JSTemplate->generateMsgColorInfoFrame( 'warning', $warningMsg, '' );
		echo $JSTemplate->generateMsgColorInfoFrame( 'recommend', $recommendationMsg, '' );
		//echo $JSTemplate->generateMsgColorInfoFrame( 'info', $infoMsg, '' );
		echo $JSTemplate->generateAdminForm();
		echo '</div><!-- needed by j1.0.15 -->';
	}

	/**
	 * This template is used when unistallation process fail
	 * or when unistallation was OK but user must manualy uninstall JS in standard Joomla uninstaller
	 * (2008-09-07 Curently it works in this way)
	 *
	 * @param string $errorMsg
	 * @param string $noErrorMsgText
	 * @param string $warningMsg
	 * @param string $noWarningMsgText
	 * @param string $recommendationMsg
	 * @param string $noRecommendationMsgText
	 */
	function doJSUninstallFailTpl( $errorMsg, $noErrorMsgText, $warningMsg, $noWarningMsgText, $recommendationMsg, $noRecommendationMsgText ) {

		$JSTemplate = new js_JSTemplate();

		echo '<div style="text-align: left;"><!-- needed by j1.0.15 -->';

		if ( $noErrorMsgText != '' ) {
			echo $JSTemplate->generateMsgColorInfoFrame( 'error', $errorMsg, $noErrorMsgText );
		}

		if( $noWarningMsgText != '' ) {
			echo $JSTemplate->generateMsgColorInfoFrame( 'warning', $warningMsg, $noWarningMsgText );
		}

		if( $noRecommendationMsgText != '' ) {
			echo $JSTemplate->generateMsgColorInfoFrame( 'recommend', $recommendationMsg, $noRecommendationMsgText );
		}

		echo $JSTemplate->generateAdminForm();
		echo '</div><!-- needed by j1.0.15 -->';
	}

	/**
	 * shows a 'summarize' page
	 *
	 * @todo function not finished - it has been only moved from admin.joomlastats.html.php file
	 * @param array $warningMsg
	 * @param array $recommendationMsg
	 * @param array $infoMsg
	 */
	function getJSSummarizePageTpl( $FilterDate, $oldest_entry_date, $entries_to_summarize, $LastSummarizationDate ) {

		$JSTemplate = new js_JSTemplate(); 

		$html  = '';
		
		$html .= '<div style="text-align: left;"><!-- needed by j1.0.15 -->';
		$html .= $JSTemplate->generateBeginingOfAdminForm();
		$html .= JTEXT::_('Last summarization').': '.$LastSummarizationDate.'<br/>';
		$html .= '<br/>';
		$html .= JTEXT::_('Summarize process help');
		$html .= '<br/><br/>';
		$html .= JTEXT::_('Summarize entries older than').': <br/>';
		$html .= $FilterDate->getHtmlDateFilterCode();
		$html .= '<br/><br/>';
		$html .= JTEXT::sprintf('From %s to %s, %s entries will be summarized', $oldest_entry_date, $FilterDate->getDateStr(), $entries_to_summarize );
		$html .= '<br/><br/>';
		$html .= '<b>'.JTEXT::_('Notice').':</b><br />';
		$html .= JTEXT::_('If summarization fail, shorten time period and try again.').' ';
		$html .= '<a href="http://www.joomlastats.org/entry/summarize_problems.php" target="_blank">'. JTEXT::_('More info') .'</a>'.'<br/>';
		$html .= '<br/>';
		$html .= $JSTemplate->generateEndOfAdminForm();
		$html .= '</div><!-- needed by j1.0.15 -->';
		
		return $html;
	}

}