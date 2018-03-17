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
 * Profile Table class
 *
 * @since __DEPLOY_VERSION__
 */
class AffiliatesTableProfile extends SellaciousTable
{
	public static function getInstance($type, $prefix = 'AffiliatesTable', $config = array())
	{
		return parent::getInstance($type, $prefix, $config);
	}

	/**
	 * Constructor
	 *
	 * @param JDatabaseDriver $db A database connector object
	 *
	 * @throws Exception
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__affiliates_profiles', 'id', $db);
	}

	/**
	 * Overload check function
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function check()
	{
		if (!$this->get('id'))
		{
			$this->set('state', 1);
		}

		return parent::check();
	}

	/**
	 * Overload getUniqueConditions for mobile number and user
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected function getUniqueConditions()
	{
		$conditions = parent::getUniqueConditions();

		$conditions['affid'] = array('affid' => $this->get('affid'));

		return $conditions;
	}

	protected function getUniqueError($uk_index, JTable $table)
	{
		if ($uk_index == 'affid')
		{
			return 'AffId exists. Please try another.';
		}

		return false;
	}

}
