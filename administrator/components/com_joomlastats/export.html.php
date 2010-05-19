<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */


if( !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}


/**
 * jstats_ipaddresses
 * jstats_keywords
 * jstats_page_request
 * jstats_page_request_c
 * jstats_pages
 * jstats_referrer
 * jstats_visits
 */
require_once( dirname(__FILE__) .DS. 'export.php' );
require_once( dirname(__FILE__) .DS. 'template.html.php' );

$js_ExportConst = new js_ExportConst();
$tableHashesAndNames	= $js_ExportConst->getTableHashesAndNames();
$tableDefaultHash		= $js_ExportConst->getTableDefaultHash();

?>
<div style="font-size: 1px;">&nbsp;</div><!-- This div is needed to show content of tab correctly in 'IE 7.0' in 'j1.5.6 Legacy'. Tested in: FF, IE, j1.0.15, j1.5.6 and works OK -->
<table class="adminform" width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td colspan="2">
			<?php 
				echo '<b>'.JTEXT::_( 'Export data to *.csv file' ).'</b><br/>';
				echo JTEXT::_( 'Export data to *.csv file - DETAILED DESCRIPTION' );
			?>
		</td>
	</tr>
	<tr>
		<td width="220">
			<table border="0">
			<?php
				foreach ($tableHashesAndNames as $tableHAN) { ?>
					<tr>
						<td width="200">
							<label for="js_table_hash_id<?php echo $tableHAN['hash']; ?>">
								<?php echo $tableHAN['native_name']; ?>
							</label>
						</td>
						<td>
							<input type="radio" name="js_table_hash" id="js_table_hash_id<?php echo $tableHAN['hash']; ?>" value="<?php echo $tableHAN['hash']; ?>"<?php echo ( $tableHAN['hash'] == $tableDefaultHash ) ? ' checked="checked"' : ''; ?> />
						</td>
					</tr>
					<?php
				} ?>
			<tr>
				<td colspan="2" style="text-align: center;">
					<input type="button" name="export2csv" style="width:165px" value="<?php echo JTEXT::_( 'CSV data export' ); ?>" onclick="if(confirm('<?php echo JTEXT::_( 'Shall the selected table be exported' ); ?>'))submitbutton('js_export_do_js2csv');" />
				</td>
			</tr>
			</table>
		</td>
	</tr>
</table>