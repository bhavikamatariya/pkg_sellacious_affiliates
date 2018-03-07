<?php
/**
 * @version     __DEPLOY_VERSION__
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.

defined('_JEXEC') or die;

/**
 * Profiles list controller class.
 *
 * @since 1.0.0
 */
class AffiliatesControllerUsers extends SellaciousControllerAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_AFFILIATES_USERS';

	/**
	 * Proxy for getModel.
	 *
	 * @since	1.6
	 */
	public function getModel($name = 'User', $prefix = 'AffiliatesModel', $config = null)
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}
}
