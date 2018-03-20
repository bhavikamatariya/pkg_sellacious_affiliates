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

use Joomla\Utilities\ArrayHelper;

/**
 * Affiliates helper.
 *
 * @since  1.0
 */
class AffiliatesHelperAffiliates extends SellaciousHelperBase
{
	/**
	 * @var  bool
	 *
	 * @since   1.4.7
	 */
	protected $hasTable = false;

	/**
	 * @var  array
	 *
	 * @since   1.0.0
	 */
	protected static $helpers = array();

	/**
	 * @var  AffiliatesHelperAffiliates
	 *
	 * @since   1.0.0
	 */
	private static $instance = false;

	/**
	 * Get an instance of helper class.
	 * Create one if not already, otherwise return existing instance
	 *
	 * @param   string $name Name of the helper class
	 *
	 * @return  AffiliatesHelperAffiliates
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public static function getInstance($name = '')
	{
		if (false === self::$instance)
		{
			self::$instance = new self;
		}

		if ($name == '')
		{
			return self::$instance;
		}

		$key       = strtolower($name);
		$className = 'AffiliatesHelper' . ucfirst($key);

		if (!isset(self::$helpers[$key]))
		{
			self::$helpers[$key] = class_exists($className) ? new $className : false;
		}

		if (self::$helpers[$key] === false)
		{
			throw new Exception(JText::sprintf('COM_AFFILIATES_ERROR_HELPER_NOT_SUPPORTED', ucwords($key)), '5501');
		}

		return self::$helpers[$key];
	}

	/**
	 * Fetch seller commissions for the given seller for each product category maps
	 *
	 * @param   int $affUid Seller user id
	 *
	 * @return  array  Commissions for each product category
	 *
	 * @since   1.5.0
	 */
	public function getCommissions($affUid)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('product_catid, commission')
			->from('#__affiliates_user_commissions')
			->where('aff_uid = ' . $db->q($affUid));

		$items  = $db->setQuery($query)->loadObjectList();
		$result = ArrayHelper::getColumn((array) $items, 'commission', 'product_catid');

