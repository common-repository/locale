<select name="locale_job_id" id="locale_job_id">
	<option value="-1">
		<?php esc_html_e( 'New job', 'locale' ); ?>
	</option>

	<?php foreach ( \Locale\Functions\jobs() as $job_id => $job_label ) : ?>
		<option value="<?php esc_attr_e( $job_id ); ?>" <?php selected( ( isset( $current ) ? $current : '' ), $job_id ) ?>>
			<?php esc_html_e( $job_label ); ?>
		</option>
	<?php endforeach; ?>

</select>
