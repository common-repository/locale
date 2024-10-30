<div class="wrap">

	<h1 class="settings__headline">
		<?php esc_html_e( 'Locale', 'translationamanager' ); ?>
		<small class="settings__version">
			<sup><?php echo esc_html( ( new \Locale\Plugin() )->version() ); ?></sup>
		</small>
	</h1>


	<div id="inpsyde-tabs" class="inpsyde-tabs">
		<?php require_once \Locale\Functions\get_template( '/views/options-page/navigation.php' ); ?>

		<section id="tab--connection" class="inpsyde-tab__content inpsyde-tabs--connection">
			<h2 class="screen-reader-text"><?php esc_html_e( 'Credentials', 'locale' ); ?></h2>
			<?php include \Locale\Functions\get_template( '/views/options-page/tabs/connection.php' ) ?>
		</section>
	</div>
</div>
