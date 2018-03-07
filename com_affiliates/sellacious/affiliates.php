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

// Include dependencies
JLoader::import('sellacious.loader');

if (!class_exists('SellaciousHelper'))
{
	throw new Exception(JText::_('COM_AFFILIATES_SELLACIOUS_LIBRARY_MISSING'));
}

JLoader::register('AffiliatesHelperAffiliates', __DIR__ . '/helpers/affiliates.php');

$app        = JFactory::getApplication();
$helper     = SellaciousHelper::getInstance();
$controller = JControllerLegacy::getInstance('Affiliates');
$task       = $app->input->getCmd('task');

$controller->execute($task);
$controller->redirect();

// Meta Redirect check will occur only if not redirected by the controller above.
$helper->core->metaRedirect();
