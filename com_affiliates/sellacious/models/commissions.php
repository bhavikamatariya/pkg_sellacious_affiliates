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
class AffiliatesModelCommissions extends SellaciousModelList
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
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.0.0
	 */
	protected function getListQuery()
	{
		$app = JFactory::getApplication();
		$affUId = $app->input->getInt('affuid');
		$me = JFactory::getUser();

		$isAdmin = $me->authorise('core.admin');
		$isAffiliate = $this->checkAffiliate($me->id);

		$affUId = !$affUId && $isAffiliate ? $me->id : $affUId;

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'a.*'))
			->from($db->qn('#__affiliates_commissions') . ' AS a');

		if ($affUId > 0)
		{
			$query->where('aff_uid = ' . (int) $affUId);
		}
		elseif ($isAdmin)
		{}
		else
		{
			$query->where(0);
		}

		$query->where('is_approved IN (0, 1)');

		return $query;
	}

	public function getItems()
	{
		$items = parent::getItems();

		foreach ($items as $item)
		{
			$item->product_categories = '';
			if ($item->product_id)
			{
				$pTitle = $this->helper->product->loadResult(array('id' => $item->product_id, 'list.select' => 'a.title'));

				$catIds = $this->helper->product->getCategories($item->product_id);

				if (!empty($catIds))
				{
					$filters  = array('list.select' => 'a.title', 'id' => $catIds);
					$catTitle = $this->helper->category->loadColumn($filters);

					$item->product_categories = implode(', ', $catTitle);
				}
			}
			$item->product_title = !empty($pTitle) ? $pTitle : ' - ';

			if ($item->variant_id)
			{
				$vTitle = $this->helper->variant->loadResult(array('id' => $item->variant_id, 'list.select' => 'a.title'));
			}
			$item->variant_title = !empty($vTitle) ? $vTitle : '';

			$item->seller_name = $item->seller_uid ? JFactory::getUser($item->seller_uid)->name : '';
			$item->affiliate_name = $item->aff_uid ? JFactory::getUser($item->aff_uid)->name : '';
		}

		return $items;
	}

	/**
	 * @param $userId
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	public static function checkAffiliate($userId)
	{
		$db = JFactory::getDbo();
		$select = $db->getQuery(true);

		$select->select('user_id')
			->from('#__affiliates_profiles')
			->where('user_id = ' . (int) $userId);

		return $db->setQuery($select)->loadResult();
	}
}
