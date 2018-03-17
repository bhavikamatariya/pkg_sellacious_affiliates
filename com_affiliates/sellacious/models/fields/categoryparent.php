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

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_sellacious
 * @since		1.6
 */
class JFormFieldCategoryParent extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var	   string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'categoryparent';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	protected function getOptions()
	{
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$table  = JTable::getInstance('Category', 'AffiliatesTable');
		$options= array();

		$id     = $this->form->getValue('id');
		$table->load($id);

		$query->select('a.id, a.title')
				->from($db->qn('#__affiliates_categories').' AS a')
				->where('a.level > 0')
				->where('a.state = 1')

				// we need level in the tree.
				->select('COUNT(DISTINCT c2.id) AS level')
				->join('LEFT OUTER', $db->qn('#__affiliates_categories').' AS c2 ON a.lft > c2.lft AND a.rgt < c2.rgt')
				->group('a.id, a.lft, a.rgt, a.parent_id, a.title')

				// intelligently disable child of self
				->select('(a.lft BETWEEN '. $db->q($table->lft) .' AND '. $db->q($table->rgt) .') AS disable')

				->order('a.lft ASC')
				;

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

		foreach ($items as $item)
		{
			$level = ($item->level > 1) ? ('|' . str_repeat('&mdash;', $item->level - 1) . ' ') : '';
			$options[] = JHtml::_('select.option', $item->id, $level . $item->title, 'value', 'text', $item->disable);
		}

		return array_merge(parent::getOptions(), $options);
	}

}
