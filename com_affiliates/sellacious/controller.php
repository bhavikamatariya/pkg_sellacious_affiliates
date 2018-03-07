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
		$view = $this->input->get('view', 'affiliates');
		$this->input->set('view', $view);

		// Todo: All components should not require to have this check embedded. Move this check to application context.
		if (!$this->helper->core->isRegistered() || !$this->helper->core->isConfigured())
		{
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious'));

			return $this;
		}
		/*elseif (!$this->helper->access->check('affiliates.manage', null, 'com_affiliates'))
		{
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious'));
			$this->setMessage(JText::_('COM_AFFILIATES_ACCESS_NOT_ALLOWED'), 'warning');

			return $this;
		}*/

		return parent::display($cacheable, $urlparams);
	}
}
