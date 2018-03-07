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
 * Methods supporting a list of Sellacious user records.
 *
 * @since __DEPLOY_VERSION__
 */
class AffiliatesModelUsers extends SellaciousModelList
{
	/**
	 * Constructor.
	 *
	 * @param  array  $config  An optional associative array of configuration settings.
	 *
	 * @see    JController
	 * @since  1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'u.id',
				'name', 'u.name',
				'username', 'u.username',
				'state', 'a.state',
				'ordering', 'a.ordering',
				'ac.title',
			);
		}

		parent::__construct($config);
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
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('u.id, u.name, u.username, u.email, u.block as state, u.activation')
			  ->from('#__users u')
			  ->group('u.id')

			  ->select('a.mobile, a.website, a.category_id, ac.title AS category_name')
			  ->join('INNER', '#__affiliates_profiles a ON a.user_id = u.id')
			  ->join('LEFT', '#__affiliates_categories ac ON ac.id = a.category_id');

		// Filter over the search string if set.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('u.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->q('%' . $db->escape($search, true) . '%');
				$query->where('(u.name LIKE ' . $search . ' OR u.username LIKE ' . $search . ')');
			}
		}

		// Filter by published state
		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where('u.block = ' . (int) ($state == 0));
		}

		// Filter by category(ies)
		if ($category = (int) $this->getState('filter.category'))
		{
			$query->where('a.category_id = ' . (int) $category);
		}

		// Add the list ordering clause.
		$ordering = $this->state->get('list.fullordering', 'a.ordering ASC');

		if (trim($ordering))
		{
			$query->order($db->escape($ordering));
		}

		return $query;
	}
}
