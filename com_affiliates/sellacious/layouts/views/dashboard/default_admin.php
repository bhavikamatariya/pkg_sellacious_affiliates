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

/** @var $this AffiliatesViewDashboard */
JHtml::_('jquery.framework');

$helper = SellaciousHelper::getInstance();
$currency     = $helper->currency->current('code_3');
$autoApprove = $helper->config->get('auto_approved_affiliates_commissions');
?>
<div><br>
	<h4>Affiliate Wise stats</h4><br>
	<table class="table">
		<tr>
			<th><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_USERNAME') ?></th>
			<th><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_TOTAL_VISITORS') ?></th>
			<th><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_TOTAL_REGISTERED') ?></th>
			<th><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_TOTAL_SALES') ?></th>
			<?php if (!$autoApprove): ?>
				<th><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_TOTAL_TENTATIVE_COMMISSIONS') ?></th>
			<?php endif; ?>
			<th><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_TOTAL_APPROVED_COMMISSIONS') ?></th>
		</tr>
		<?php foreach ($this->affData as $affData) : ?>
			<tr>
				<td>
					<a href="<?php echo JUri::base() . 'index.php?option=com_affiliates&view=commissions&affuid=' . (int) $affData->user_id ?>">
						<?php echo JFactory::getUser($affData->user_id)->name; ?>
					</a>
				</td>
				<td><?php echo $affData->total_visits; ?></td>
				<td><?php echo $affData->total_registered; ?></td>
				<td><?php echo $affData->total_sales; ?></td>
				<?php if (!$autoApprove): ?>
					<td><?php echo $affData->tentative_commission . ' ' . $currency; ?></td>
				<?php endif; ?>
				<td><?php echo $affData->approved_commission . ' ' . $currency; ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
</div>
<?php
