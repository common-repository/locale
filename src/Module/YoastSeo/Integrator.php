<?php

/**
 * Class Integrate
 *
 * @since   1.0.0
 * @package Locale\Module\YoastSeo
 */

namespace Locale\Module\YoastSeo;

use Locale\Module\Integrable;

/**
 * Class Integrate
 *
 * @since   1.0.0
 * @package Locale\Module\YoastSeo
 */
class Integrator implements Integrable
{
    /**
     * @inheritdoc
     */
    public function integrate()
    {
        if (!class_exists('WPSEO_Meta')) {
            return;
        }

        $wordpressSeo = new WordPressSeo();

        add_action('locale_outgoing_data', [$wordpressSeo, 'prepare_outgoing']);
        add_action('locale_updated_post', [$wordpressSeo, 'update_translation']);
    }
}
