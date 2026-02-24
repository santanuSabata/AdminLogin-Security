<div class="wrap">
<h1>GuardWP Security Settings</h1>

<form method="post" action="options.php">
<?php settings_fields('guardwp_settings_group'); ?>

<table class="form-table">

<tr>
<th>Max Login Attempts</th>
<td>
<input type="number" name="guardwp_max_attempts"
value="<?php echo esc_attr(get_option('guardwp_max_attempts', 5)); ?>">
</td>
</tr>

<tr>
<th>Lockout Time (minutes)</th>
<td>
<input type="number" name="guardwp_lockout_time"
value="<?php echo esc_attr(get_option('guardwp_lockout_time', 15)); ?>">
</td>
</tr>

<tr>
<th>Lockout Message</th>
<td>
<textarea name="guardwp_lockout_message"><?php
echo esc_textarea(get_option('guardwp_lockout_message',
'Too many failed attempts. Please try again later.'));
?></textarea>
</td>
</tr>

<tr>
<th>Whitelist IPs (comma separated)</th>
<td>
<input type="text" name="guardwp_whitelist_ips"
value="<?php echo esc_attr(get_option('guardwp_whitelist_ips')); ?>">
</td>
</tr>

<tr>
<th>reCAPTCHA Site Key</th>
<td>
<input type="text" name="guardwp_recaptcha_site_key"
value="<?php echo esc_attr(get_option('guardwp_recaptcha_site_key')); ?>">
</td>
</tr>

<tr>
<th>reCAPTCHA Secret Key</th>
<td>
<input type="text" name="guardwp_recaptcha_secret_key"
value="<?php echo esc_attr(get_option('guardwp_recaptcha_secret_key')); ?>">
</td>
</tr>

</table>

<?php submit_button(); ?>
</form>
</div>