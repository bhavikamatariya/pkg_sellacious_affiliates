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
	<div class="left">
		<h2><?php echo JText::plural('COM_AFFILIATES_DASHBOARD_WELCOME_AFFILIATE', Jfactory::getUser()->name) ?></h2>
	</div>

	<?php
	if(!empty($this->affData))
	{
		if ($this->isAffiliate)
		{
			echo $this->loadTemplate('affiliate');
		}
		else
		{
			echo $this->loadTemplate('admin');
		}
	} ?>
</div>
<?php
