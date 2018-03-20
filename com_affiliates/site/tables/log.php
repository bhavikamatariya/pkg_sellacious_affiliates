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
 * Log Table class
 *
 * @since __DEPLOY_VERSION__
 */
class AffiliatesTableLog extends SellaciousTable
{
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
			parent::__construct('#__affiliates_log', 'id', $db);
	}
}
