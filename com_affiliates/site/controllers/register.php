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
 * User register controller class.
 *
 * @since   1.0.0
 */
class AffiliatesControllerRegister extends SellaciousControllerForm
{
	/**
	 * @var  string  The prefix to use with controller messages.
	 *
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_AFFILIATES_REGISTER';

	/**
	 * The URL view list variable.
	 *
	 * @var    string
	 *
	 * @since  12.2
	 */
	protected $view_list = 'register';

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name
	 * @param   string  $prefix
	 * @param   array   $config
	 *
	 * @return  JModelLegacy
	 *
	 * @since   1.0.0
	 */
	public function getModel($name = 'Register', $prefix = 'AffiliatesModel', $config = null)
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

	/**
	 * Method to check if you can add a new record. Extended classes can override this if necessary.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowAdd($data = array())
	{
		$params = JComponentHelper::getParams('com_users');
		$allow  = $params->get('allowUserRegistration');

		return $allow && JFactory::getUser()->guest;
	}

	/**
	 * Method to check if you can edit an existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		return false;
	}

	public function save($key = null, $urlVar = null)
	{
		$return = parent::save();

		if ($return)
		{
			$this->setMessage(JText::sprintf('COM_AFFILIATES_PROFILE_SAVE_SUCCESS'), 'success');
			$this->setRedirect(JRoute::_('index.php?option=com_affiliates&view=register', false));
		}
	}
}
