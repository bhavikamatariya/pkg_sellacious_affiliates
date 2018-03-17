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

use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious Category model.
 *
 * @since   1.0.0
 */
class AffiliatesModelCategory extends SellaciousModelAdmin
{
	/**
	 * @var AffiliatesHelperAffiliates|string
	 * @since __DEPLOY_VERSION__
	 */
	protected $affHelper = '';

	/**
	 * AffiliatesModelUser constructor.
	 *
	 * @param array $config
	 *
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function __construct(array $config = array())
	{
		$this->affHelper = AffiliatesHelperAffiliates::getInstance();

		parent::__construct($config);
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
	public function getTable($name = 'Category', $prefix = 'AffiliatesTable', $options = array())
	{
		JTable::addIncludePath(JPATH_SITE . '/components/com_affiliates/tables');

		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object $record A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function canDelete($record)
	{
		if ($count = $this->helper->category->countItems($record->id, false))
		{
			$this->setError(JText::sprintf('COM_SELLACIOUS_CATEGORY_HAS_ITEMS_DELETE_NOT_ALLOWED', $record->title, $count));

			return false;
		}

		return $this->helper->access->check('affiliate.category.delete');
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object $record A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   1.0.0
	 */
	protected function canEditState($record)
	{
		return $this->helper->access->check('affiliate.category.edit.state');
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function save($data)
	{
		// Initialise variables
		$dispatcher = JEventDispatcher::getInstance();

		/** @var AffiliatesTableCategory $table */
		$table = $this->getTable();
		$pk    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('sellacious');

		// Load the row if saving an existing category.
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		if (isset($data['affiliate_commission']))
		{
			$affiliateCommission = $data['affiliate_commission'];

			unset($data['affiliate_commission']);
		}

		// Set the new parent id if parent id not matched OR while New/Save as Copy .
		if ($table->get('parent_id') != $data['parent_id'] || $data['id'] == 0)
		{
			$table->setLocation($data['parent_id'], 'last-child');
		}

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		// Check the data.
		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		// Trigger the onBeforeSave event.
		$result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, $isNew));

		if (in_array(false, $result, true))
		{
			$this->setError($table->getError());

			return false;
		}

		// Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		if (isset($affiliateCommission))
		{
			$affCatid = $table->get('id');
			$commissions = $this->affHelper->getCommissionsByCategory($affCatid);

			foreach ($affiliateCommission as $productCatid => $commission)
			{
				$old = ArrayHelper::getValue($commissions, $productCatid);

				$query = $this->_db->getQuery(true);
				$zero  = trim($commission, '% ') == 0;

				// Insert if has value and not already exists
				if (!isset($old))
				{
					if (!$zero)
					{
						$query->insert('#__affiliates_category_commissions')
							->columns('affiliate_catid, product_catid, commission')
							->values(implode(', ', $this->_db->q(array($affCatid, $productCatid, $commission))));

						$this->_db->setQuery($query)->execute();
					}
				}
				else
				{
					// Delete if ZERO, and already exists
					if ($zero)
					{
						$query->delete('#__affiliates_category_commissions')
							->where('affiliate_catid = ' . $this->_db->q($affCatid))
							->where('product_catid = ' . $this->_db->q($productCatid));

						$this->_db->setQuery($query)->execute();
					}
					// Update only if modified
					elseif ($commission != $old)
					{
						$query->update('#__affiliates_category_commissions')
							->set('commission = ' . $this->_db->q($commission))
							->where('affiliate_catid = ' . $this->_db->q($affCatid))
							->where('product_catid = ' . $this->_db->q($productCatid));

						$this->_db->setQuery($query)->execute();
					}
				}
			}
		}

		// Trigger the onAfterSave event.
		$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $isNew));

		// Rebuild the path for the category
		if (!$table->rebuildPath($table->get('id')))
		{
			$this->setError($table->getError());

			return false;
		}

		// Rebuild the paths of the category's children
		if (!$table->rebuild($table->get('id'), $table->lft, $table->level, $table->get('path')))
		{
			$this->setError($table->getError());

			return false;
		}

		$this->setState($this->getName() . '.id', $table->get('id'));

		return true;
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
	 * Method to allow derived classes to preprocess the data.
	 *
	 * @param   string  $context  The context identifier.
	 * @param   mixed   &$data    The data to be processed. It gets altered directly.
	 * @param   string  $group    The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function preprocessData($context, &$data, $group = 'content')
	{
		// Get the dispatcher and load the plugins.
		$dispatcher = $this->helper->core->loadPlugins();

		// Trigger the data preparation event.
		$results = $dispatcher->trigger('onContentPrepareData', array($context, $data));

		// Check for errors encountered while preparing the data.
		if (count($results) > 0 && in_array(false, $results, true))
		{
			$this->setError($dispatcher->getError());
		}

		if (is_object($data))
		{
			$rates = $this->affHelper->getCommissionsByCategory($data->id);

			$data->affiliate_commission = $rates;

			$data = ArrayHelper::fromObject($data);
		}
	}
}
