<?php

namespace Locale\Request\Api;

use Brain\Nonces\NonceInterface;
use Exception;
use Locale\Locale\Entities\JobTaxonomy;
use Locale\JobHandler;
use Locale\JobUpdater;
use Locale\Request\RequestHandleable;
use Locale\Auth\Authable;
use Locale\Notice\TransientNoticeService;

use function get_term;
use function Locale\Functions\redirect_admin_page_network;

/**
 * Class AddTranslation
 *
 * @since   1.0.0
 * @package Locale\Request
 */
class AddTranslation implements RequestHandleable
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
     * AddTranslation constructor
     *
     * @param \Locale\Auth\Authable $auth The instance to use to verify the request.
     * @param \Brain\Nonces\NonceInterface $nonce The instance to use to verify the request.
     *
     * @param \Locale\JobHandler $job_handler
     *
     * @since 1.0.0
     */
    public function __construct(
        Authable $auth,
        NonceInterface $nonce,
        JobHandler $job_handler
    ) {

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
            return;
        }

        // Better use object than array.
        $data = (object)$data;

        // @todo What about using `JobUpdater` directly instead of via hook?
        $updater = new JobUpdater();
        $updater->init();

        try {
            $job = isset($data->locale_job_id) ? $data->locale_job_id : '-1';

            if ('-1' === $job) {
                $job = JobHandler::create_job_using_date();
            }

            /**
             * Runs before adding translations to the job.
             *
             * You might add other things to the job before the translations kick in
             * or check against some other things (like account balance) to stop adding things to the job
             * and show some error message.
             *
             * For those scenarios this filter allows turn it's value into false.
             * In that case it will neither add things to the job/job
             * nor redirect to the job- / job-view.
             *
             * @param bool $valid Initially true and can be torn to false to stop adding items to the job.
             * @param int $job ID of the job (actually a term ID).
             * @param int $post_ID The post ID for the post to translate.
             * @param array $locale_language The language in which translate the post.
             *
             * @see wp_insert_post() actions and filter to access each single transation that is added to job.
             */
            $valid = apply_filters(
                'locale_filter_before_add_to_job',
                true,
                $job,
                $data->post_ID,
                $data->locale_language
            );

            if (true !== $valid) {
                return;
            }

            // Remember the last manipulated job.
            update_user_meta(get_current_user_id(), 'locale_job_recent', $job);

            // Iterate translations.
            $jobTaxonomy = JobTaxonomy::incremental(get_term($job));
            foreach ($data->locale_language as $lang_id) {
                $jobTaxonomy->addTranslation((int)$data->post_ID, $lang_id);
            }

            /**
             * Action
             *
             * After adding posts to a job / job it will redirect to this job.
             * One last time you can filter to which job it will redirect (by using the ID)
             * or if should'nt redirect at all (by setting the value to "false").
             *
             * @param int $job ID of the job (actually a term ID).
             * @param int $post_id ID of the post that will be added to the job.
             * @param int[] $languages IDs of the target languages (assoc pair).
             *
             * @see \Locale\Functions\action_job_add_translation() where this filter resides.
             * @see \Locale\Functions\get_languages() how languages are gathered.
             */
            do_action(
                'locale_action_job_add_translation',
                $job,
                $data->post_ID,
                $data->locale_language
            );

            $notice = [
                'message' => esc_html__(
                    'New translation added successfully.',
                    'locale'
                ),
                'severity' => 'success',
            ];
        } catch (Exception $e) {
            $notice = [
                'message' => $e->getMessage(),
                'severity' => 'error',
            ];
        }

        TransientNoticeService::add_notice($notice['message'], $notice['severity']);

        redirect_admin_page_network(
            'admin.php',
            [
                'page' => 'locale-job',
                'locale_job_id' => $job,
                'post_type' => 'job_item',
                'updated' => -1,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function is_valid_request()
    {
        if (!isset($_POST['locale_action_job_add_translation'])) { // phpcs:ignore
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
        return \Locale\Functions\filter_input(
            [
                'locale_job_id' => FILTER_SANITIZE_NUMBER_INT,
                'post_ID' => FILTER_SANITIZE_NUMBER_INT,
                'locale_language' => [
                    'filter' => FILTER_SANITIZE_STRING,
                    'flags' => FILTER_FORCE_ARRAY,
                ],
            ],
            INPUT_POST
        );
    }
}
