<h2 class="page-header"><?php _e('upload_settings') ?></h2>

<!-- nav-tabs -->
<ul class="nav nav-pills">
	<li role="presentation"><?php echo admin_anchor('settings', lang('general')) ?></li>
	<li role="presentation"><?php echo admin_anchor('settings/users', lang('users')) ?></li>
	<li role="presentation"><?php echo admin_anchor('settings/email', lang('email')) ?></li>
	<li role="presentation" class="active"><?php echo admin_anchor('settings/uploads', lang('uploads')) ?></li>
	<li role="presentation"><?php echo admin_anchor('settings/captcha', lang('captcha')) ?></li>
</ul><hr />
<!-- /nav-tabs -->
<!-- tab-content -->
<?php echo form_open('admin/settings/uploads', 'role="form" class="form-horizontal"', $hidden) ?>
	<fieldset>

		<!-- Uploads path -->
		<div class="form-group<?php echo form_error('upload_path') ? ' has-error' : ''?>">
			<label for="upload_path" class="col-sm-2 control-label"><?php _e('set_upload_path') ?></label>
			<div class="col-sm-10">
				<?php echo print_input($upload_path, array('class' => 'form-control')) ?>
				<div class="help-block"><?php echo form_error('upload_path') ?: lang('set_upload_path_tip') ?></div>
			</div>
		</div>

		<!-- Allowed file types -->
		<div class="form-group<?php echo form_error('allowed_types') ? ' has-error' : ''?>">
			<label for="allowed_types" class="col-sm-2 control-label"><?php _e('set_allowed_types') ?></label>
			<div class="col-sm-10">
				<?php echo print_input($allowed_types, array('class' => 'form-control')) ?>
				<div class="help-block"><?php echo form_error('allowed_types') ?: lang('set_allowed_types_tip') ?></div>
			</div>
		</div>

		<div class="text-right">
			<button class="btn btn-primary" type="submit"><?php _e('save_changes') ?></button>
		</div>
	</fieldset>

<?php echo form_close() ?>