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
 * View class for a list of Banners.
 *
 * @since   1.0.0
 */
class AffiliatesViewCommissions extends SellaciousViewList
{
	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $action_prefix = 'affiliate.commissions';

	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $view_item = '';

	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $view_list = 'commissions';

	/**
	 * @var  bool
	 *
	 * @since   1.0.0
	 */
	protected $is_nested = false;

	/**
	 * @param null $tpl
	 *
	 * @return mixed
	 *
	 * @since version
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		return parent::display($tpl);
	}

	protected function addToolbar()
	{
		$this->setPageTitle();
	}

}
