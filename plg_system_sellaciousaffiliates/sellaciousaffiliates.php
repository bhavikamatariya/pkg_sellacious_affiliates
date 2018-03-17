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

JLoader::register('AffiliatesHelperAffiliates', JPATH_SITE . '\components\com_affiliates\helpers\affiliates.php');

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
	 * @var SellaciousHelper
	 * @since __DEPLOY_VERSION__
	 */
	protected $helper;

	/**
	 * @var AffiliatesHelperAffiliates
	 * @since __DEPLOY_VERSION__
	 */
	protected $affHelper;

	/**
	 * PlgSystemSellaciousAffiliates constructor.
	 *
	 * @param       $subject
	 * @param array $config
	 *
	 * @throws Exception
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct($subject, array $config = array())
	{
		$this->helper = SellaciousHelper::getInstance();
		$this->affHelper = AffiliatesHelperAffiliates::getInstance();

		parent::__construct($subject, $config);
	}

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
		$affBannerId = $uri->getVar('affbannerid', null);

		if (!empty($affId))
		{
			$affData = $this->getAffiliate($affId);

			if ($affData->id > 0)
			{
				$jInput = $this->app->input;
				$path   = JUri::root(true) ?: '/';
				$expiry = strtotime(JFactory::getDate('+1 days'));

				$jInput->cookie->set('sellacious_affiliate_affid', $affId, $expiry, $path);

				$data               = new stdClass;
				$data->id           = $affData->id;
				$data->total_visits = $affData->total_visits + 1;

				$log          = new stdClass;
				$log->aff_uid = $affData->user_id;
				$log->context = 'user.visit';

				$this->saveLog($data, $log);
			}
		}

		if (!empty($affBannerId))
		{
			$affBannerFlag = $uri->getVar('bannerflag', null);

			if ($affBannerId > 0 && $affBannerFlag)
			{
				$db = JFactory::getDbo();
				$update = $db->getQuery(true);
				$update->update('#__affiliates_banners')
					->set('total_clicks = total_clicks + 1')
					->where('id = ' . (int)$affBannerId);
				try
				{
					$db->setQuery($update)->execute();
				}
				catch (Exception $e){}

				$log          = new stdClass;
				$log->aff_uid = isset($affData->id) && $affData->user_id > 0 ? $affData->user_id : 0;
				$log->context = 'banner.clicked';

				$this->saveLog(null, $log);
			}
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
				$data                   = new stdClass;
				$data->id               = $affData->id;
				$data->total_registered = $affData->total_registered + 1;

				$log          = new stdClass;
				$log->aff_uid = $affData->user_id;
				$log->context = 'user.registered';

				$this->saveLog($data, $log);

				$path = JUri::root(true) ?: '/';
				$jInput->cookie->set('sellacious_affiliate_affid', '', 1, $path);
			}
		}

		return;
	}

	/**
	 * This method sends add log when a affiliate made a sale.
	 *
	 * @param   string $context The calling context
	 * @param   object $payment Holds the payment object from the payments table for the target order
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAfterOrderPayment($context, $payment)
	{
		if ($context == 'com_sellacious.order')
		{
			$jInput    = $this->app->input;
			$affCookie = $jInput->cookie->getString('sellacious_affiliate_affid', null);

			if (!empty($affCookie))
			{
				$affData = $this->getAffiliate($affCookie);

				if (empty($affData))
				{
					return true;
				}

				$affHelper = AffiliatesHelperAffiliates::getInstance();
				$totalSales = 0;
				$totalComm = array();
				$items     = $this->helper->order->getOrderItems($payment->order_id);

				if (!empty($items))
				{
					foreach ($items as $item)
					{
						list($comm_amount, $comm_rate, $is_percent) = $affHelper->getAffiliateCommissions($item, $affData);

						if (abs($comm_amount) >= 0.01)
						{
							$finalComm = $comm_amount * $item->quantity;

							$totalSales++;
							$totalComm[] = $finalComm;

							$log                = new stdClass;
							$log->aff_uid       = $affData->user_id;
							$log->context       = 'order.placed';
							$log->product_code  = $item->item_uid;
							$log->product_price = $item->basic_price;
							$log->commission    = $finalComm;

							$this->saveLog(null, $log);
						}
					}

					if ($totalSales > 0 && !empty($totalComm))
					{
						$data              = new stdClass;
						$data->id          = $affData->id;
						$data->total_sales = $affData->total_sales + $totalSales;
						$data->commission  = $affData->commission + array_sum($totalComm);

						$this->saveLog($data, null);
					}

					$path = JUri::root(true) ?: '/';
					$jInput->cookie->set('sellacious_affiliate_affid', '', 1, $path);
				}
			}
		}

		return true;
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
	 * @param $data
	 * @param $log
	 *
	 * @throws Exception
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function saveLog($data, $log)
	{
		$app = JFactory::getApplication();

		JTable::addIncludePath(JPATH_SITE . '/components/com_affiliates/tables');

		try
		{
			if (!empty($data))
			{
				$table    = JTable::getInstance('Profile', 'AffiliatesTable');

				$table->bind((array) $data);
				$table->check();
				$table->store();
			}
		}
		catch (Exception $e)
		{
			JLog::add(JText::sprintf('PLG_SYSTEM_AFFILIATES_TRACK_SESSION_ERROR', $e->getMessage()), JLog::NOTICE);
		}

		try
		{
			if (!empty($log))
			{
				$logTable = JTable::getInstance('UserLog', 'AffiliatesTable');

				$log->user_id    = JFactory::getUser()->get('id');
				$log->aff_url    = JUri::root();
				$log->ip_address = $app->input->server->getString('REMOTE_ADDR');
				$logTable->bind((array) $log);
				$logTable->check();
				$logTable->store();
			}
		}
		catch (Exception $e)
		{
			JLog::add(JText::sprintf('PLG_SYSTEM_AFFILIATES_TRACK_SESSION_ERROR', $e->getMessage()), JLog::NOTICE);
		}
	}
}
