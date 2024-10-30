<?php

namespace Locale\Request\Api;

use Brain\Nonces\NonceInterface;
use DateTime;
use Exception;
use Locale\Locale\Entities\TranslationJob;
use Locale\Request\RequestHandleable;
use Locale\Auth\Authable;
use Locale\Notice\TransientNoticeService;
use Locale\Utils\TimeZone;
use WP_Term;

use function esc_html__;
use function get_term_meta;
use function Locale\Functions\job_global_status;
use function Locale\Functions\redirect_admin_page_network;
use function Locale\Functions\set_unique_term_meta;

/**
 * Class UpdateJobOrderStatus
 *
 * @since   1.0.0
 * @package Locale\Request
 */
class UpdateJobOrderStatus implements RequestHandleable
{
    /**
     * Auth
     *
     * @since 1.0.0
     *
     * @var Authable The instance to use to verify the request
     */
    private $auth;

    /**
     * Nonce
     *
     * @since 1.0.0
     *
     * @var NonceInterface The instance to use to verify the request
     */
    private $nonce;

    /**
     * User Capability
     *
     * @since 1.0.0
     *
     * @var string The capability needed by the user to be able to perform the request
     */
    private static $capability = 'manage_options';

    /**
     * UpdateJobOrderStatus constructor
     *
     * @param Authable $auth The instance to use to verify the request.
     * @param NonceInterface $nonce The instance to use to verify the request.
     *
     * @since 1.0.0
     */
    public function __construct(Authable $auth, NonceInterface $nonce)
    {
        $this->auth = $auth;
        $this->nonce = $nonce;
    }

    /**
     * @inheritdoc
     */
    public function handle()
    {
        if (!$this->is_valid_request()) {
            return;
        }

        $data = $this->request_data();

        if (!$data) {
            TransientNoticeService::add_notice(
                esc_html__('The request is valid but no data was found.', 'locale'),
                'error'
            );

            return;
        }

        try {
            // Retrieve the job info.
            $job = get_term(
                $data['locale_job_id'],
                'locale_job'
            );
            if (!$job instanceof WP_Term) {
                TransientNoticeService::add_notice(
                    esc_html__('Invalid job name.', 'locale'),
                    'error'
                );

                return;
            }

            // Retrieve the generic status for the translation.
            $prevStatus = get_term_meta($job->term_id, '_locale_order_status', true);
            $status = job_global_status($job);

            $this->update_job_status($job, $status);
            $this->update_job_status_request_date($job);

            if (strtolower($status) === TranslationJob::STATUS_DELIVERED) {
                // Update the translated at meta.
                $this->update_job_translated_at($job);
            }

            if ($prevStatus !== $status) {
                $notice = [
                    'message' => esc_html__("The translation job has been updated with a new status! \u{2728}", 'locale'),
                    'severity' => 'success',
                ];
            }
        } catch (Exception $e) {
            $notice = [
                'message' => $e->getMessage(),
                'severity' => 'error',
            ];
        }

        if (! empty($notice)) {
            TransientNoticeService::add_notice(
                $notice['message'],
                $notice['severity']
            );
        }

        redirect_admin_page_network(
            'admin.php',
            [
                'page' => 'locale-job',
                'locale_job_id' => $job->term_id,
                'post_type' => 'job_item',
                'job_status' => $status,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function is_valid_request()
    {
        if (!isset($_POST['locale_action_job_update'])) { // phpcs:ignore
            return false;
        }

        return $this->auth->can(wp_get_current_user(), self::$capability)
            && $this->auth->request_is_valid($this->nonce);
    }

    /**
     * @inheritdoc
     */
    public function request_data()
    {
        return filter_input_array(
            INPUT_POST,
            [
                'locale_job_id' => FILTER_SANITIZE_NUMBER_INT,
            ]
        );
    }

    /**
     * Update Order Status
     *
     * @param WP_Term $job The term object for which update the meta.
     * @param string $status The value to store as meta value.
     *
     * @return mixed Whatever the *_term_meta returns
     * @since 1.0.0
     */
    private function update_job_status(WP_Term $job, $status)
    {
        return set_unique_term_meta($job, '_locale_order_status', $status);
    }

    /**
     * Update Order Status Last Update Date
     *
     * @param WP_Term $job The term object for which update the meta.
     *
     * @return mixed Whatever the *_term_meta returns
     * @throws Exception
     * @since 1.0.0
     */
    private function update_job_status_request_date(WP_Term $job)
    {
        return set_unique_term_meta(
            $job,
            '_locale_order_status_last_update_request',
            (new DateTime('now', (new TimeZone())->value()))->getTimestamp()
        );
    }

    /**
     * Update Job Translated at meta
     *
     * @param WP_Term $job The term object for which update the meta.
     *
     * @return mixed Whatever the *_term_meta returns
     * @throws Exception
     * @since 1.0.0
     */
    private function update_job_translated_at(WP_Term $job)
    {
        return set_unique_term_meta(
            $job,
            '_locale_order_translated_at',
            (new DateTime('now', (new TimeZone())->value()))->getTimestamp()
        );
    }
}
