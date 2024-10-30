<?php

namespace Locale\Api;

use Locale\Functions;
use Locale\Api;

/**
 * Handling the job endpoint of the API.
 *
 * @package Locale\Api
 */
class JobItem
{
    /**
     * Post URL
     *
     * @since 1.0.0
     *
     * @var string The endpoint for the job item.
     */
    const URL = 'job/%d/item';

    /**
     * Get URL
     *
     * @since 1.1.1
     *
     * @var string Endpoint to fetch information about a job item.
     */
    const URL_GET = 'job/%d/item/%d';

    /**
     * API
     *
     * @since 1.0.0
     *
     * @var Api
     */
    private $api;

    /**
     * JobItem constructor
     *
     * @param \Locale\Api $api The Api instance.
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
     * @param int $job_id
     * @param string $post_type_name
     * @param string $target_language
     * @param array $data
     *
     * @return int|null ID of the new job or NULL on failure.
     * @throws \Locale\Api\ApiException In case the job item cannot be created.
     *
     * @since 1.0.0
     */
    public function create($job_id, $post_type_name, $target_language, $data = [])
    {
        $body = $this->api->post(
            $this->get_url($job_id),
            $data,
            [
                'X-Source' => $this->normalize_language_code(Functions\current_lang_code()),
                'X-Target' => $this->normalize_language_code($target_language),
                'X-TextType' => $this->get_text_type($post_type_name),
                'X-System-Module' => $post_type_name,
            ]
        );

        if (!isset($body['id'])) {
            throw new ApiException(
                esc_html_x(
                    'The server response does not contain a job item ID.',
                    'api-response',
                    'locale'
                )
            );
        }

        return (int)$body['id'];
    }

    /**
     * @param int $job_id Job ID.
     * @param int $item_id Item ID within the Job.
     *
     * @return mixed
     * @since 1.1.1
     */
    public function get($job_id, $item_id)
    {
        return $this->api->get(sprintf(self::URL_GET, $job_id, $item_id));
    }

    /**
     * @param string $job_id Job ID.
     *
     * @return string The url for the request
     * @since 1.0.0
     */
    private function get_url($job_id)
    {
        return sprintf(self::URL, $job_id);
    }

    /**
     * Get the Text-Type based on the Post-Type
     *
     * @param string $post_type_name The post type name.
     *
     * @return string text-type name for REST-API
     * @since 1.0.0
     */
    private function get_text_type($post_type_name)
    {
        $text_type_name = str_replace(
            ['post', 'page'],
            ['marketing', 'specialized-text'],
            $post_type_name
        );

        $text_type_name = apply_filters(
            'locale_get_text_type',
            $text_type_name,
            $post_type_name
        );

        return $text_type_name;
    }

    /**
     * Normalize language code for locale api request
     *
     * @param string $lang_code The language code to normalize.
     *
     * @return string The normalize language code
     * @since 1.0.0
     */
    private function normalize_language_code($lang_code)
    {
        $api_language_codes = apply_filters(
            'locale_get_api_language_codes',
            [
                  'af' => 'afr',
                  'bg-bg' => 'bg',
                  'ca' => 'cat',
                  'cy' => 'wel',
                  'da-dk' => 'da',
                  'ga' => 'gai',
                  'gd' => 'gla',
                  'gl-es' => 'glg',
                  'gu-in' => 'guj',
                  'he-il' => 'he',
                  'hi-in' => 'hin',
                  'hr' => 'hr',
                  'hu-hu' => 'hu',
                  'id-id' => 'ind',
                  'is-is' => 'ice',
                  'mk-mk' => 'mk',
                  'ms-my' => 'msa',
                  'my-mm' => 'my',
                  'pl-PL' => 'pl',
                  'sk-sk' => 'sk',
                  'sl-si' => 'sl',
                  'so-so' => 'som',
                  'sr-rs' => 'sr',
                  'tr-tr' => 'tr',
                  'vi' => 'vn',
            ]
        );

        if (array_key_exists($lang_code, $api_language_codes)) {
            return $api_language_codes[$lang_code];
        }

        return strtolower(str_replace('_', '-', $lang_code));
    }
}
