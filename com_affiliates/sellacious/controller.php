<?php
/**
 * @version     __DEPLOY_VERSION__
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

/**
 * @package   Sellacious
 *
 * @since   __DEPLOY_VERSION__
 */
class AffiliatesController extends SellaciousControllerBase
{
	/**
	 * Method to display a view.
	 *
	 * @param   bool   $cacheable  if true, the view output will be cached
	 * @param   mixed  $urlparams  An array of safe url parameters and their variable types,
	 *                             for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JControllerLegacy  This object to support chaining.
	 * @since   1.5
	 */
	public function display($cacheable = false, $urlparams = false)
	{
		$view = $this->input->get('view', 'dashboard');
		$this->input->set('view', $view);

		// Todo: All components should not require to have this check embedded. Move this check to application context.
		if (!$this->helper->core->isRegistered() || !$this->helper->core->isConfigured())
		{
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious'));

			return $this;
		}
		elseif (!$this->helper->access->check('affiliate.manage', null, 'com_sellacious'))
		{
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious'));
			$this->setMessage(JText::_('COM_AFFILIATES_ACCESS_NOT_ALLOWED'), 'warning');

			return $this;
		}
		elseif (!$this->canView())
		{
			$tmpl   = $this->input->get('tmpl', null);
			$suffix = !empty($tmpl) ? '&tmpl=' . $tmpl : '';
			$return = JRoute::_('index.php?option=com_sellacious' . $suffix, false);

			if ($tmpl != 'raw')
			{
				$this->setRedirect($return);

				JLog::add(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'), JLog::WARNING, 'jerror');
			}

			return $this;
		}

		return parent::display($cacheable, $urlparams);
	}

	/**
	 * Checks whether a user can see this view.
	 *
	 * @return  bool
	 *
	 * @since   1.6
	 */
	protected function canView()
	{
		$app    = JFactory::getApplication();
		$view   = $app->input->get('view', 'dashboard');

		/**
		 * Todo: Below we assume all (backend) singular views to be edit layout only.
		 * Todo: This may not be true. 'create' etc permissions need to be checked too.
		 */
		switch ($view)
		{
			case 'dashboard':
				$allow = true;
				break;
			case 'categories':
				$allow = $this->helper->access->check('affiliate.category.list');
				break;
			case 'category':
				$allow = $this->helper->access->check('affiliate.category.edit');
				break;
			case 'users':
				$allow = $this->helper->access->check('affiliate.user.list');
				break;
			case 'user':
				$allow = $this->helper->access->check('affiliate.user.edit');
				break;
			case 'banners':
				$allow = $this->helper->access->check('affiliate.banner.list');
				break;
			case 'banner':
				$allow = $this->helper->access->check('affiliate.banner.edit');
				break;
			case 'commissions':
				$allow = $this->helper->access->check('affiliate.commission.list');
				break;
			default:
				$allow = false;
		}

		return $allow;
	}
}
