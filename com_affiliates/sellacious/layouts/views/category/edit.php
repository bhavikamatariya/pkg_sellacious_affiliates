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

$data	= array(
			'name'  => $this->getName(),
			'state' => $this->state,
			'item'  => $this->item,
			'form'  => $this->form,
		);

$options= array(
			'client' => 2,
			'debug'  => 0,
		);

echo JLayoutHelper::render('com_sellacious.view.edittabs', $data, '', $options);
