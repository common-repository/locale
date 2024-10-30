<?php

/**
 * Order Info
 *
 * @since   1.0.0
 * @package Locale\MetaBox
 */

namespace Locale\View\Job;

use Brain\Nonces\WpNonce;
use DateTime;
use Locale\Functions;
use Locale\Locale\Entities\JobTaxonomy;
use Locale\Utils\TimeZone;
use Locale\View\Viewable;
use function esc_html__;
use function get_term_meta;
use Locale\Locale\Support\Arr;
use Locale\Locale\Entities\TranslationJob;

/**
 * Class OrderInfo
 *
 * @since   1.0.0
 * @package Locale\MetaBox
 */
class OrderInfo implements Viewable
{
    /**
     * Jobs Term ID
     *
     * @var int The ID used to retrieve the jobs associated to this term.
     */
    private $jobs_term_id;

    /**
     * OrderInfo constructor
     *
     * @param int $jobs_term_id The order ID that include job items.
     *
     * @since 1.0.0
     */
    public function __construct($jobs_term_id)
    {
        $this->jobs_term_id = $jobs_term_id;
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        $template = Functions\get_template('views/job/order-info.php');

        if (!$template || !file_exists($template)) {
            return;
        }

        require $template;
    }

    /**
     * Nonce
     *
     * @return \Brain\Nonces\WpNonce The instance of the nonce
     * @since 1.0.0
     */
    private function nonce()
    {
        $action = str_replace('locale_', '', $this->action());

        return new WpNonce($action);
    }

    /**
     * States can be (german):
     *
     * - In Vorbereitung ( In Preparation )
     * - In Arbeit ( In Progress )
     * - Geliefert ( Supplied )
     *
     * @return string The status for the current order.
     * @since 1.0.0
     */
    public function get_status_label()
    {
        $order_status = $this->get_order_status();

        if (!$order_status) {
            return esc_html__('Ready to order', 'locale');
        }

        return Arr::get(
            TranslationJob::HUMAN_READABLE_STATUSES,
            apply_filters('locale_order_status', $order_status, $this),
            apply_filters('locale_order_status', $order_status, $this)
        );
    }

    /**
     * Returns the translation mode of a job, that can is human-readable, and can
     * be presented to the user.
     *
     * @return array|\ArrayAccess|mixed
     */
    public function get_translation_mode_label()
    {
        $translationType = get_term_meta(
            $this->jobs_term_id,
            JobTaxonomy::TRANSLATION_MODE_FIELD_NAME,
            true
        );

        return Arr::get(
            JobTaxonomy::HUMAN_READABLE_TRANSLATION_MODES,
            $translationType,
            $translationType
        );
    }

    /**
     * Get Order Status
     *
     * @return string The status of the job translation order
     * @since 1.0.0
     */
    private function get_order_status()
    {
        return get_term_meta($this->jobs_term_id, '_locale_order_status', true);
    }

    /**
     * Retrieve the latest request order status Date
     *
     * @return \DateTime|null Null if the value doesn't exists. DateTime instance otherwise.
     * @since 1.0.0
     * @throws \Exception
     */
    private function get_latest_update_request_date()
    {
        $timestamp = get_term_meta(
            $this->jobs_term_id,
            '_locale_order_status_last_update_request',
            true
        );

        if (!$timestamp) {
            return null;
        }

        $date = new DateTime('now', (new TimeZone())->value());
        $date->setTimestamp($timestamp);

        return $date;
    }

    /**
     * Returns REST API ID or Plunet ID.
     *
     * Returns rest ID and as soon as given the plunet ID.
     *
     * @return string The meta value
     * @since 1.0.0
     *
     * TODO return correct number.
     */
    private function get_order_id()
    {
        return get_term_meta($this->jobs_term_id, '_locale_order_id', true);
    }

    /**
     * Get ordered date
     *
     * @return \DateTime
     * @throws \Exception
     * @since 1.0.0
     */
    private function get_ordered_at()
    {
        $posts = Functions\get_job_items($this->jobs_term_id);

        return new DateTime($posts[0]->post_date, (new TimeZone())->value());
    }

    /**
     * Get translated date
     *
     * @return \DateTime
     * @throws \Exception
     * @since 1.0.0
     */
    private function get_translated_at()
    {
        $timestamp = get_term_meta(
            $this->jobs_term_id,
            '_locale_order_translated_at',
            true
        );

        if (!$timestamp) {
            return null;
        }

        $date = new DateTime('now', (new TimeZone())->value());
        $date->setTimestamp($timestamp);

        return $date;
    }

    /**
     * Get imported date
     *
     * @return \DateTime
     * @throws \Exception
     */
    private function get_imported_at()
    {
        $timestamp = get_term_meta(
            $this->jobs_term_id,
            '_locale_order_imported_at',
            true
        );

        if (!$timestamp) {
            return null;
        }

        $date = new DateTime('now', (new TimeZone())->value());
        $date->setTimestamp($timestamp);

        return $date;
    }

    /**
     * Has Jobs
     *
     * @return int The number of jobs within the current order
     * @since 1.0.0
     */
    private function has_jobs()
    {
        $posts = Functions\get_job_items($this->jobs_term_id);

        return count($posts);
    }

    /**
     * Action
     *
     * @return string The action to perform.
     * @since 1.0.0
     */
    private function action()
    {
        if ($this->get_translated_at() instanceof DateTime) {
            return 'locale_import_job';
        }

        if ($this->get_order_id()) {
            return 'locale_update_job';
        }

        return 'locale_order_job';
    }
}
