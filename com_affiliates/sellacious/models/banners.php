<?php
/**
 * @version     __DEPLOY_VERSION__
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access.
defined('_JEXEC') or die;

/**
 * Methods supporting a list of Banners.
 *
 * @since  1.0.0
 */
class AffiliatesModelBanners extends SellaciousModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see    JController
	 * @since  1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'total_clicks', 'a.total_clicks',
				'total_views', 'a.total_views',
				'state', 'a.state',
			);
		}

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
	public function getTable($name = 'Banner', $prefix = 'AffiliatesTable', $options = array())
	{
		JTable::addIncludePath(JPATH_SITE . '/components/com_affiliates/tables');

		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.0.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'a.*'))
			->from($db->qn('#__affiliates_banners') . ' AS a')
			->group('a.id, a.title');

		// Filter the comments over the search string if set.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->q('%' . $db->escape($search, true) . '%');
				$query->where('a.title LIKE ' . $search);
			}
		}

		// Filter by published state
		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where('a.state = ' . (int) $state);
		}
		elseif ($state == '')
		{
			$query->where('a.state IN (0, 1)');
		}

		$ordering = $this->state->get('list.fullordering', 'a.id ASC');

		if (trim($ordering))
		{
			$query->order($db->escape($ordering));
		}

		return $query;
	}
}
