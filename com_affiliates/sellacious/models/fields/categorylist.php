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

JFormHelper::loadFieldClass('List');

/**
 * Form Field class for the sellacious category list.
 *
 * @since   1.6
 */
class JFormFieldCategoryList extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'CategoryList';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 * @since   1.6
	 */
	protected function getOptions()
	{
		// This may be called from outer context so load helpers explicitly.
		jimport('sellacious.loader');

		if (!class_exists('SellaciousHelper'))
		{
			JFactory::getApplication()->enqueueMessage('COM_SELLACIOUS_LIBRARY_NOT_FOUND', 'error');

			return parent::getOptions();
		}


		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('a.id, a.title, a.type, a.level, a.lft, a.rgt')
			->from($db->qn('#__affiliates_categories').' AS a')
			->where('a.level > 0')
			->where('a.state = 1');

		$db->setQuery($query);

		try
		{
			$items = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			JError::raiseWarning('505', $e->getMessage());
			$items = array();
		}

		$show  = (string) $this->element['show_all'] == 'true';
		$options = array();

		foreach ($items as $item)
		{
			// We enable only leaf nodes for selection
			$level   = ($item->level > 1) ? (str_repeat('|&mdash; ', $item->level - 1)) : '';
			$disable = $show ? false : ($item->rgt - $item->lft) > 1;

			$options[] = JHtml::_('select.option', $item->id, $level . $item->title, 'value', 'text', $disable);
		}

		return array_merge(parent::getOptions(), $options);
	}
}
