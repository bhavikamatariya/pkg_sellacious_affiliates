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
 * Banners list controller class
 *
 * @since   1.0.0
 */
class AffiliatesControllerBanners extends SellaciousControllerAdmin
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 *
	 * @since   1.0.0
	 */
	protected $text_prefix = 'COM_AFFILIATES_BANNERS';

	/**
	 * Proxy for getModel.
	 *
	 * @param string $name
	 * @param string $prefix
	 * @param array  $config
	 *
	 * @return object
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function getModel($name = 'Banner', $prefix = 'AffiliatesModel', $config = Array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}
}
