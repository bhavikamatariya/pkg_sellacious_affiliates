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

/**
 * View to edit
 *
 * @since __DEPLOY_VERSION__
 */
class AffiliatesViewBanner extends SellaciousViewForm
{
	/**
	 * @var  string
	 * @since __DEPLOY_VERSION__
	 */
	protected $action_prefix = 'banner';

	/**
	 * @var  string
	 * @since __DEPLOY_VERSION__
	 */
	protected $view_item = 'banner';

	/**
	 * @var  string
	 * @since __DEPLOY_VERSION__
	 */
	protected $view_list = 'banners';
}
