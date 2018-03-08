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

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious model.
 *
 * @since __DEPLOY_VERSION__
 */
class AffiliatesModelUser extends SellaciousModelAdmin
{
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		$app = JFactory::getApplication();

		// Check the session for previously entered form data.
		$data = $app->getUserStateFromRequest("$this->option.edit.$this->name.data", 'jform', array(), 'array');

		if (empty($data))
		{
			// Load user info
			$data = $this->getItem();

			// Remove password
			unset($data->password);

			// Add profile info
			$profile = $this->getProfile($data->get('id'));
			$profile->commission = $this->getCommissions($data->get('id'));
			$data->set('profile', $profile);
		}

		$this->preprocessData('com_affiliates.' . $this->name, $data);

		return $data;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 * @throws  Exception
	 *
	 * @since   12.2
	 */
	public function delete(&$pks)
	{
		$app = JFactory::getApplication();
		$me  = JFactory::getUser();

		if (in_array($me->id, $pks))
		{
			$pks = array_diff($pks, array($me->id));

			$app->enqueueMessage(JText::plural('COM_SELLACIOUS_USERS_OWN_PROFILE_DELETE_WARNING', $me->name), 'warning');
		}

		if ($delete = parent::delete($pks))
		{
			$tables = array(
				array('#__affiliates_profiles', 'user_id'),
				array('#__affiliates_user_commissions', 'aff_uid', array('aff_uid > 0')),
			);

			$queries = array();

			$uid   = $this->_db->getQuery(true)->select('id')->from('#__users');
			$query = $this->_db->getQuery(true);

			foreach ($tables as $table)
			{
				$query->clear()->delete($table[0])->where($table[1] . ' NOT IN (' . $uid . ')');

				if (isset($table[2]))
				{
					$query->where($table[2]);
				}

				$queries[] = (string) $query;
			}
		}

		return $delete;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   12.2
	 * @throws  Exception
	 */
	public function getTable($name = 'User', $prefix = 'AffiliatesTable', $options = array())
	{
		JTable::addIncludePath(JPATH_SITE . '/components/com_affiliates/tables');

		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Override preprocessForm to load the sellacious plugin group instead of content.
	 *
	 * @param   JForm   $form   A form object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  Plugin group to load
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		$obj     = is_array($data) ? ArrayHelper::toObject($data) : $data;
		$user_id = isset($obj->id) ? $obj->id : 0;

		$me = JFactory::getUser();

		if ($me->id == $user_id)
		{
			$form->setFieldAttribute('block', 'type', 'hidden');
			$form->setFieldAttribute('block', 'filter', 'unset');
		}

		if ($user_id)
		{
			$form->setFieldAttribute('affid', 'readonly', 'true', 'profile');
		}

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  int
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function save($data)
	{
		$app = JFactory::getApplication();

		// Extract variables
		$profile = ArrayHelper::getValue($data, 'profile', null);

		if (!preg_match('/^[a-zA-Z]+[a-zA-Z0-9._]+$/', $profile['affid']))
		{
			$app->enqueueMessage(JText::_('COM_AFFILIATES_PROFILE_USER_FIELD_AFFID_INVALID_MSG'), 'error');

			return false;
		}

		unset($data['profile']);

		$isNew = empty($data['id']);
		$user  = $this->saveUser($data);

		if (!($user instanceof JUser))
		{
			return false;
		}

		// Set up profile and all for the user just saved
		$profile['state'] = $user->block ? 0 : 1;

		$this->saveProfile($profile, $user->id);

		if (!empty($profile['category_id']))
		{
			if (isset($profile['commission']))
			{
				$affCommissions = $profile['commission'];

				unset($profile['commission']);
			}

			if (!empty($affCommissions))
			{
				$affUid = $user->id;
				$commissions = $this->getCommissions($affUid);

				foreach ($affCommissions as $productCatid => $commission)
				{
					$old = ArrayHelper::getValue($commissions, $productCatid);

					$query = $this->_db->getQuery(true);
					$zero  = trim($commission, '% ') == 0;

					// Insert if has value and not already exists
					if (!isset($old))
					{
						if (!$zero)
						{
							$query->insert('#__affiliates_user_commissions')
								->columns('aff_uid, product_catid, commission')
								->values(implode(', ', $this->_db->q(array($affUid, $productCatid, $commission))));

							$this->_db->setQuery($query)->execute();
						}
					}
					else
					{
						// Delete if ZERO, and already exists
						if ($zero)
						{
							$query->delete('#__affiliates_user_commissions')
								->where('aff_uid = ' . $this->_db->q($affUid))
								->where('product_catid = ' . $this->_db->q($productCatid));

							$this->_db->setQuery($query)->execute();
						}
						// Update only if modified
						elseif ($commission != $old)
						{
							$query->update('#__affiliates_user_commissions')
								->set('commission = ' . $this->_db->q($commission))
								->where('aff_uid = ' . $this->_db->q($affUid))
								->where('product_catid = ' . $this->_db->q($productCatid));

							$this->_db->setQuery($query)->execute();
						}
					}
				}
			}
		}

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onContentAfterSave', array('com_affiliates.user', $user, $isNew));

		return $user->id;
	}

	/**
	 * @param   array  $data  The data to save for related Joomla user account.
	 *
	 * @return  JUser|bool  The user id of the user account on success, false otherwise
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function saveUser($data)
	{
		$pk = !empty($data['id']) ? $data['id'] : (int) $this->getState($this->name . '.id');

		if ($pk == 0)
		{
			$app = JFactory::getApplication();
			$registry = new Registry($data);
			$user     = $this->helper->user->autoRegister($registry);

			// Set global edit id in case rest of the process fails, page should load with new user id
			// Joomla bug in Registry, array key does not update. Fixed in later version of J! 3.4.x
			$state       = $app->getUserState("com_affiliates.edit.$this->name.data");
			$state['id'] = $user->id;

			$this->setState("$this->name.id", $user->id);
			$app->setUserState("com_affiliates.edit.$this->name.data", $state);
			$app->setUserState("com_affiliates.edit.$this->name.id", (int) $user->id);
		}
		else
		{
			$user = new JUser($data['id']);

			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('sellacious');

			// Bind the data.
			if (!$user->bind($data))
			{
				$this->setError($user->getError());

				return false;
			}

			// Trigger the onAfterSave event.
			$dispatcher->trigger('onBeforeSaveUser', array($this->option . '.' . $this->name, &$user, false));

			// Store the data.
			if (!$user->save())
			{
				$this->setError($user->getError());

				return false;
			}

			// Trigger the onAfterSave event.
			$dispatcher->trigger('onAfterSaveUser', array($this->option . '.' . $this->name, &$user, false));
		}

		return $user;
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   1.0.0
	 */
	protected function canDelete($record)
	{
		return $this->helper->access->check('user.delete');
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interrogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not
	 *
	 * @return  JForm|bool  A JForm object on success, false on failure
	 * @since __DEPLOY_VERSION__
	 */
	public function getForm($data = array(), $loadData = true)
	{
		JForm::addFormPath(dirname(__DIR__) . '/models/forms');

		return parent::getForm($data, $loadData);
	}


	/**
	 * Saves the manually set order of records.
	 *
	 * @param   array    $pks    An array of primary key ids.
	 * @param   integer  $order  +1 or -1
	 *
	 * @return  boolean|\JException  Boolean true on success, false on failure, or \JException if no items are selected
	 * @throws  Exception
	 *
	 * @since   1.6
	 */
	public function saveorder($pks = array(), $order = null)
	{
		$table          = $this->getTable('Profile');
		$tableClassName = get_class($table);
		$contentType    = new \JUcmType;
		$type           = $contentType->getTypeByTable($tableClassName);
		$tagsObserver   = $table->getObserverOfClass('\JTableObserverTags');
		$conditions     = array();

		if (empty($pks))
		{
			return \JError::raiseWarning(500, \JText::_($this->text_prefix . '_ERROR_NO_ITEMS_SELECTED'));
		}

		$orderingField = $table->getColumnAlias('ordering');

		// Update ordering values
		foreach ($pks as $i => $pk)
		{
			$table->load(array('user_id' => (int) $pk));

			// Access checks.
			if (!$this->canEditState($table))
			{
				// Prune items that you can't change.
				unset($pks[$i]);
				\JLog::add(\JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), \JLog::WARNING, 'jerror');
			}
			elseif ($table->$orderingField != $order[$i])
			{
				$table->$orderingField = $order[$i];

				if ($type)
				{
					$this->createTagsHelper($tagsObserver, $type, $pk, $type->type_alias, $table);
				}

				if (!$table->store())
				{
					$this->setError($table->getError());

					return false;
				}

				// Remember to reorder within position and client_id
				$condition = $this->getReorderConditions($table);
				$found     = false;

				foreach ($conditions as $cond)
				{
					if ($cond[1] == $condition)
					{
						$found = true;
						break;
					}
				}

				if (!$found)
				{
					$key          = $table->getKeyName();
					$conditions[] = array($table->$key, $condition);
				}
			}
		}

		// Execute reorder for each category.
		if ($conditions)
		{
			foreach ($conditions as $cond)
			{
				$table->load($cond[0]);
				$table->reorder($cond[1]);
			}
		}
		else
		{
			$table->reorder();
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array   &$pks  A list of the primary keys to change.
	 * @param   integer $value The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0.0
	 */
	public function publish(&$pks, $value = 1)
	{
		$app = JFactory::getApplication();
		$me  = JFactory::getUser();

		if (in_array($me->id, $pks))
		{
			$pks = array_diff($pks, array($me->id));

			$app->enqueueMessage(JText::plural('COM_SELLACIOUS_USERS_OWN_PROFILE_UNPUBLISH_WARNING', $me->name), 'warning');
		}

		$published = parent::publish($pks, $value);

		if ($published && count($pks))
		{
			$query = $this->_db->getQuery(true);
			$query->update('#__sellacious_profiles')
				->set('state = ' . ($value == 1 ? 1 : 0))
				->where('user_id IN (' . implode(', ', $pks) . ')');

			try
			{
				$this->_db->setQuery($query)->execute();
			}
			catch (Exception $e)
			{
				// Ignore
			}
		}

		return $published;
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
	 * Fetch seller commissions for the given seller for each product category maps
	 *
	 * @param   int  $affUid  Seller user id
	 *
	 * @return  array  Commissions for each product category
	 *
	 * @since   1.5.0
	 */
	public function getCommissions($affUid)
	{
		$query = $this->_db->getQuery(true);

		$query->select('product_catid, commission')
			->from('#__affiliates_user_commissions')
			->where('aff_uid = ' . $this->_db->q($affUid));

		$items  = $this->_db->setQuery($query)->loadObjectList();
		$result = ArrayHelper::getColumn((array) $items, 'commission', 'product_catid');

		return $result;
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
