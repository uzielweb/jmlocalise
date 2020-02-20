<?php
/**
 * @package     Com_Localise
 * @subpackage  views
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.formvalidator');

$fieldSets = $this->form->getFieldsets();
$ftpSets   = $this->formftp->getFieldsets();
$params    = ComponentHelper::getParams('com_localise');
$ref_tag   = $params->get('reference', 'en-GB');
$isNew     = empty($this->item->id);
$tag       = $this->item->tag ;
$client    = $this->item->client;

HTMLHelper::_('script', 'com_localise/language-form.js', ['version' => 'auto', 'relative' => true]);
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'language.cancel' || document.formvalidator.isValid(.getElementById('localise-language-form')))
		{
			Joomla.submitform(task, document.getElementById('localise-language-form'));
		}
	}
</script>

<form action="<?php echo \JRoute::_('index.php?option=com_localise&view=language&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="localise-language-form" class="form-validate">
	<div class="row-fluid">
		<!-- Begin Localise Language -->
		<div class="span12 form-horizontal">
			<?php if ($isNew) : ?>
				<p><em><?php echo Text::_('COM_LOCALISE_COPY_REF_TO_NEW_LANG_FIRSTSAVE'); ?></em><p>
			<?php elseif (!$isNew && $client != 'installation') : ?>
				<p><em> <?php echo Text::sprintf('COM_LOCALISE_COPY_REF_TO_NEW_LANG_TIP', $ref_tag, $tag); ?></em></p>
			<?php endif; ?>
			<fieldset>
				<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => $this->ftp ? 'ftp' : 'default')); ?>
					<?php if ($this->ftp) : ?>
					<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'ftp', Text::_($ftpSets['ftp']->label, true)); ?>

						<?php if (!empty($ftpSets['ftp']->description)):?>
							<p class="tip"><?php echo Text::_($ftpSets['ftp']->description); ?></p>
						<?php endif;?>

						<?php if ($this->ftp instanceof Exception): ?>
							<p class="error"><?php echo Text::_($this->ftp->message); ?></p>
						<?php endif; ?>

						<?php foreach($this->formftp->getFieldset('ftp',false) as $field): ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $field->label; ?>
							</div>
							<div class="controls">
								<?php echo $field->input; ?>
							</div>
						</div>
						<?php endforeach; ?>

					<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

					<?php endif; ?>

					<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'default', Text::_($fieldSets['default']->label, true)); ?>

						<div class="span6">
						<?php if (!empty($fieldSets['default']->description)) : ?>
							<p class="tip"><?php echo Text::_($fieldSets['default']->description); ?></p>
						<?php endif;?>
							<?php foreach($this->form->getFieldset('default') as $field): ?>
							<div class="control-group">
								<div class="control-label">
									<?php echo $field->label; ?>
								</div>
								<div class="controls">
									<?php echo $field->input; ?>
								</div>
							</div>
							<?php endforeach; ?>
						</div>
						<div class="span6">
						<?php if (!empty($fieldSets['meta']->description)) : ?>
							<p class="tip"><?php echo Text::_($fieldSets['meta']->description); ?></p>
						<?php endif;?>
							<?php foreach ($this->form->getFieldset('meta') as $field) : ?>
								<div class="control-group">
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php endforeach; ?>
							<?php if (version_compare(\JVERSION, '3.7', 'ge')) : ?>
								<?php foreach ($this->form->getFieldset('metanew') as $field) : ?>
									<div class="control-group">
										<div class="control-label">
											<?php echo $field->label; ?>
										</div>
										<div class="controls">
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php endforeach; ?>
							<?php endif;?>
						</div>

					<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

					<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'author', Text::_($fieldSets['author']->label, true)); ?>

						<?php if (!empty($fieldSets['author']->description)):?>
							<p class="tip"><?php echo Text::_($fieldSets['author']->description); ?></p>
						<?php endif;?>

						<?php foreach($this->form->getFieldset('author') as $field): ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $field->label; ?>
							</div>
							<div class="controls">
								<?php echo $field->input; ?>
							</div>
						</div>
						<?php endforeach; ?>

					<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

					<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'permissions', Text::_($fieldSets['permissions']->label, true)); ?>

						<?php if (!empty($fieldSets['permissions']->description)):?>
							<p class="tip"><?php echo Text::_($fieldSets['permissions']->description); ?></p>
						<?php endif;?>

						<?php foreach($this->form->getFieldset('permissions') as $field): ?>
						<div class="control-group form-vertical">
							<div class="controls">
								<?php echo $field->input; ?>
							</div>
						</div>
						<?php endforeach; ?>

					<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

					<input type="hidden" name="task" value="" />
					<?php echo HTMLHelper::_('form.token'); ?>

				<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
			</fieldset>
		</div>
		<!-- End Localise Language -->
	</div>
</form>
