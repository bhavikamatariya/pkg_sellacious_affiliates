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
	 * @param   string  $name  Name of the helper class
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
}
