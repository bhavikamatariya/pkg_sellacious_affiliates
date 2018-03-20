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

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious model.
 */
class AffiliatesModelRegister extends SellaciousModelAdmin
{
	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object $record A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canDelete($record)
	{
		return false;
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canEditState($record)
	{
		return false;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState()
	{
		parent::populateState();

		$app   = JFactory::getApplication();
		$catid = $app->getUserStateFromRequest('com_affiliates.edit.register.catid', 'catid', 0, 'int');

		if(empty($catid))
		{
			$this->setError(JText::_('COM_AFFILIATES_REGISTER_REGISTER_NO_CATEGORY_SELECTED'));
		}

		$this->state->set('register.catid', $catid);
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    Table name
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for table. Optional.
	 *
	 * @return  JTable
	 */
	public function getTable($type = 'User', $prefix = 'AffiliatesTable', $config = array())
	{
		JTable::addIncludePath(JPATH_SITE . '/components/com_affiliates/tables');

		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interrogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not
	 *
	 * @return  JForm|bool  A JForm object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		JForm::addFormPath(dirname(__DIR__) . '/models/forms');

		return parent::getForm($data, $loadData);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$app   = JFactory::getApplication();
		$data  = $app->getUserStateFromRequest("$this->option.edit.$this->name.data", 'jform', array(), 'array');
		$catid = $this->getState('register.catid');

		$data['category_id'] = $catid;

		$this->preprocessData('com_affiliates.' . $this->name, $data);

		return $data;
	}

	/**
	 * Override preprocessForm to load the sellacious plugin group instead of content.
	 *
	 * @param   JForm   $form   A form object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  Plugin group to load
	 *
	 * @return  void
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		$obj   = is_array($data) ? ArrayHelper::toObject($data) : $data;
		$catid = $this->getState('register.catid');

		$obj->category_id = $catid;

		$form->setFieldAttribute('password', 'required', 'true');
		$form->setFieldAttribute('password2', 'required', 'true');

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  int
	 * @throws  Exception
	 */
	public function save($data)
	{
		$app = JFactory::getApplication();

		// Extract variables
		$postData = $data;

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onContentBeforeSave', array('com_affiliates.register', $data, true));

		$profile  = ArrayHelper::getValue($postData, 'profile', array(), 'array');

		if (!preg_match('/^[a-zA-Z]+[a-zA-Z0-9._]+$/', $profile['affid']))
		{
			$app->enqueueMessage(JText::_('COM_AFFILIATES_PROFILE_USER_FIELD_AFFID_INVALID_MSG'), 'error');

			return false;
		}

		unset($postData['profile']);

		$postData['id']       = $this->getState($this->name . '.id');
		$postData['username'] = isset($postData['username']) ? $postData['username'] : $postData['email'];

		$profile['category_id'] = $this->getState('register.catid');

		try
		{
			$user = $this->helper->user->autoRegister(new Registry($postData));

			if (!($user instanceof JUser))
			{
				return false;
			}

			$this->setState($this->name . '.id', $user->id);
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Set up profile and all for the user just saved
		if ($profile)
		{
			$this->saveProfile($profile, $user->id);
		}

		$data['id'] = $user->id;

		$dispatcher->trigger('onContentAfterSave', array('com_affiliates.register', (object) $data, true));

		return $user->id;
	}

	/**
	 * Proxy method to create/update sellacious profile for a given joomla user
	 *
	 * @param   array  $data     Profile data to add
	 * @param   int    $user_id  User Id
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function saveProfile($data, $user_id)
	{
		// TODO: check for aff unique before user save.
		$profile = $this->getProfile($user_id);

		$data['user_id'] = $user_id;

		$data  = array_merge((array) $profile, $data);
		$table = $this->getTable('Profile');

		$table->bind($data);

		$table->check();
		$table->store();

		return true;
	}

	/**
	 * @param  $userId
	 *
	 * @return mixed
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getProfile($userId)
	{
		$query = $this->_db->getQuery(true);

		$query->select('*')
			->from('#__affiliates_profiles')
			->where('user_id = ' . $this->_db->q($userId));

		return $this->_db->setQuery($query)->loadObject();
	}
}
