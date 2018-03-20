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

$listOrder   = $this->escape($this->state->get('list.ordering'));
$listDirn    = $this->escape($this->state->get('list.direction'));
$helper      = SellaciousHelper::getInstance();
$autoApprove = $helper->config->get('auto_approved_affiliates_commissions');

$me      = JFactory::getUser();
$isAdmin = $me->authorise('core.admin');
?>
<tr>
	<th class="nowrap hidden-phone" style="width: 1%;">
		<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
	</th>

	<th class="nowrap">
		<?php echo JText::_('COM_AFFILIATES_COMMISSIONS_HEADING_AFFILIATE_NAME'); ?>
	</th>

	<th class="nowrap">
		<?php echo JText::_('COM_AFFILIATES_COMMISSIONS_HEADING_PRODUCT_TITLE'); ?>
	</th>

	<th class="nowrap">
		<?php echo JText::_('COM_AFFILIATES_COMMISSIONS_HEADING_BASE_PRICE'); ?>
	</th>

	<th class="nowrap">
		<?php echo JText::_('COM_AFFILIATES_COMMISSIONS_HEADING_COMMISSION'); ?>
	</th>

	<th class="nowrap">
		<?php echo JText::_('COM_AFFILIATES_COMMISSIONS_HEADING_COMMISSION_AMOUNT'); ?>
	</th>

	<?php if (!$autoApprove && $isAdmin): ?>
		<th></th>
	<?php endif; ?>
</tr>
