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

/** @var  AffiliatesViewUsers $this */
JHtml::_('stylesheet', 'com_sellacious/view.users.css', null, true);

$profile_type = $this->state->get('filter.profile_type');
$listOrder    = $this->escape($this->state->get('list.ordering'));
$listDirn     = $this->escape($this->state->get('list.direction'));
$ordering     = ($listOrder == 'a.ordering');
$saveOrder    = ($listOrder == 'a.ordering' && strtolower($listDirn) == 'asc');

$gc = $this->helper->currency->getGlobal('code_3');
$uc = $this->helper->currency->forUser(null, 'code_3');

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_affiliates&task=users.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'userList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

foreach ($this->items as $i => $item) :
	$canEdit   = $this->helper->access->check('affiliate.user.edit', $item->id);
	$canChange = $this->helper->access->check('affiliate.user.edit.state', $item->id);
	?>
	<tr role="row">
		<td class="order nowrap center hidden-phone">
			<?php if ($canChange) :
				$disableClassName = '';
				$disabledLabel	  = '';

				if (!$saveOrder) :
					$disabledLabel    = JText::_('JORDERINGDISABLED');
					$disableClassName = 'inactive tip-top';
				endif; ?>
				<span class="sortable-handler hasTooltip <?php echo $disableClassName; ?>" title="<?php echo $disabledLabel; ?>">
									<span class="icon-menu" aria-hidden="true"></span>
								</span>
				<input type="text" style="display:none" name="order[]" size="5"
					   value="<?php echo $item->ordering; ?>" class="width-20 text-area-order" title=""/>
			<?php else : ?>
				<span class="sortable-handler inactive">
									<span class="icon-menu" aria-hidden="true"></span>
								</span>
			<?php endif; ?>
		</td>
		<td class="nowrap center hidden-phone">
			<label>
				<input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
					   value="<?php echo $item->id ?>" onclick="Joomla.isChecked(this.checked);"
					<?php echo ($canEdit || $canChange) ? '' : ' disabled="disabled"' ?> />
				<span></span>
			</label>
		</td>
		<td class="nowrap center">
			<span class="btn-round"><?php echo JHtml::_('jgrid.published', !$item->state, $i, 'users.', $canChange);?></span>
		</td>
		<td class="nowrap">
			<?php if ($canEdit) : ?>
				<a href="<?php echo JRoute::_('index.php?option=com_affiliates&task=user.edit&id=' . (int)$item->id); ?>">
					<?php echo $this->escape($item->name); ?></a>
			<?php else : ?>
				<?php echo $this->escape($item->name); ?>
			<?php endif; ?>
		</td>

		<td class="nowrap">
			<?php echo $this->escape($item->username); ?>
		</td>

		<td class="nowrap">
			<?php echo $this->escape($item->email); ?>
		</td>

		<td class="nowrap">
			<?php echo $this->escape($item->mobile); ?>
		</td>

		<td class="nowrap">
			<?php echo $this->escape($item->category_name); ?>
		</td>

		<td class="center hidden-phone">
			<span><?php echo (int) $item->id; ?></span>
		</td>
	</tr>
<?php
endforeach;
