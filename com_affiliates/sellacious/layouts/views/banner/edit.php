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

JHtml::_('jquery.framework');

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

$helper = SellaciousHelper::getInstance();
$image = $helper->media->getImages('affiliate.banner.image', $this->item->id, false, false);
$image = reset($image);

if (!empty($image) && $this->item->id):
	$width = '';
	$height = '';
	try
	{
		list($width, $height, $type, $attr) = getimagesize(JUri::root() . $image);
	}
	catch (Exception $e){} ?>
	<div class="row">
		<div class="col-sm-12">
			<div class="w100p center"><?php echo $width ?> x <?php echo $height ?></div>
			<div class="col-sm-2">
				<img src="<?php echo JUri::root() . $image; ?>">
			</div>
		</div>
	</div>
<?php
endif;


