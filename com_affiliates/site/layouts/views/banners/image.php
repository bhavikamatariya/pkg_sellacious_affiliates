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

$app = JFactory::getApplication();
$jInput = $app->input;
$affId = $jInput->getString('affid');
$affBannerId = $jInput->getInt('affbannerid');

if (!empty($affId) && !empty($affBannerId))
{
	$helper    = SellaciousHelper::getInstance();
	$affHelper = AffiliatesHelperAffiliates::getInstance();

	$affHelper->countBannerViews($affBannerId);
	$image  = $helper->media->loadObject(array('record_id' => $affBannerId, 'context' => 'banner.image', 'table_name' => 'affiliate'));

	if (!headers_sent())
	{
		$type = 'image/jpeg';
		$path = 'components/com_affiliates/layouts/views/banners/no_image.jpg';

		if ($image)
		{
			$type = $image->type;
			$path = $image->path;
		}

		header('Content-Type: ' . $type);
		readfile(JPATH_SITE . '/' . $path);
		jexit();
	}
	else
	{
		jexit();
	}
}
