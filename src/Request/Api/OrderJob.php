<?php

namespace Locale\Request\Api;

use Brain\Nonces\NonceInterface;
use Locale\Request\RequestHandleable;
use Locale\Api\ApiException;
use Locale\Auth\Authable;
use Locale\Notice\TransientNoticeService;
use WP_Term;

use function Locale\Functions\create_job_order;
use function Locale\Functions\redirect_admin_page_network;

/**
 * Class OrderJob
 *
 * @since   1.0.0
 * @package Locale\Request
 */
class OrderJob implements RequestHandleable
{
    /**
     * Auth
     *
     * @since 1.0.0
     *
     * @var \Locale\Auth\Authable The instance to use to verify the request
     */
    private $auth;

    /**
     * Nonce
     *
     * @since 1.0.0
     *
     * @var \Brain\Nonces\NonceInterface The instance to use to verify the request
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
     * OrderJob constructor
     *
     * @param \Locale\Auth\Authable $auth The instance to use to verify the request.
     * @param \Brain\Nonces\NonceInterface $nonce The instance to use to verify the request.
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

        $job = get_term($data['locale_job_id'], 'locale_job');
        if (!$job instanceof WP_Term) {
            TransientNoticeService::add_notice(
                esc_html__('Invalid job name.', 'locale'),
                'error'
            );

            return;
        }

        try {
            create_job_order($job);

            $notice = [
                'message' => esc_html__(
                    'A new job request has been sent.',
                    'locale'
                ),
                'severity' => 'success',
            ];
        } catch (ApiException $e) {
            $notice = [
                'message' => sprintf(
                    esc_html__('Locale: %s', 'locale'),
                    $e->getMessage()
                ),
                'severity' => 'error',
            ];
        }

        TransientNoticeService::add_notice($notice['message'], $notice['severity']);

        redirect_admin_page_network(
            'admin.php',
            [
                'page' => 'locale-job',
                'locale_job_id' => $data['locale_job_id'],
                'post_type' => 'job_item',
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function is_valid_request()
    {
        if (!isset($_POST['locale_action_job_order'])) { // phpcs:ignore
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
}
