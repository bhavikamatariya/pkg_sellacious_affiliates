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

class AffiliatesController extends SellaciousControllerBase
{
	/**
	 * Method to display a view.
	 *
	 * @param   bool   $cacheable  If true, the view output will be cached
	 * @param   mixed  $urlparams  An array of safe url parameters and their variable types, for valid values.
	 *
	 * @see     JFilterInput::clean()
	 *
	 * @return  JControllerLegacy  This object to support chaining.
	 * @since   1.5
	 */
	public function display($cacheable = false, $urlparams = false)
	{
		$app  = JFactory::getApplication();
		$view = $app->input->get('view', '');

		$app->input->set('view', $view);

		return parent::display($cacheable, $urlparams);
	}
}
