<?php

/**
 * Handling the job endpoint of the API.
 *
 * @since   1.0.0
 *
 * @package Locale\Api
 */

namespace Locale\Api;

use Locale\Api;
use Locale\Domain;

/**
 * Class Job
 *
 * @since   1.0.0
 *
 * @package Locale\Api
 */
class Job
{
    /**
     * Endpoint Url
     *
     * @since 1.0.0
     *
     * @var string The endpoint for the job
     */
    const URL = 'job';

    /**
     * Api
     *
     * @since 1.0.0
     *
     * @var Api The instance of the api
     */
    private $api;

    /**
     * Job constructor
     *
     * @param \Locale\Api $api he instance of the api.
     *
     * @since 1.0.0
     */
    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    /**
     * Create a new job.
     *
     * @param \Locale\Domain\Job $job The job info needed to create the job in the server.
     *
     * @return int|null ID of the new job or NULL on failure.
     * @throws ApiException In case the job cannot be created.
     *
     * @since 1.0.0
     */
    public function create(Domain\Job $job)
    {
        $response = $this->api->post(self::URL, [], $job->to_header_array());

        if (!isset($response['id'])) {
            throw new ApiException(
                esc_html_x(
                    'The server response does not contain a job item ID.',
                    'api-response',
                    'locale'
                )
            );
        }

        return (int)$response['id'];
    }

    /**
     * Update Status
     *
     * @param int $job_id The ID of the job for which update the status.
     * @param string $status The new status.
     *
     * @return mixed Depending on the request response.
     * @throws ApiException If the response code isn't a valid one.
     *
     * @since 1.0.0
     */
    public function update_status($job_id, $status)
    {
        return $this->api->patch(
            'transition/' . self::URL . '/' . $job_id,
            [],
            ['X-Item-Status' => $status]
        );
    }

    /**
     * Get Job
     *
     * @param string $job_id The ID of the job to retrieve from the server.
     *
     * @return mixed Depending on the request response.
     * @since 1.0.0
     */
    public function get($job_id)
    {
        return $this->api->get('job/' . (int)$job_id);
    }
}
