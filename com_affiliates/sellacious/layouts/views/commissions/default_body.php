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

JHtml::_('stylesheet', 'com_sellacious/view.products.css', null, true);

/** @var  AffiliatesViewCommissions $this */
$helper      = SellaciousHelper::getInstance();
$currency    = $helper->currency->current('code_3');
$autoApprove = $helper->config->get('auto_approved_affiliates_commissions');
$me          = JFactory::getUser();
$isAdmin     = $me->authorise('core.admin');

foreach ($this->items as $i => $item) : ?>
	<tr role="row">
		<td class="center hidden-phone">
			<span><?php echo (int) $item->id; ?></span>
		</td>

		<td class="nowrap">
			<?php echo $this->escape($item->affiliate_name); ?>
		</td>

		<td class="nowrap">
			<?php
			echo $item->product_title;
			if ($item->product_categories)
			{
				echo '<br>Category: <label class="label capsule text-normal">' . $item->product_categories . '</label>';
			}
			if ($item->variant_title)
			{
				echo '<br>Variant: <label class="label capsule text-normal">' . $item->variant_title . '</label>';
			}
			if ($item->seller_name)
			{
				echo '<br>Seller: <label class="label capsule text-normal">' . $item->seller_name . '</label>';
			}
			?>
		</td>

		<td class="nowrap">
			<?php echo $this->escape($item->basic_price) . ' ' . $currency; ?>
		</td>

		<td class="nowrap">
			<?php echo $this->escape($item->affiliate_commission); ?>
		</td>

		<td class="nowrap">
			<?php echo $this->escape($item->commission_amount) . ' ' . $currency; ?>
		</td>

		<?php if (!$autoApprove && $isAdmin): ?>
			<td align="center">
				<?php
				$disabled = '';
				$label    = 'Approve';
				$url      = JUri::base() . 'index.php?option=com_affiliates&view=commissions&task=commissions.approveCommission&commission_id=' . (int) $item->id . '&aff_uid=' . (int) $item->aff_uid;

				if ($item->is_approved)
				{
					$disabled = 'disabled="disabled"';
					$label    = 'Already Approved';
					$url      = 'javascript::void(0)';
				}
				?>

				<a href="<?php echo $url ?>">
					<button type="button" class="hasTooltip btn btn-primary" <?php echo $disabled ?> data-title="Approve this commission."><?php echo $label; ?></button>
				</a>
			</td>
		<?php endif; ?>
	</tr>
<?php
endforeach;