		return $result;
	}

	/**
	 * Fetch seller commissions for the given category maps
	 *
	 * @param   int $catid Seller category id
	 *
	 * @return  array  Commissions for each product category
	 *
	 * @since   1.5.0
	 */
	public function getCommissionsByCategory($catid)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('product_catid, commission')
			->from('#__affiliates_category_commissions')
			->where('affiliate_catid = ' . $db->q($catid));

		$items  = $db->setQuery($query)->loadObjectList();
		$result = ArrayHelper::getColumn((array) $items, 'commission', 'product_catid');

		return $result;
	}

	/**
	 * Calculate the effective seller commission for the selected order item (seller > seller_category > global)
	 *
	 * @param   stdClass $item      The order item to check for
	 * @param   stdClass $affiliate The order id to check for
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	public function getAffiliateCommissions($item, $affiliate)
	{
		$helper     = SellaciousHelper::getInstance();
		$basicPrice = $item->basic_price;
		$affUid     = $affiliate->user_id;
		$productId  = $item->product_id;

		// We'll use inheritance if a category is not mapped
		$pCatLevels = array();
		$categories = $helper->product->getCategories($productId, false);

		foreach ($categories as $categoryId)
		{
			$pCatLevel    = $helper->category->getParents($categoryId, true);
			$pCatLevels[] = array_reverse($pCatLevel);
		}

		// Check seller uid - product category specific
		$commissions = $this->getCommissions($affUid);

		list($bestA, $bestR, $bestP) = $this->pickCommission($commissions, $pCatLevels, $basicPrice);

		// We've found a commission value? Return!
		if (isset($bestA))
		{
			return array($bestA, $bestR, $bestP);
		}

		// Now lookup into the seller category
		$categoryS  = (array) $affiliate->category_id;
		$categories = $this->getParents($categoryS, true);
		$sCatLevel  = array_reverse($categories);

		// We'll use inheritance if a category is not mapped. Inheritance includes default category here.
		foreach ($sCatLevel as $sCatid)
		{
			$commissions = $this->getCommissionsByCategory($sCatid);

			list($bestA, $bestR, $bestP) = $this->pickCommission($commissions, $sCatLevel, $basicPrice);

			// We've found a commission value
			if (isset($bestA))
			{
				return array($bestA, $bestR, $bestP);
			}
		}
	}

	/**
	 * Pick the best commission rate (minimum) for the seller's sale
	 *
	 * @param   string[] $commissions    The commissions array from which to pick
	 * @param   int[][]  $categoriesList The product categories id groups each for the inheritance level for each assigned category
	 * @param   float    $basicPrice     The effective sales price on which to calculate commission
	 *
	 * @return  array
	 * @since   1.5.0
	 */
	protected function pickCommission($commissions, $categoriesList, $basicPrice)
	{
		$bestA = null;
		$bestR = null;
		$bestP = null;

		// Iterate over each assigned category
		foreach ($categoriesList as $categories)
		{
			if (!is_array($categories))
			{
				$categories = (array) $categories;
			}
			// Iterate for each parent upward for the assigned category in this iteration
			foreach ($categories as $categoryId)
			{
				if (isset($commissions[$categoryId]))
				{
					$value  = $commissions[$categoryId];
					$perc   = substr($value, -1) == '%';
					$rate   = $perc ? substr($value, 0, -1) : $value;
					$amount = $perc ? round($basicPrice * $rate / 100.0, 2) : $rate;

					if (!isset($bestA) || $amount < $bestA)
					{
						$bestA = $amount;
						$bestR = $rate;
						$bestP = $perc;
					}

					// No more inherit as we have found a value
					break;
				}
			}
		}

		return array($bestA, $bestR, $bestP);
	}

	/**
	 * Return a list of parent items for given item or items. Only available for nested set tables
	 *
	 * @param   int|int[] $pks       Item id or a list of ids for which parents are to be found
	 * @param   bool      $inclusive Whether the output list should contain the queried ids as well
	 *
	 * @return  int[]
	 * @throws  UnexpectedValueException
	 *
	 * @since   1.0.0
	 */
	public function getParents($pks, $inclusive)
	{
		$list = array();

		foreach ($pks as $pk)
		{
			try
			{
				JTable::addIncludePath(JPATH_SITE . '/components/com_affiliates/tables');
				$table = JTable::getInstance('Category', 'AffiliatesTable');
				$table->load($pk);

				$db     = JFactory::getDbo();
				$select = $db->getQuery(true);
				$select->select('a.id')
					->from('#__affiliates_categories a')
					->where('a.lft' . ($inclusive ? ' <= ' : ' < ') . (int) $table->get('lft'))
					->where('a.rgt' . ($inclusive ? ' >= ' : ' > ') . (int) $table->get('rgt'))
					->order('a.lft');

				$result = (array) $db->setQuery($select)->loadColumn();
			}
			catch (Exception $e)
			{
				$result = array();
			}

			$list[] = $result;
		}

		$parents = array_reduce($list, 'array_merge', array());

		return array_unique($parents);
	}

	/**
	 * @param $bannerId
	 *
	 * @return bool
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function countBannerViews($bannerId)
	{
		$db     = JFactory::getDbo();
		$update = $db->getQuery(true);

		$update->update('#__affiliates_banners')
			->set('total_views = total_views + 1')
			->where('id = ' . (int) $bannerId);

		try
		{
			$db->setQuery($update)->execute();

			JTable::addIncludePath(JPATH_SITE . '/components/com_affiliates/tables');
			$logTable = JTable::getInstance('Log', 'AffiliatesTable');

			$log             = new stdClass;
			$log->banner_id  = $bannerId;
			$log->context    = 'banner.viewed';
			$log->user_id    = JFactory::getUser()->get('id');
			$log->aff_url    = JUri::root();
			$log->ip_address = JFactory::getApplication()->input->server->getString('REMOTE_ADDR');

			$logTable->bind((array) $log);
			$logTable->check();
			$logTable->store();
		}
		catch (Exception $e)
		{
		}

		return true;
	}
}
