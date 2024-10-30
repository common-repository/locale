<form method="post"
      action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
      class="locale-job-details-form">
	<input type="hidden" name="action" value="locale_job_info_save">
	<input type="hidden"
	       name="locale_job_id"
	       value="<?php echo intval( $this->job->term_id ) ?>">
	<div class="alignleft actions">
		<div class="form-field form-required term-name-wrap">
			<label for="tag-name">
				<?php _ex( 'Name', 'term name' ); ?>
			</label>
			<input name="tag-name"
			       id="tag-name"
			       type="text"
			       value="<?php echo esc_attr( $this->job->name ); ?>"
			       size="40"
			       aria-required="true"/>
		</div>

		<div class="form-field term-description-wrap">
			<label for="description"><?php esc_html_e( 'Description', 'locale' ); ?></label>
			<textarea
				name="description"
				id="description"
				rows="5"
				cols="40"><?php echo esc_attr( $this->job->description ) ?></textarea>
			<p>
				<i>
					<?php esc_html_e( 'Note: Only plain text is allowed. No markup.', 'locale' ); ?>
				</i>
			</p>
		</div>

		<input type="hidden"
		       name="<?php echo esc_attr( $this->nonce->action() ) ?>"
		       value="<?php echo esc_attr( $this->nonce ) ?>"/>

		<input type="submit"
		       name="locale_job_info_save"
		       class="button button-primary"
		       value="<?php esc_html_e( 'Save Job', 'locale' ) ?>">
	</div>
</form>
