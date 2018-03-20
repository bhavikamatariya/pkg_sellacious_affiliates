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
		$this->helper    = SellaciousHelper::getInstance();
		$this->affHelper = AffiliatesHelperAffiliates::getInstance();

		parent::__construct($subject, $config);
	}

	/**
	 * Adds order email template fields to the sellacious form for creating email templates
	 *
	 * @param   JForm $form The form to be altered.
	 * @param   array $data The associated data for the form.
	 *
	 * @since   3.0
	 * @return  boolean
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!$form instanceof JForm)
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		if ($form->getName() == 'com_sellacious.config')
		{
			$form->loadFile(__DIR__ . '/forms/form.xml', false);
		}

		return true;
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

		$uri         = JUri::getInstance();
		$affId       = $uri->getVar('affid', null);
		$affBannerId = $uri->getVar('affbannerid', null);
		$view        = $uri->getVar('view', null);
		$layout      = $uri->getVar('layout', null);

		if ($view == 'banners' && $layout == 'image')
		{
			return;
		}

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
				$log->context = 'user.viewed';

				$this->saveLog($data, $log);
			}
		}

		if (!empty($affBannerId))
		{
			$affBannerFlag = $uri->getVar('bannerflag', null);

			if ($affBannerId > 0 && $affBannerFlag)
			{
				$db     = JFactory::getDbo();
				$update = $db->getQuery(true);
				$update->update('#__affiliates_banners')
					->set('total_clicks = total_clicks + 1')
					->where('id = ' . (int) $affBannerId);
				try
				{
					$db->setQuery($update)->execute();
				}
				catch (Exception $e)
				{
					JLog::add($e->getMessage(), JLog::NOTICE);
				}

				$log            = new stdClass;
				$log->aff_uid   = isset($affData->id) && $affData->user_id > 0 ? $affData->user_id : 0;
				$log->banner_id = $affBannerId;
				$log->context   = 'banner.clicked';

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

				$affHelper   = AffiliatesHelperAffiliates::getInstance();
				$totalSales  = 0;
				$totalComm   = array();
				$items       = $this->helper->order->getOrderItems($payment->order_id);
				$autoApprove = $this->helper->config->get('auto_approved_affiliates_commissions');

				if (!empty($items))
				{
					foreach ($items as $item)
					{
						list($comm_amount, $comm_rate, $is_percent) = $affHelper->getAffiliateCommissions($item, $affData);

						if (abs($comm_amount) >= 0.01)
						{
							$rate      = $is_percent ? $comm_rate . '%' : $comm_rate . ' ' . $payment->currency;
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

							$comm                       = new stdClass;
							$comm->aff_uid              = $affData->user_id;
							$comm->user_id              = JFactory::getUser()->id;
							$comm->product_id           = $item->product_id;
							$comm->variant_id           = $item->variant_id;
							$comm->seller_uid           = $item->seller_uid;
							$comm->basic_price          = $item->basic_price;
							$comm->quantity             = $item->quantity;
							$comm->affiliate_commission = $rate;
							$comm->commission_amount    = $finalComm;
							$comm->is_approved          = $autoApprove;
							$comm->created              = JFactory::getDate()->toSql();

							try
							{
								$db = JFactory::getDbo();
								$db->insertObject('#__affiliates_commissions', $comm);
							}
							catch (Exception $e)
							{
								JLog::add($e->getMessage(), JLog::NOTICE);
							}
						}
					}

					if ($totalSales > 0 && !empty($totalComm))
					{
						$data              = new stdClass;
						$data->id          = $affData->id;
						$data->total_sales = $affData->total_sales + $totalSales;

						if ($autoApprove)
						{
							$data->approved_commission = $affData->approved_commission + array_sum($totalComm);
						}
						else
						{
							$data->tentative_commission = $affData->tentative_commission + array_sum($totalComm);
						}

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
			JLog::add($e->getMessage(), JLog::NOTICE);
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
				$table = JTable::getInstance('Profile', 'AffiliatesTable');

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
				$logTable = JTable::getInstance('Log', 'AffiliatesTable');

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

	/**
	 * Listener for the `onAfterRender` event
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onAfterRender()
	{
		if (!$this->app->isClient('site'))
		{
			return;
		}

		$now        = JFactory::getDate();
		$paymentDay = $this->helper->config->get('affiliates_payment_day');
		$currentDay = $now->format('d');

		if ($paymentDay == $currentDay)
		{
			$currency = $this->helper->currency->current('code_3');
			$db       = JFactory::getDbo();

			$select = $db->getQuery(true);
			$select->select('SUM(commission_amount) AS amount, aff_uid')
				->from('#__affiliates_commissions')
				->where('is_approved = 1')
				->group('aff_uid');

			try
			{
				$commissions = $db->setQuery($select)->loadObjectList();

				foreach ($commissions as $commission)
				{
					if (abs($commission->amount) >= 0.01 && $commission->aff_uid)
					{
						$amount = number_format($commission->amount, 2);
						$transactions = array(
							(object) array(
								'id'         => null,
								'context'    => 'user.id',
								'context_id' => $commission->aff_uid,
								'reason'     => 'affilate.commission',
								'crdr'       => 'cr',
								'amount'     => $amount,
								'currency'   => $currency,
								'balance'    => $amount,
								'txn_date'   => $now->toSql(),
								'notes'      => 'Add fund (' . $amount . ' ' . $currency . ') into wallet for user ' . $commission->aff_uid . ' on ' . $now . '.',
								'state'      => 1,
							)
						);

						$this->helper->transaction->register($transactions);

						$update = $db->getQuery(true);
						$update->update('#__affiliates_profiles')
							->set('approved_commission = 0')
							->where('user_id = ' . (int) $commission->aff_uid);
						$db->setQuery($update)->execute();

						$update = $db->getQuery(true);
						$update->update('#__affiliates_commissions')
							->set('is_approved = -1')
							->where('aff_uid = ' . (int) $commission->aff_uid);
						$db->setQuery($update)->execute();
					}
				}
			}
			catch (Exception $e)
			{
				JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
			}
		}
	}
}
