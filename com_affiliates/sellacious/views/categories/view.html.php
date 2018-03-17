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
 * View class for a list of categories.
 *
 * @since   1.0.0
 */
class AffiliatesViewCategories extends SellaciousViewList
{
	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $action_prefix = 'affiliate.category';

	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $view_item = 'category';

	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $view_list = 'categories';

	/**
	 * @var  bool
	 *
	 * @since   1.0.0
	 */
	protected $is_nested = true;

	/**
	 * @var  array
	 *
	 * @since   1.0.0
	 */
	protected $types = array();
}
