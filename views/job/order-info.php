<div id="postbox-container-1" class="postbox-container locale-postbox-container">
	<div id="submitdiv" class="postbox">

		<h2 class="box-status-title">
			<?php esc_html_e( 'Status', 'locale' ); ?>
		</h2>

		<div class="inside">
			<form id="locale_order_or_update_jobs"
			      class="locale-order-or-update-jobs"
			      method="post"
			      action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

				<ul class="locale-order-info">
					<li class="locale-order-info-item">
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'Status', 'locale' ); ?>:
						<b>
							<?php echo esc_html( $this->get_status_label() ); ?>
						</b>
					</li>

					<?php if ( $this->get_order_id() ): ?>
						<li class="locale-order-info-item">
							<span class="dashicons dashicons-testimonial"></span>
							<?php esc_html_e( 'Job number', 'locale' ) ?>:
							<b>
								<?php echo esc_html( $this->get_order_id() ) ?>
							</b>
						</li>

                        <li class="locale-order-info-item">
                            <span class="dashicons dashicons-admin-settings"></span>
                            <?php esc_html_e( 'Mode', 'locale' ); ?>:
                            <b>
                                <?php echo esc_html( $this->get_translation_mode_label() ); ?>
                            </b>
                        </li>

						<?php if ( $this->get_ordered_at() instanceof \DateTime ) : ?>
							<li class="locale-order-info-item">
								<span class="dashicons dashicons-calendar-alt"></span>
								<?php esc_html_e( 'Ordered on', 'locale' ) ?>:
								<b>
									<?php echo esc_html(
										$this->get_ordered_at()
										     ->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) )
									); ?>
								</b>
							</li>
						<?php endif; ?>

						<?php if ( $this->get_translated_at() instanceof \DateTime &&  is_null($this->get_imported_at()) ) : ?>
							<li class="locale-order-info-item">
								<span class="dashicons dashicons-calendar-alt"></span>
								<?php esc_html_e( 'Translated on', 'locale' ); ?>:
								<b>
									<?php echo esc_html(
										$this->get_translated_at()
										     ->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) )
									); ?>
								</b>
							</li>

							<input type="submit"
							       name="locale_import_job_translation"
							       class="button button-primary"
							       onclick="jQuery('#locale_action_job_update').click();"
							       value="<?php esc_html_e( 'Import', 'locale' ); ?>"/>
						<?php endif; ?>

						<?php if ( $this->get_latest_update_request_date() instanceof \DateTime ) : ?>
							<li class="locale-order-info-item">
								<span class="dashicons dashicons-calendar-alt"></span>
								<?php esc_html_e( 'Latest update on', 'locale' ); ?>:
								<b>
									<?php echo esc_html(
										$this->get_latest_update_request_date()
										     ->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) )
									); ?>
								</b>
							</li>
						<?php endif; ?>

                        <?php if ( $this->get_imported_at() instanceof \DateTime ) : ?>
							<li class="locale-order-info-item">
								<span class="dashicons dashicons-calendar-alt"></span>
								<?php esc_html_e( 'Imported on', 'locale' ); ?>:
								<b>
									<?php echo esc_html(
										$this->get_imported_at()
										     ->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) )
									); ?>
								</b>
							</li>
						<?php endif; ?>

						<?php if ( ! $this->get_translated_at() ) : ?>
							<input type="submit"
							       name="locale_action_job_update"
							       class="button button-primary"
							       onclick="jQuery('#locale_action_job_update').click();"
							       value="<?php esc_html_e( 'Update', 'locale' ); ?>"/>
						<?php endif; ?>
					<?php endif; ?>
				</ul>

				<?php if ( ! $this->get_order_id() ) : ?>
					<?php if ( ! $this->has_jobs() ) {
						printf(
							'<p class="no-jobs-found">%s</p>',
							esc_html__( 'Please add at least one post in order to submit the job.', 'locale' )
						);
					}
					?>
					<input type="submit"
					       name="locale_action_job_order"
					       id="locale_action_job_order"
					       class="button button-primary"
						<?php echo( ! $this->has_jobs() ? ' disabled="disabled" ' : '' ); ?>
						   value="<?php esc_attr_e( 'Place Order', 'locale' ); ?>"/>
				<?php endif; ?>

				<input type="hidden" name="action" value="<?php echo esc_attr( $this->action() ) ?>">
				<input type="hidden"
				       name="locale_job_id"
				       value="<?php echo filter_input( INPUT_GET, 'locale_job_id', FILTER_SANITIZE_NUMBER_INT ); ?>">
				<input type="hidden"
				       name="<?php echo esc_attr( $this->nonce()->action() ) ?>"
				       value="<?php echo esc_attr( $this->nonce() ) ?>"/>
			</form>
		</div>
	</div>
</div>
