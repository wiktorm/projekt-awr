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

/**
 * Object of this class contain templates used by 'Exclude Manager' pages
 */
class js_JSExcludeTpl
{
	/**
	 * HTML output of exclude manager
	 *
	 * //old name JoomlaStats_Engine::listIpAddresses();
	 *
	 * @param array $rows
	 * @param array $pageination
	 * @param string $search
	 */
	function viewJSExcludeManagerPageTpl( &$rows, $pagination, $search ) {
		global $mainframe;

		$JSTemplate = new js_JSTemplate();

		echo $JSTemplate->generateBeginingOfAdminForm( 'js_view_exclude' );
		echo '<input type="hidden" name="boxchecked" value="0" />' . "\n";

		$Filter = '&nbsp;&nbsp;' . JTEXT::_( 'Search' ) . ':&nbsp;' . '<input type="text" name="search" id="search" value="'.$search.'" class="inputbox" size="30" onChange="submitbutton(\'js_view_exclude\');" />';
		echo $Filter;
?>
		<table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist">
			<thead>
			<tr>
				<th width="2%" class="title">#</th>
				<th width="3%" class="title">
					<input type="checkbox" name="toggle" id="toggle" value="" onClick="checkAll(<?php echo count($rows); ?>);" />
				</th>
				<th width="20%" class="title"><?php echo JTEXT::_( 'IP-Address' ); ?></th>
				<th width="40%" class="title"><?php echo JTEXT::_( 'NS-Lookup' ); ?></th>
				<th width="15%" class="title"><?php echo JTEXT::_( 'OS' ); ?></th>
				<th width="15%" class="title"><?php echo JTEXT::_( 'Browser' ); ?></th>
				<th width="5%" class="title"><?php echo JTEXT::_( 'Exclude' ); ?></th>
			</tr>
			</thead>
				<?php
				$k = 0;
				$n = count($rows);

				for ($i = 0; $i < $n; $i++) {
					$row	=& $rows[$i];
					$img	= $row->exclude ? 'tick.png' : 'publish_x.png';
					$task	= $row->exclude ? 'js_do_ip_include' : 'js_do_ip_exclude';
					$alt	= $row->exclude ? JTEXT::_( 'Click to include' ) : JTEXT::_( 'Click to exclude' );
					?>
				<tr class="row<?php echo $k; ?>">
					<td><?php echo $i + 1 + $pagination->limitstart;?></td>
					<td>
						<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->id; ?>" onClick="isChecked(this.checked);" />
					</td>
					<td>
						<a href="http://<?php echo $row->ip; ?>" target="_blank" title="<?php echo JTEXT::_( 'Click opens new window' ); ?>"><?php echo $row->ip; ?></a>
					</td>
					<td><?php echo $row->nslookup; ?></td>
					<td><?php echo $row->system; ?></td>
					<td><?php echo $row->browser; ?></td>
					<td width="10%" align="center">
					<a href="javascript:void(0);" onClick="return listItemTask('cb<?php echo $i;?>','<?php echo $task;?>');" title="<?php echo $alt; ?>"><img src="images/<?php echo $img;?>" border="0" alt="<?php echo $alt; ?>" /></a>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			} ?>
			<tfoot>
			<tr>
				<td colspan="7"><?php echo $pagination->getListFooter(); ?></td>
			</tr>
			</tfoot>
		</table>
		<?php

		echo $JSTemplate->generateEndOfAdminForm();
		//echo '</div><!-- needed by j1.0.15 -->';//can not be used for lists! (in FF mosPageNav generate footer that is left justified instead of center)
	}
}
