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
 * Plugin class for redirect handling.
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

		$uri = JUri::getInstance();
		$affId = $uri->getVar('affid');

		if (empty($affId))
		{
			return;
		}

		$db = JFactory::getDbo();
		$select = $db->getQuery(true);

		$select->select('p.*')
			->from('#__affiliates_profiles p')
			->where('affid = ' . $db->q($affId));

		$affData = $db->setQuery($select)->loadObject();

		if ($affData->id > 0)
		{
			$app = JFactory::getApplication();
			$jInput = $app->input;
			$path = JUri::root(true) ?: '/';
			$expiry = strtotime(JFactory::getDate('+1 days'));

			$jInput->cookie->set('com_affiliate.affid', $affId, $expiry, $path);

			JTable::addIncludePath(JPATH_SITE . '/components/com_affiliates/tables');
			$table   = JTable::getInstance('Profile', 'AffiliatesTable');

			$data = new stdClass;
			$data->id = $affData->id;
			$data->total_visits = $affData->total_visits + 1;

			try
			{
				$table->bind((array) $data);
				$table->check();
				$table->store();
			}
			catch (Exception $e)
			{
				JLog::add(JText::sprintf('PLG_SYSTEM_SELLACIOUSUTM_TRACK_SESSION_ERROR', $e->getMessage()), JLog::NOTICE);

				return;
			}
		}

		return;
	}
}
