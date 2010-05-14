<?php
defined('_JEXEC') or die('Restricted access');
$user 	=& JFactory::getUser();

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
			<?php
			echo $this->lists['sectionid'];
			echo $this->lists['catid'];
			?>
		</td>
	</tr>
</table>
	
	

<div>
	<table width="800">
		<tr>
			<th width="20"><?php echo JText::_( 'NUM' ); ?></th>
				
			<th align="left" width="700">
			<div style="text-align:left;margin:20px"><strong><?php echo JHTML::_('grid.sort',  'Title', 'a.title', $this->lists['order_Dir'], $this->lists['order'] ); ?></strong> - <em><?php echo JHTML::_('grid.sort',  'Filename', 'a.filename', $this->lists['order_Dir'], $this->lists['order'] ); ?></em> <small>(<?php echo JHTML::_('grid.sort',  'Section', 'section', $this->lists['order_Dir'], $this->lists['order'] ); ?>/<?php echo JHTML::_('grid.sort',  'Category', 'categorytitle', $this->lists['order_Dir'], $this->lists['order'] ); ?>)</small></div></th>
			
			<th width="100" nowrap="nowrap"><div style="text-align:left;margin:20px"><?php echo JHTML::_('grid.sort',  'Downloads', 'a.hits', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</div></th>
		</tr>
	
		<?php
		$k = 0;
		$color = 0;
		for ($i=0, $n=count( $this->items ); $i < $n; $i++)
		{
			$row = &$this->items[$i];
			
			$colors = array (
'#FF8080','#FF9980','#FFB380','#FFC080','#FFCC80','#FFD980','#FFE680','#FFF280','#FFFF80','#E6FF80',
'#CCFF80','#99FF80','#80FF80','#80FFC9','#80FFFF','#80C9FF','#809FFF','#9191FF','#AA80FF','#B580FF',
'#D580FF','#FF80FF','#FF80DF','#FF80B8');
			
			if ((int)$this->maxhit == 0) {
				$per = 0;
			} else {
				$per = round((int)$row->hits / (int)$this->maxhit * 700);
			}
			
			// Only text (description - no file)
			if ($row->textonly == 0) {
				echo '<tr>';
				echo '<td>'. $this->pagination->getRowOffset( $k ). '</td>';
				echo '<td>';
				echo '<div style="background:'.$colors[$color].' url(\''. JURI::base(true).'/components/com_phocadownload/assets/images/white-space.png'.'\') '.$per.'px 0px no-repeat;width:700px;padding:5px 5px;margin:5px 0px">';
			//	echo '<small style="color:#666666">['. $row->id .']</small>';
				echo '<strong  style="color:#666666;">'.$row->title .'</strong>';
				echo ' - <em>'. PhocaDownloadHelper::getTitleFromFilenameWithExt($row->filename) .'</em>';
				echo ' <small style="color:#666666">('. $row->sectiontitle .'/'. $row->categorytitle .')</small>';
				echo '</div>';
				echo '</td>';
				echo '<td align="center">'. $row->hits .'</td>';
				echo '</tr>';
			
				$color++;
				if ($color > 23) {
					$color = 0;
				}
				$k++;
			}
		}
		?>
		<tr>
			<td colspan="3" align="center"><div style="text-align:center;margin:20px auto"><?php echo $this->pagination->getListFooter(); ?></div></td>
		</tr>
	</table>
</div>

<input type="hidden" name="controller" value="phocadownloadstat" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
