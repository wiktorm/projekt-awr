<?php
defined('_JEXEC') or die('Restricted access');
//Ordering allowed ?
$ordering = ($this->lists['order'] == 'a.ordering');
JHTML::_('behavior.tooltip');
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
			</td>
		</tr>
	</table>

	<div id="editcell">
		<table class="adminlist">
			<thead>
				<tr>
					<th width="5"><?php echo JText::_( 'NUM' ); ?></th>
					<th width="5"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" /></th>
					
					<th width="15%" nowrap="nowrap"><?php echo JHTML::_('grid.sort',  'User', 'uname', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					
					<th width="15%" nowrap="nowrap"><?php echo JHTML::_('grid.sort',  'Username', 'username', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					
					<th class="title" width="25%"><?php echo JHTML::_('grid.sort',  'Title', 'filetitle', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					<th width="25%" nowrap="nowrap"><?php echo JHTML::_('grid.sort',  'Filename', 'filename', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					
					<th width="5%" nowrap="nowrap"><?php echo JHTML::_('grid.sort',  'Downloads', 'a.count', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					
					<th width="15%" nowrap="nowrap"><?php echo JHTML::_('grid.sort',  'Date', 'a.date', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					
				</th>
					<th width="1%" nowrap="nowrap"><?php echo JHTML::_('grid.sort',  'ID', 'a.id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="12"><?php echo $this->pagination->getListFooter(); ?></td>
				</tr>
			</tfoot>
			<tbody>
				<?php
				$k = 0;
				for ($i=0, $n=count( $this->items ); $i < $n; $i++) {
					$row 	= &$this->items[$i];
					$checked 	= JHTML::_('grid.checkedout', $row, $i );
					
					$linkUser = 'index.php?option=com_users&view=user&task=edit&cid[]='.(int)$row->userid;
					$linkFile = 'index.php?option=com_phocadownload&controller=phocadownload&task=edit&cid[]='.(int)$row->fileid;
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td><?php echo $this->pagination->getRowOffset( $i ); ?></td>
						<td><?php echo $checked; ?></td>
						
						<td><?php if ($row->uname =='') {
								echo JText::_('Guest');
							} else {
								echo '<a href="'.$linkUser.'">'. $row->uname.'</a>';
							} ?></td>
						<td><?php if ($row->username =='') {
								echo JText::_('Guest');
							} else {
								echo '<a href="'.$linkUser.'">'. $row->username.'</a>';
							} ?></td>
							
						<td><?php echo '<a href="'.$linkFile.'">'. $row->filetitle.'</a>';?></td>
						<td><?php echo '<a href="'.$linkFile.'">'. $row->filename.'</a>';?></td>
						
						<td align="center"><?php echo $row->count;?></td>
						<td align="center"><?php echo $row->date;?></td>
						<td align="center"><?php echo $row->id; ?></td>
					</tr>
					<?php
					$k = 1 - $k;
				}
			?>
			</tbody>
		</table>
	</div>
<?php $cid	= JRequest::getVar( 'cid', 0, 'get', 'array' ); ?>	
<input type="hidden" name="idfile" value="<?php echo $cid[0] ?>" />
<input type="hidden" name="controller" value="phocadownloadut" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>