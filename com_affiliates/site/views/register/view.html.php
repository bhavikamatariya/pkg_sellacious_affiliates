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
 * View to edit a sellacious user account
 */
class AffiliatesViewRegister extends SellaciousViewForm
{
	/** @var  string */
	protected $action_prefix = 'register';

	/** @var  string */
	protected $view_item = 'register';

	/** @var  string */
	protected $view_list = null;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl
	 *
	 * @return  mixed
	 */
	public function display($tpl = null)
	{
		$me = JFactory::getUser();

		if (!$me->guest)
		{
			$app = JFactory::getApplication();
			$app->redirect(JUri::base(), 'You are already registered.');
		}

		return parent::display($tpl);
	}

	/**
	 * Method to prepare data/view before rendering the display.
	 * Child classes can override this to alter view object before actual display is called.
	 *
	 * @return  void
	 */
	protected function prepareDisplay()
	{
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.6
	 */
	protected function addToolbar()
	{
	}
}
