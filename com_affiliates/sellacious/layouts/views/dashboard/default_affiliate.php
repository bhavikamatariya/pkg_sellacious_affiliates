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
<div class="row">
	<div class="col-sm-12">
		<?php if (!$autoApprove): ?>
			<div class="col-sm-2 text-center">
				<h3><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_TOTAL_TENTATIVE_COMMISSIONS') ?></h3>
				<span><?php echo $this->affData->tentative_commission . ' ' . $currency; ?></span>
			</div>
		<?php endif; ?>
		<div class="col-sm-2 text-center">
			<h3><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_TOTAL_APPROVED_COMMISSIONS') ?></h3>
			<span><?php echo $this->affData->approved_commission . ' ' . $currency; ?></span>
		</div>
		<div class="col-sm-2 text-center">
			<h3><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_TOTAL_VISITORS') ?></h3>
			<span><?php echo $this->affData->total_visits; ?></span>
		</div>
		<div class="col-sm-2 text-center">
			<h3><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_TOTAL_REGISTERED') ?></h3>
			<span><?php echo $this->affData->total_registered; ?></span>
		</div>
		<div class="col-sm-2 text-center">
			<h3><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_TOTAL_SALES') ?></h3>
			<span><?php echo $this->affData->total_sales; ?></span>
		</div>
	</div>
</div>

<br><br>
<div class="row">
	<div class="col-sm-12">
		<div class="col-sm-2">
			<strong><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_URL') ?></strong>
		</div>
		<div class="col-sm-8">
			<input type="text" readonly value="<?php echo JUri::root(). '?affid=' . $this->affData->affid; ?>" class="inputbox" size="80">
		</div>
	</div>
	<div class="col-sm-12">
		<div class="col-sm-2">
			<strong><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_URL_GENERATOR') ?></strong>
		</div>
		<div class="col-sm-8">
			<input type="text" class="inputbox" id="url-generator" value="" size="80" placeholder="Paste any valid sellacious URL and create your affiliate URL.">
			<button type="button" id="generate-url"><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_URL_GO') ?></button>
			<span class="red-note" id="error-note"><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_URL_INVALID') ?></span>
		</div>
	</div>
</div>

<script type="text/javascript">
	jQuery(document).ready(function ($) {
		$('#error-note').hide();
		$("#generate-url").click(function(){
			var url = $('#url-generator').val();
			url_validate = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
			if(!url_validate.test(url)){
				$('#error-note').show();
			}
			else{
				$('#error-note').hide();
				if(url.indexOf('?') >= 0) {
					var new_url = url + "&affid=<?php echo $this->affData->affid ?>";
				}else{
					var new_url =  url + "?affid=<?php echo $this->affData->affid ?>";
				}

				$('#url-generator').val(new_url);
			}
		});
	});
</script>

<div>
	<?php if (!empty($this->affData->userCommission)): ?>
		<h2><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_PRODUCT_CATEGORY_COMMISSIONS') ?></h2>
		<table class="table">
			<tr>
				<th><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_CATEGORY_PRODUCT_CATEGORY_TITLE'); ?></th>
				<th><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_CATEGORY_PRODUCT_CATEGORY_COMMISSION'); ?></th>
			</tr>
			<?php foreach ($this->affData->userCommission as $ucmsn):
				$level = ($ucmsn->level > 1) ? ('|' . str_repeat('&mdash;', $ucmsn->level - 1) . ' ') : '';?>
				<tr>
					<td><?php echo $level . $ucmsn->category_title; ?></td>
					<td><?php echo $ucmsn->commission; ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
	<?php endif; ?>
	<?php if (!empty($this->affData->catCommission)): ?>
		<h2><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_CATEGORY_PRODUCT_CATEGORY_COMMISSIONS') ?></h2>
		<table class="table">
			<tr>
				<th><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_CATEGORY_PRODUCT_CATEGORY_TITLE'); ?></th>
				<th><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_CATEGORY_PRODUCT_CATEGORY_COMMISSION'); ?></th>
			</tr>
			<?php foreach ($this->affData->catCommission as $ucmsn):
				$level = ($ucmsn->level > 1) ? ('|' . str_repeat('&mdash;', $ucmsn->level - 1) . ' ') : '';?>
				<tr>
					<td><?php echo $level . $ucmsn->category_title; ?></td>
					<td><?php echo $ucmsn->commission; ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
	<?php endif; ?>
</div>
<?php
