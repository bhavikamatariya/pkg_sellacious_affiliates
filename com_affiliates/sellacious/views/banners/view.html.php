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
 * View class for a list of Banners.
 *
 * @since   1.0.0
 */
class AffiliatesViewBanners extends SellaciousViewList
{
	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $action_prefix = 'banner';

	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $view_item = 'banner';

	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $view_list = 'banners';

	/**
	 * @var  bool
	 *
	 * @since   1.0.0
	 */
	protected $is_nested = false;

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
		$affiliate = $this->getAffiliate($me->id);

		if (!empty($affiliate))
		{
			$this->isAffiliate = true;
			$this->affData = $affiliate;
		}

		return parent::display($tpl);
	}

	/**
	 * @param $userId
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	public static function getAffiliate($userId)
	{
		$db = JFactory::getDbo();
		$select = $db->getQuery(true);

		$select->select('a.*')
			->from('#__affiliates_profiles a')
			->where('user_id = ' . (int) $userId);

		return $db->setQuery($select)->loadObject();
	}
}
