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
?>
<div>
	<?php if ($this->isAffiliate && !empty($this->affData->affid)): ?>
		<div>
			<h2><?php echo JText::plural('COM_AFFILIATES_DASHBOARD_WELCOME_AFFILIATE', Jfactory::getUser($this->affData->user_id)->name) ?></h2>
		</div>

		<label>
			<strong><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_URL') ?></strong>
			<input type="text" readonly value="<?php echo JUri::root(). '?affid=' . $this->affData->affid; ?>" class="inputbox" size="80">
		</label>

		<label>
			<strong><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_URL_GENERATOR') ?></strong>
			<input type="text" class="inputbox" id="url-generator" value="" size="80" placeholder="Paste any valid sellacious URL and create your affiliate URL.">
			<span class="red-note" id="error-note"><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_URL_INVALID') ?></span>
			<button type="button" id="generate-url"><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_URL_GO') ?></button>
		</label>
	<?php else: ?>
		<h4><?php echo JText::_('COM_AFFILIATES_DASHBOARD_AFFILIATE_USER_INVALID') ?></h4>
	<?php endif; ?>
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

<?php