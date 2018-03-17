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

/** @var  AffiliatesViewBanners $this */
$helper = SellaciousHelper::getInstance();

foreach ($this->items as $i => $item) :
	$canEdit   = $this->helper->access->check('affiliate.banner.edit', $item->id);
	$canChange = $this->helper->access->check('affiliate.banner.edit.state', $item->id);
	?>
	<tr role="row">
		<td class="nowrap center">
			<span class="btn-round"><?php echo JHtml::_('jgrid.published', $item->state, $i, 'banners.', $canChange);?></span>
		</td>

		<td style="width:50px; padding:5px;" class="image-box">
			<?php $image = $helper->media->getImage('affiliate.banner.image', $item->id, true); ?>
			<img class="image-large" src="<?php echo $image ?>">
			<img class="image-small" style="max-width: 200px; max-height: 100px" src="<?php echo $image ?>">
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
				<?php $imageSrc = JUri::root() . 'index.php?option=com_affiliates&view=banners&layout=image&affid=' . (string)$this->affData->affid . '&affbannerid=' . (int)$item->id; ?>
				<textarea readonly  cols="60" rows="3"><a target="_blank" href="<?php echo JUri::root(). '?affid=' . (string)$this->affData->affid . '&affbannerid=' . (int)$item->id . '&bannerflag=1'; ?>"><img src="<?php echo $imageSrc ?>"></a></textarea>
			</td>
		<?php endif; ?>

		<td class="nowrap">
			<?php echo $this->escape($item->total_views); ?>
		</td>

		<td class="nowrap">
			<?php echo $this->escape($item->total_clicks); ?>
		</td>

		<td class="center hidden-phone">
			<span><?php echo (int) $item->id; ?></span>
		</td>
	</tr>
<?php
endforeach;
