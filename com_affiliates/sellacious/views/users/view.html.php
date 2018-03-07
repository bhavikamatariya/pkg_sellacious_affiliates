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
 * View class for a list of Sellacious users.
 *
 * @since __DEPLOY_VERSION__
 */
class AffiliatesViewUsers extends SellaciousViewList
{
	/**
	 * @var  string
	 * @since __DEPLOY_VERSION__
	 */
	protected $action_prefix = 'user';

	/**
	 * @var  string
	 * @since __DEPLOY_VERSION__
	 */
	protected $view_item = 'user';

	/**
	 * @var  string
	 * @since __DEPLOY_VERSION__
	 */
	protected $view_list = 'users';

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.6
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');

		if ($this->helper->access->check($this->action_prefix . '.create'))
		{
			JToolBarHelper::addNew($this->view_item . '.add', 'JTOOLBAR_NEW');
		}

		if (count($this->items))
		{
			if ($this->helper->access->check($this->action_prefix . '.edit') || $this->helper->access->check($this->action_prefix . '.edit.own'))
			{
				JToolBarHelper::editList($this->view_item . '.edit', 'JTOOLBAR_EDIT');
			}

			$filter_state = $state->get('filter.state');

			if ($this->helper->access->check($this->action_prefix . '.edit.state'))
			{
				if (!is_numeric($filter_state) || $filter_state != '1')
				{
					JToolBarHelper::custom($this->view_list . '.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				}

				if (!is_numeric($filter_state) || $filter_state != '0')
				{
					JToolBarHelper::custom($this->view_list . '.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
				}
			}

			// We can allow direct 'delete' implicitly for if so permitted, *warning* User table does not support trash.
			if ($this->helper->access->check($this->action_prefix . '.delete'))
			{
				JToolBarHelper::custom($this->view_list . '.delete', 'delete.png', 'delete.png', 'JTOOLBAR_DELETE', true);
			}
		}

		if ($this->is_nested && $this->helper->access->check('user.list'))
		{
			JToolBarHelper::custom($this->view_list . '.rebuild', 'refresh.png', 'refresh_f2.png', 'JTOOLBAR_REBUILD', false);
		}

		$this->setPageTitle();
	}
}
