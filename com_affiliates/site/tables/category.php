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

use Joomla\Utilities\ArrayHelper;

/**
 * Category Table class
 *
 * @since __DEPLOY_VERSION__
 */
class AffiliatesTableCategory extends SellaciousTableNested
{
	/**
	 * Constructor
	 *
	 * @param  JDatabaseDriver  $db  A database connector object
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct(&$db)
	{
		$this->_array_fields = array(
			'commission',
			'params',
		);

		parent::__construct('#__affiliates_categories', 'id', $db);
	}

	/**
	 * Override to make sure to obey following -
	 * Default item cannot be unpublished
	 * Parent of default cannot be unpublished
	 *
	 * @param   int[]  $pks
	 * @param   int    $state
	 * @param   int    $userId
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		$pks = ArrayHelper::toInteger($pks);

		$query = $this->_db->getQuery(true);

		if ($state != 1 && count($pks))
		{
			$query->select('a.id')
				  ->from($this->_tbl . ' AS a')
				  ->where('a.id IN (' . implode(', ', $this->_db->q($pks)) . ')')
				  ->join('LEFT', $this->_tbl . ' AS b ON (a.lft <= b.lft AND b.rgt <= a.rgt)')
				  ->where('b.is_default = 1');

			$this->_db->setQuery($query);
			$exclude = $this->_db->loadColumn();

			if ($excluded = count($exclude))
			{
				throw new Exception(JText::sprintf('COM_SELLACIOUS_ERROR_DEFAULT_ITEM_STATE_CHANGE', (int)$excluded));
			}
		}

		return parent::publish($pks, $state, $userId);
	}

	/**
	 * Returns an array of conditions to meet for the uniqueness of the row, of course other than the primary key
	 *
	 * @return  array  key-value pairs to check the table row uniqueness against the row being checked
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected function getUniqueConditions()
	{
		$conditions   = array(
			'alias' => array(
				'alias'     => $this->get('alias'),
				'parent_id' => $this->get('parent_id'),
			)
		);

		return $conditions;
	}

	/**
	 * Get Custom error message for each uniqueness error
	 *
	 * @param   array  $uk_index  Array index/identifier of unique keys returned by getUniqueConditions
	 * @param   JTable $table     Table object with which conflicted
	 *
	 * @return bool|string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected function getUniqueError($uk_index, JTable $table)
	{
		if ($uk_index === 'alias')
		{
			return JText::sprintf('COM_SELLACIOUS_TABLE_UNIQUE_KEYS', $this->getName());
		}

		return false;
	}
}
