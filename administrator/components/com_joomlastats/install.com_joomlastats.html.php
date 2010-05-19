<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */


if( !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}

require_once( dirname(__FILE__) .DS. 'template.html.php' );

$JSTemplate = new js_JSTemplate();

$introduce_msg  = '<img src="'. JURI::base(true) . '/components/com_joomlastats/images/icon-48-js_js-logo.png" width="48" height="48" alt="JoomlaStats" title="JoomlaStats" /><br clear="all" />';
$introduce_msg .= JTEXT::_( 'Thank you for using JoomlaStats' ); ?>
<div style="text-align: left;">
	<div style="width:95%; border: 1px solid #EFEFEF; margin:5px auto 5px auto; padding:5px;">
		<table style="width: 100%; padding: 0px; border-width: 0px; border-collapse: collapse; /*not working in IE 6.0, 7.0 use cellspacing=0 */ border-spacing: 0px; /* no difference */" cellspacing="0">
		<tr>
			<td style="padding: 0px; text-align: left;"><?php echo $introduce_msg; ?></td>
			<td style="padding: 0px; text-align: right; vertical-align: top; font-weight: bold;">
				JoomlaStats version:&nbsp;'<?php echo $this->JSConf->JSVersion; ?>'
			</td>
		</tr>
		</table>	
	
		<div>
			<?php
			if( count( $StatusTData->errorMsg ) > 0 ) {
				echo $JSTemplate->generateMsgColorInfoFrame( 'error', $StatusTData->errorMsg, '' );
			}

			echo $JSTemplate->generateMsgColorInfoFrame(
				'warning',
				$StatusTData->warningMsg,
				JTEXT::_( 'It seems that JoomlaStats is working correctly.') . ' '
				. JTEXT::_( 'No warnings at the moment' )
			);
			echo $JSTemplate->generateMsgColorInfoFrame(
				'recommend',
				$StatusTData->recommendationMsg,
				JTEXT::_( 'It seems that JoomlaStats is working correctly.') . ' '
				. JTEXT::_( 'No recommendations at this moment.' )
			);
			echo $JSTemplate->generateMsgColorInfoFrame(
				'info',
				$StatusTData->infoMsg,
				JTEXT::_( 'It seems that JoomlaStats is working correctly.') . ' '
				. JTEXT::_( 'No informations at this moment.' )
			); ?>
		</div>
	</div>
</div>