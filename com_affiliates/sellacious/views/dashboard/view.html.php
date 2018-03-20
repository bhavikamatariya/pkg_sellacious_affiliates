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
 * View to edit
 *
 * @since __DEPLOY_VERSION__
 */
class AffiliatesViewDashboard extends SellaciousView
{
	/**
	 * @var  stdClass[]
	 * @since __DEPLOY_VERSION__
	 */
	protected $affData;

	/**
	 * @var  bool
	 * @since __DEPLOY_VERSION__
	 */
	protected $isAffiliate = false;

	/**
	 * @param null $tpl
	 *
	 * @return mixed
	 *
	 * @since version
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$me = JFactory::getUser();
		$isAdmin = $me->authorise('core.admin');
		$affiliate = $this->getAffiliate($me->id, $isAdmin);

		if (!empty($affiliate))
		{
			$this->isAffiliate = $isAdmin ? false : true;
			$this->affData = $affiliate;
		}

		return parent::display($tpl);
	}

	/**
	 * @param $userId
	 * @param $isAdmin
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	public static function getAffiliate($userId, $isAdmin)
	{
		$db = JFactory::getDbo();
		$select = $db->getQuery(true);

		$select->select('a.*')
			->from('#__affiliates_profiles a');

		if ($isAdmin)
		{
			return $db->setQuery($select)->loadObjectList();
		}
		else
		{
			$select->where('user_id = ' . (int) $userId);

			$result = $db->setQuery($select)->loadObject();
			$result->userCommission = array();
			$result->catCommission = array();

			if (!empty($result->user_id))
			{
				$select = $db->getQuery(true);
				$select->select('uc.product_catid, uc.commission, sc.title AS category_title, sc.level')
					->from('#__affiliates_user_commissions uc')
					->join('left', '#__sellacious_categories sc ON sc.id = uc.product_catid')
					->where('uc.aff_uid = ' . (int) $result->user_id);

				$result->userCommission = $db->setQuery($select)->loadObjectList();
			}

			if (!empty($result->category_id))
			{
				$select = $db->getQuery(true);
				$select->select('uc.product_catid, uc.commission, sc.title AS category_title, sc.level')
					->from('#__affiliates_category_commissions uc')
					->join('left', '#__sellacious_categories sc ON sc.id = uc.product_catid')
					->where('uc.affiliate_catid = ' . (int) $result->category_id);

				$result->catCommission = $db->setQuery($select)->loadObjectList();
			}

			return $result;
		}
	}
}
