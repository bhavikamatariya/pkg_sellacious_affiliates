<?php
/**
 * @version     __DEPLOY_VERSION__
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<tr>
	<th class="nowrap center" style="width:1%;">
		<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
	</th>
	<th></th>
	<th class="nowrap">
		<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
	</th>
	<?php if ($this->isAffiliate && !empty($this->affData->affid)): ?>
		<th class="nowrap">
			<?php echo JText::_('COM_AFFILIATES_BANNER_TITLE_HTML_LABEL'); ?>
		</th>
	<?php endif; ?>

	<th class="nowrap">
		<?php echo JHtml::_('searchtools.sort', 'COM_AFFILIATES_BANNER_TITLE_TOTAL_VIEWS_LABEL', 'a.total_views', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap">
		<?php echo JHtml::_('searchtools.sort', 'COM_AFFILIATES_BANNER_TITLE_TOTAL_CLICKS_LABEL', 'a.total_clicks', $listDirn, $listOrder); ?>
	</th>

	<th class="nowrap hidden-phone" style="width: 1%;">
		<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
	</th>
</tr>
