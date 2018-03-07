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

$profile_type = $this->state->get('filter.profile_type');
?>
<tr role="row">
	<th width="1%" class="nowrap center hidden-phone">
		<?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
	</th>
	<th style="width: 10px;">
		<label class="checkbox style-0">
			<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip checkbox style-3"
				title="<?php echo JHtml::tooltipText('JGLOBAL_CHECK_ALL') ?>" onclick="Joomla.checkAll(this);" />
			<span></span>
		</label>
	</th>
	<th class="nowrap" width="5%">
		<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
	</th>

	<th class="nowrap">
		<?php echo JHtml::_('searchtools.sort', 'COM_AFFILIATES_PROFILE_HEADING_NAME', 'u.name', $listDirn, $listOrder); ?>
	</th>

	<th class="nowrap">
		<?php echo JHtml::_('searchtools.sort', 'COM_AFFILIATES_PROFILE_HEADING_USERNAME', 'u.username', $listDirn, $listOrder); ?>
	</th>

	<th class="nowrap">
		<?php echo JHtml::_('searchtools.sort', 'COM_AFFILIATES_PROFILE_HEADING_EMAIL', 'u.email', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap">
		<?php echo JHtml::_('searchtools.sort', 'COM_AFFILIATES_PROFILE_HEADING_MOBILE', 'a.mobile', $listDirn, $listOrder); ?>
	</th>

	<th class="nowrap">
		<?php echo JHtml::_('searchtools.sort', 'Category', 'cc.title', $listDirn, $listOrder); ?>
	</th>

	<th class="nowrap hidden-phone" style="width: 1%;">
		<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'u.id', $listDirn, $listOrder); ?>
	</th>
</tr>
