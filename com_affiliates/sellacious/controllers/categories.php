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
 * Categories list controller class
 *
 * @since   1.0.0
 */
class AffiliatesControllerCategories extends SellaciousControllerAdmin
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 *
	 * @since   1.0.0
	 */
	protected $text_prefix = 'COM_AFFILIATES_CATEGORIES';

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
	public function getModel($name = 'Category', $prefix = 'AffiliatesModel', $config = Array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

	/**
	 * Rebuild the nested set tree.
	 *
	 * @return  bool  False on failure or error, true on success.
	 *
	 * @throws Exception
	 *
	 * @since   1.0.0
	 */
	public function rebuild()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$allowed = $this->helper->access->check('category.rebuild');

		$this->setRedirect(JRoute::_('index.php?option=com_affiliates&view=categories', false));

		if (!$allowed)
		{
			$this->setMessage(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'), 'error');

			return false;
		}

		/** @var  AffiliatesModelCategory  $model */
		$model = $this->getModel();

		if ($model->rebuild())
		{
			$this->setMessage(JText::_($this->text_prefix . '_REBUILD_SUCCESS'));

			return true;
		}
		else
		{
			$this->setMessage(JText::sprintf($this->text_prefix . '_REBUILD_FAILURE', $model->getError()), 'error');

			return false;
		}
	}
}
