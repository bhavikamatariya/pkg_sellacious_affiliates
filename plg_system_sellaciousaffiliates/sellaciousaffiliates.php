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
 * Plugin class for affiliates handling.
 *
 * @since  1.6
 */
class PlgSystemSellaciousAffiliates extends JPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.4
	 */
	protected $autoloadLanguage = true;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    JApplicationCms
	 * @since  3.4
	 */
	protected $app;

	/**
	 * This method logs the user visits.
	 *
	 * @return  void
	 *
	 * @throws Exception
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function onAfterRoute()
	{
		// We only work for site visitors and only GET requests
		if (!$this->app->isSite() || $this->app->input->getMethod() != 'GET')
		{
			return;
		}

		JLoader::import('sellacious.loader');

		if (!class_exists('SellaciousHelper'))
		{
			return;
		}

		$uri   = JUri::getInstance();
		$affId = $uri->getVar('affid', null);

		if (empty($affId))
		{
			return;
		}

		$affData = $this->getAffiliate($affId);

		if ($affData->id > 0)
		{
			$jInput = $this->app->input;
			$path   = JUri::root(true) ?: '/';
			$expiry = strtotime(JFactory::getDate('+1 days'));

			$jInput->cookie->set('sellacious_affiliate_affid', $affId, $expiry, $path);

			$this->saveLog($affData, $affData->total_visits + 1, 0, 'user.visit', JFactory::getUser()->get('id'));
		}

		return;
	}

	/**
	 * Utility method to act on a user after it has been saved.
	 *
	 * This method sends a registration email to new users created in the backend.
	 *
	 * @param   array   $user    Holds the new user data.
	 * @param   boolean $isNew   True if a new user is stored.
	 * @param   boolean $success True if user was successfully stored in the database.
	 * @param   string  $msg     Message.
	 *
	 * @throws Exception
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function onUserAfterSave($user, $isNew, $success, $msg)
	{
		// We only work for site visitors and only GET requests
		if (!$this->app->isSite())
		{
			return;
		}

		JLoader::import('sellacious.loader');

		if (!class_exists('SellaciousHelper'))
		{
			return;
		}

		$jInput    = $this->app->input;
		$affCookie = $jInput->cookie->getString('sellacious_affiliate_affid', null);

		if ($isNew && $success && !empty($affCookie))
		{
			$affData = $this->getAffiliate($affCookie);

			if ($affData->id > 0)
			{
				$this->saveLog($affData, 0, $affData->total_registered + 1, 'user.registered', $user['id']);

				$path = JUri::root(true) ?: '/';
				$jInput->cookie->set('sellacious_affiliate_affid', '', 1, $path);
			}
		}

		return;
	}

	/**
	 * Get affiliates from db
	 *
	 * @param $affId
	 *
	 * @return mixed|stdClass
	 *
	 * @since version
	 */
	public static function getAffiliate($affId)
	{
		$db      = JFactory::getDbo();
		$affData = new stdClass;
		$select  = $db->getQuery(true);

		$select->select('p.*')
			->from('#__affiliates_profiles p')
			->where('affid = ' . $db->q($affId));

		try
		{
			$affData = $db->setQuery($select)->loadObject();
		}
		catch (Exception $e)
		{
		}

		return $affData;
	}

	/**
	 * Save log of affiliate links
	 *
	 * @param $affData
	 * @param $totalVisits
	 * @param $totalRegistered
	 * @param $context
	 * @param $userId
	 *
	 * @throws Exception
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function saveLog($affData, $totalVisits, $totalRegistered, $context, $userId)
	{
		$app = JFactory::getApplication();

		JTable::addIncludePath(JPATH_SITE . '/components/com_affiliates/tables');
		$table = JTable::getInstance('Profile', 'AffiliatesTable');

		$data     = new stdClass;
		$data->id = $affData->id;

		if ($totalVisits > 0)
		{
			$data->total_visits = $totalVisits;
		}

		if ($totalRegistered > 0)
		{
			$data->total_registered = $totalRegistered;
		}

		$log      = new stdClass;
		$logTable = JTable::getInstance('UserLog', 'AffiliatesTable');

		$log->aff_uid    = $affData->user_id;
		$log->user_id    = $userId;
		$log->context    = $context;
		$log->aff_url    = JUri::root();
		$log->ip_address = $app->input->server->getString('REMOTE_ADDR');

		try
		{
			$table->bind((array) $data);
			$table->check();
			$table->store();

			$logTable->bind((array) $log);
			$logTable->check();
			$logTable->store();
		}
		catch (Exception $e)
		{
			JLog::add(JText::sprintf('PLG_SYSTEM_AFFILIATES_TRACK_SESSION_ERROR', $e->getMessage()), JLog::NOTICE);

			return;
		}
	}
}
