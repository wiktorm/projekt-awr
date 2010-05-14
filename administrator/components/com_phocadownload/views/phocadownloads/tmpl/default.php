<?php
defined('_JEXEC') or die('Restricted access');
$db			= &JFactory::getDBO();
$user 		= &JFactory::getUser();
$config		= &JFactory::getConfig();
$nullDate 	= $db->getNullDate();
$now		= &JFactory::getDate();

//Ordering allowed ?
$ordering = ($this->lists['order'] == 'a.ordering');

JHTML::_('behavior.tooltip');

if (isset($this->tmpl['notapproved']->count) && (int)$this->tmpl['notapproved']->count > 0 ) {
	echo '<div class="notapproved">'.JText::_('PHOCADOWNLOAD_NOT_APPROVED_FILES_IN_DOWNLOAD').': '.(int)$this->tmpl['notapproved']->count.'</div>';
}
?>

<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm">
	<table>
		<tr>
			<td align="left" width="100%"><?php echo JText::_( 'Filter' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
				<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
			</td>
			<td nowrap="nowrap">
				<?php
				echo $this->lists['sectionid'];
				echo $this->lists['catid'];
				echo $this->lists['state'];
				?>
			</td>
		</tr>
	</table>

	<div id="editcell">
		<table class="adminlist">
			<thead>
				<tr>
					<th width="5"><?php echo JText::_( 'NUM' ); ?></th>
					<th width="5"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" /></th>
					<th class="title" width="40%"><?php echo JHTML::_('grid.sort',  'Title', 'a.title', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					<th width="20%" nowrap="nowrap"><?php echo JHTML::_('grid.sort',  'Filename', 'a.filename', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					
					<th width="10%" nowrap="nowrap"><?php echo JHTML::_('grid.sort',  'Downloads', 'a.hits', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					
					<th width="5" nowrap="nowrap"><?php echo JText::_('User Statistics'); ?></th>
					<th width="5"><?php echo JHTML::_('grid.sort',  'PHOCADOWNLOAD_OWNER', 'ownerid', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
					
					<th width="5" nowrap="nowrap"><?php echo JHTML::_('grid.sort',  'Published', 'a.published', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					
					
					
					<th width="5" nowrap="nowrap"><?php echo JHTML::_('grid.sort',   JText::_('PHOCADOWNLOAD_APPROVED'), 'a.approved', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					
					<th width="5" nowrap="nowrap"><?php echo JText::_('PHOCADOWNLOAD_ACTIVE'); ?></th>
					
					<th width="15%" nowrap="nowrap">
						<?php echo JHTML::_('grid.sort',  'Order', 'a.ordering', $this->lists['order_Dir'], $this->lists['order'] ); ?>
						<?php echo JHTML::_('grid.order',  $this->items ); ?></th>
					<th width="15%"  class="title">
						<?php echo JHTML::_('grid.sort',  'Category', 'categorytitle', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
						<th width="15%"  class="title">
						<?php echo JHTML::_('grid.sort',  'Section', 'section', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
						<th width="7%">
					<?php //echo JHTML::_('grid.sort',   'Access', 'groupname', @$lists['order_Dir'], @$lists['order'] );
					echo JTEXT::_('Access');

					?>
				</th>
					<th width="1%" nowrap="nowrap"><?php echo JHTML::_('grid.sort',  'ID', 'a.id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="15"><?php echo $this->pagination->getListFooter(); ?></td>
				</tr>
			</tfoot>
			<tbody>
				<?php
				$k = 0;
				for ($i=0, $n=count( $this->items ); $i < $n; $i++)
				{
					$row 			= &$this->items[$i];
					
					// Only text (description - no file)
					if ($row->textonly == 1) {
						$link 			= JRoute::_( 'index.php?option=com_phocadownload&controller=phocadownload&task=text&cid[]='. $row->id );
					} else {
						$link 			= JRoute::_( 'index.php?option=com_phocadownload&controller=phocadownload&task=edit&cid[]='. $row->id );
					}
					$linkUserStatistics = JRoute::_( 'index.php?option=com_phocadownload&view=phocadownloadut&cid[]='. $row->id );
					$checked 	= JHTML::_('grid.checkedout', $row, $i );
					$published 	= JHTML::_('grid.published', $row, $i );
					$approved 	= PhocaDownloadHelper::approved( $row, $i );
					$access 	= JHTML::_('grid.access',   $row, $i );
					$linkCat 	= JRoute::_('index.php?option=com_phocadownload&controller=phocadownloadcat&task=edit&cid[]='. $row->catid );
					$linkSec 	= JRoute::_('index.php?option=com_phocadownload&controller=phocadownloadsec&task=edit&cid[]='. $row->sectionid );
					$linkUser	= JRoute::_('index.php?option=com_users&view=user&task=edit&cid[]='.(int)$row->ownerid);
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td><?php echo $this->pagination->getRowOffset( $i ); ?></td>
					<td><?php echo $checked; ?></td>
					
					<td>
						<?php
						if (  JTable::isCheckedOut($this->user->get ('id'), $row->checked_out ) ) {
							echo $row->title;
						} else {
						?>
							<a href="<?php echo $link; ?>" title="<?php echo JText::_( 'Edit Phoca Download' ); ?>">
								<?php echo $row->title; ?></a>
						<?php
						}
						?>
					</td>
					<td><?php echo $row->filename;?></td>
					
					<td align="center"><?php echo $row->hits;?></td>
					
					<td align="center"><a href="<?php echo $linkUserStatistics; ?>"><?php echo  JHTML::_('image.site', 'icon-16-user-stat.png', 'components/com_phocadownload/assets/images/', NULL, NULL, 'User Statistics'); ?></a></td>
					
					
					<td align="center"><a href="<?php echo $linkUser; ?>" title="<?php echo JText::_( 'PHOCADOWNLOAD_USER_EDIT' ); ?>"><?php echo $row->ownername; ?></a></td>
					
					<td align="center"><?php echo $published;?></td>
					<td align="center"><?php echo $approved;?></td>
					
					<?php
					
					$publish_up 	= &JFactory::getDate($row->publish_up);
					$publish_down 	= &JFactory::getDate($row->publish_down);
					$publish_up->setOffset($config->getValue('config.offset'));
					$publish_down->setOffset($config->getValue('config.offset'));
					if ( $now->toUnix() <= $publish_up->toUnix() ) {
						$text = JText::_( 'PHOCADOWNLOAD_PENDING' );
					} else if ( ( $now->toUnix() <= $publish_down->toUnix() || $row->publish_down == $nullDate ) ) {
						$text = JText::_( 'PHOCADOWNLOAD_ACTIVE' );
					} else if ( $now->toUnix() > $publish_down->toUnix() ) {
						$text = JText::_( 'PHOCADOWNLOAD_EXPIRED' );
					}
					
					$times = '';
					if (isset($row->publish_up)) {
						if ($row->publish_up == $nullDate) {
							$times .= JText::_( 'Start: Always' );
						} else {
							$times .= JText::_( 'Start' ) .": ". $publish_up->toFormat();
						}
					}
					if (isset($row->publish_down)) {
						if ($row->publish_down == $nullDate) {
							$times .= "<br />". JText::_( 'Finish: No Expiry' );
						} else {
							$times .= "<br />". JText::_( 'Finish' ) .": ". $publish_down->toFormat();
						}
					}
					
					if ( $times ) {
						echo '<td align="center">'
							.'<span class="editlinktip hasTip" title="'. JText::_( 'PHOCADOWNLOAD_PUBLISH_INFORMATION' ).'::'. $times.'">'
							.'<a href="javascript:void(0);" >'. $text.'</a></span>'
							.'</td>';
					}
					
					?><td class="order">
						<span><?php echo $this->pagination->orderUpIcon( $i, ($row->catid == @$this->items[$i-1]->catid),'orderup', 'Move Up', $ordering ); ?></span>
						<span><?php echo $this->pagination->orderDownIcon( $i, $n, ($row->catid == @$this->items[$i+1]->catid), 'orderdown', 'Move Down', $ordering ); ?></span>
					<?php $disabled = $ordering ?  '' : 'disabled="disabled"'; ?>
						<input type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
					</td>
					<td><a href="<?php echo $linkCat; ?>" title="<?php echo JText::_( 'Edit Category' ); ?>"><?php echo $row->categorytitle; ?></a>
					</td>
					
					<td><a href="<?php echo $linkSec; ?>" title="<?php echo JText::_( 'Edit Category' ); ?>"><?php echo $row->sectiontitle; ?></a>
					</td>
					<td align="center"><?php echo $access;?></td>  
					<td align="center"><?php echo $row->id; ?></td>
				</tr>
				<?php
				$k = 1 - $k;
				}
			?>
			</tbody>
		</table>
	</div>

	
<input type="hidden" name="controller" value="phocadownload" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>