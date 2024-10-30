<form method="post" action="options.php" class="inpsyde-form" id="inpsyde-form">
	<?php
	settings_fields( \Locale\Setting\PluginSettings::OPTION_GROUP );
	do_settings_sections( 'locale_api' );
	submit_button( esc_html__( 'Save changes', 'locale' ), 'primary', 'save_action' );
	?>
</form>
