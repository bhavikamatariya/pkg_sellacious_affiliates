<?php
/**
 * @version     __DEPLOY_VERSION__
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

/**
 * Commissions list controller class
 *
 * @since   1.0.0
 */
class AffiliatesControllerCommissions extends SellaciousControllerAdmin
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 *
	 * @since   1.0.0
	 */
	protected $text_prefix = 'COM_AFFILIATES_BANNERS';

	/**
	 * Proxy for getModel.
	 *
	 * @param string $name
	 * @param string $prefix
	 * @param array  $config
	 *
	 * @return object
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function getModel($name = 'Commissions', $prefix = 'AffiliatesModel', $config = Array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

	public function approveCommission()
	{
		$jInput       = JFactory::getApplication()->input;
		$commissionId = $jInput->getInt('commission_id');
		$affUId       = $jInput->getInt('aff_uid');

		if (empty($commissionId))
		{
			$msg = JText::_('COM_AFFILIATES_COMMISSIONS_APPROVED_FAILURE');
			$type = 'error';
		}
		else
		{
			$db     = JFactory::getDbo();
			$update = $db->getQuery(true);
			$update->update('#__affiliates_commissions')
				->set('is_approved = 1')
				->where('id = ' . (int) $commissionId)
				->where('aff_uid = ' . (int) $affUId);

			try
			{
				$db->setQuery($update)->execute();

				$select = $db->getQuery(true);
				$select->select('SUM(commission_amount)')
					->from('#__affiliates_commissions')
					->where('is_approved = 1')
					->where('aff_uid = ' . (int) $affUId);
				$approved = $db->setQuery($select)->loadResult();

				$select = $db->getQuery(true);
				$select->select('SUM(commission_amount)')
					->from('#__affiliates_commissions')
					->where('is_approved = 0')
					->where('aff_uid = ' . (int) $affUId);
				$tentative = $db->setQuery($select)->loadResult();

				$update = $db->getQuery(true);
				$update->update('#__affiliates_profiles')
					->set('approved_commission = ' . (double) $approved)
					->set('tentative_commission = ' . (double) $tentative)
					->where('user_id = ' . (int) $affUId);
				$db->setQuery($update)->execute();

				$msg = JText::_('COM_AFFILIATES_COMMISSIONS_APPROVED_SUCCESS');
				$type = 'message';
			}
			catch (Exception $e)
			{
				$msg = JText::_('COM_AFFILIATES_COMMISSIONS_APPROVED_FAILURE');
				$type = 'error';
			}
		}

		$this->setRedirect('index.php?option=com_affiliates&view=commissions', $msg, $type);
	}
}
