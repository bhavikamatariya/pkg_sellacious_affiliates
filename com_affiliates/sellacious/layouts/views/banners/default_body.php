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

/** @var  AffiliatesViewBanners $this */

foreach ($this->items as $i => $item) :
	$canEdit   = $this->helper->access->check('banner.edit', $item->id);
	$canChange = $this->helper->access->check('banner.edit.state', $item->id);
	?>
	<tr role="row">
		<td class="nowrap center">
			<span class="btn-round"><?php echo JHtml::_('jgrid.published', $item->state, $i, 'banners.', $canChange);?></span>
		</td>

		<td class="nowrap">
			<?php if ($canEdit) : ?>
				<a href="<?php echo JRoute::_('index.php?option=com_affiliates&task=banner.edit&id=' . (int)$item->id); ?>">
					<?php echo $this->escape($item->title); ?></a>
			<?php else : ?>
				<?php echo $this->escape($item->title); ?>
			<?php endif; ?>
		</td>

		<?php if ($this->isAffiliate && !empty($this->affData->affid)): ?>
			<td class="nowrap">
				<textarea readonly  cols="90" rows="1"><a href="<?php echo $item->url . '?affid=' . (string)$this->affData->affid . '&affbannerid=' . (int)$item->id; ?>"><img src="<?php echo JUri::root() . '/images/banners/' . $item->image ?>"></a></textarea>
			</td>
		<?php endif; ?>

		<td class="center hidden-phone">
			<span><?php echo (int) $item->id; ?></span>
		</td>
	</tr>
<?php
endforeach;
