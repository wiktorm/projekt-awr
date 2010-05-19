<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */


if( !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}

require_once( dirname( __FILE__ ) .DS. 'template.html.php' );

$JSTemplate = new js_JSTemplate();

$introduce_msg		= JTEXT::_( 'Thank you for using JoomlaStats' );
$last_summ_info		= ( $StatusTData->LastSummarizationDate ) ? $StatusTData->LastSummarizationDate : JTEXT::_( 'Not yet processed' );
$infoTable			= '';
?>

<div style="text-align: left;">
	<div style="width:95%; border: 1px solid #EFEFEF; margin:5px auto 5px auto; padding:5px;">
		<table style="width: 100%; padding: 0px; border-width: 0px; border-collapse: collapse; /*not working in IE 6.0, 7.0 use cellspacing=0 */ border-spacing: 0px; /* no difference */" cellspacing="0">
		<tr>
			<td style="padding: 0px; text-align: left;"><?php echo $introduce_msg; ?></td>
			<td style="padding: 0px; text-align: right; vertical-align: top; font-weight: bold;">
				JoomlaStats version:&nbsp;
				<!-- JoomlaStats build version: '<?php echo $this->JSConf->JSVersion; ?>' -->
				<?php
					if (strpos($this->JSConf->JSVersion, ' ') === false) {
						//for release, cut the build number (last digits) from version number
						$pos = strrpos($this->JSConf->JSVersion, '.');
						if ($pos === false) {
							//somethings goes wrong, echo all
							echo $this->JSConf->JSVersion;
						} else {
							echo substr($this->JSConf->JSVersion, 0, $pos);
						}
					} else {
						echo $this->JSConf->JSVersion;
					}
				?>
			</td>
		</tr>
		</table>	
		<?php
		echo $JSTemplate->generateMsgColorInfoFrame(
			'warning',
			$StatusTData->warningMsg,
			JTEXT::_( 'It seems that JoomlaStats is working correctly.' ) . ' ' . JTEXT::_( 'No further messages at this moment.' )
		);
		echo $JSTemplate->generateMsgColorInfoFrame(
			'recommend',
			$StatusTData->recommendationMsg,
			JTEXT::_( 'It seems that JoomlaStats is working correctly.' ) .' '. JTEXT::_( 'No recommendations at this moment.' )
		);

	 	if( $StatusTData->showDbInfoTable == true ) {
		 	$infoTable .= '
			<br/>
			<table width="550" align="center" style="border: 1px solid #CCCCCC; background-color: #F5F5F5;">
			<tr>
				<td colspan="4" style="font-weight:bold; text-align:center;">' . JTEXT::_( 'Summary of existing database' ) . '<hr /></td>
			<tr>
				<td width="220">' . JTEXT::_( 'Spider/Bots' ) . '</td>
				<td width="150" align="left">'.$StatusTData->totalbots.'</td>
				<td width="220" align="left">' . JTEXT::_( 'Visited pages' ) . '</td>
				<td width="150" align="left">'.$StatusTData->totalpages.'</td>
			</tr>
			<tr>
				<td>' . JTEXT::_( 'Browser types' ) . '</td>
				<td align="left">'.$StatusTData->totalbrowser.'</td>
				<td align="left">' . JTEXT::_( 'Page hits' ) . '</td>
				<td align="left">'.$StatusTData->totalpagerequest.' [ '.$StatusTData->bu_totalpagerequest.' ] *</td>
			</tr>
			<tr>
				<td>' . JTEXT::_( 'Search engines' ) . '</td>
				<td align="left">'.$StatusTData->totalse.'</td>
				<td align="left">' . JTEXT::_( 'Referrer' ) . '</td>
				<td align="left">'.$StatusTData->totalpagereferrer.'</td>
			</tr>
			<tr>
				<td>' . JTEXT::_( 'Visitor OS' ) . '</td>
				<td align="left">'.$StatusTData->totalsys.'</td>
				<td align="left">' . JTEXT::_( 'Visitor' ) . '</td>
				<td align="left">'.$StatusTData->totalvisits.'</td>
			</tr>
			<tr>
				<td>' . JTEXT::_( 'Toplevel Domains' ) . '</td>
				<td align="left">'.$StatusTData->totaltld.'</td>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td>' . JTEXT::_( 'IP Adresses' ) . '</td>
				<td align="left">'.$StatusTData->totalipc.'</td>
				<td align="left">' . JTEXT::_( 'Last summarization' ) .'</td>
				<td>'.$last_summ_info.'</td>
			</tr>
			<tr>
				<td valign="top">&nbsp;</td>
				<td colspan="3" style="text-align:left; color:#FF0000;">' . JTEXT::_( 'Summarized data' ) . '</td>
			</tr>
			</table>
			<br />
			';
	 	}

		echo $JSTemplate->generateMsgColorInfoFrame(
			'info',
			$StatusTData->infoMsg,
			JTEXT::_( 'It seems that JoomlaStats is working correctly.' ) . ' ' . JTEXT::_( 'No informations at this moment.' ),
			 $infoTable
		); ?>
	</div>
	<div style="clear:both; margin-top:15px"></div>
	<div style="text-align:center; color:#9F9F9F; font-size:0.9em">
		&copy;2003-<?php echo js_gmdate( 'Y' ); ?> JoomlaStats Team - All rights reserved.<br />
		<a href="http://www.JoomlaStats.org" target="_blank" title="Visit Homepage">JoomlaStats</a>
		is Free Software released under the GNU/GPL License.<br />
	</div>
	<?php echo $JSTemplate->generateAdminForm(); ?>
</div>