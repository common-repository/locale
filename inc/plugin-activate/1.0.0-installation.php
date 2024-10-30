<?php
/**
 * 1.0.0 Activation
 */

if ( ! get_site_option( 'locale_api_url' ) ) {
    update_site_option('locale_api_url', 'https://api.locale.to/v1/');
}
