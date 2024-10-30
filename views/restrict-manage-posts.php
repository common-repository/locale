<div id="locale_language_overlay" class="locale-language-overlay">
	<div class="locale-lang-popup">
		<a class="close" href="#">&times;</a>
		<div class="content">
            <h2><?php esc_html_e( 'Do you want to start the translation?', 'locale' ) ?></h2>
            <div id="locale-lang-wrap-div" style="display: none;">
				<h2><?php esc_html_e( 'Select languages:', 'locale' ) ?></h2>

				<?php foreach ( \Locale\Functions\get_languages_by_site_id( get_current_blog_id() ) as $lang_key => $lang ) : ?>
					<input type="checkbox"
					       name="locale_bulk_languages[]"
					       value="<?php echo esc_attr( $lang_key ) ?>"
                           checked="checked" />
					<?php echo esc_html( $lang->get_label() ); ?>
				<?php endforeach; ?>
			</div>

            <input type="hidden" name="locale_job_id" value="-1">

            <br /><br /><br />
            <input type="submit" name="locale_submit_bulk_translate" id="locale_submit_bulk_translate" class="button button-primary" value="OK">
            <input type="button" name="locale_cancel_bulk_translate" id="locale_cancel_bulk_translate" class="cancel button" value="Cancel">
		</div>
	</div>
</div>
