<?php

namespace Locale\Locale\Support;

use Locale\Locale\Exceptions\HttpRequestUnavailableWithoutApiKeyException;
use Locale\Setting\PluginSettings;
use WP_Error;
use function array_map;
use function get_site_option;
use function getenv;
use function implode;
use function json_decode;
use function rtrim;
use function wp_remote_get;
use function wp_remote_post;

/**
 * Used to make an HTTP Request to any Locale APIs
 *
 * @package Locale\Locale\Support
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class HttpRequest
{
    /**
     * The Locale API URL.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * The configured API Key
     *
     * @var string
     */
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            getenv('LOCALE_BASE_URL') !== false
                ? getenv('LOCALE_BASE_URL')
                : get_site_option('locale_api_url'),
            '/'
        );
        $this->apiKey = get_option(PluginSettings::API_KEY);

        if (empty($this->apiKey)) {
            throw new HttpRequestUnavailableWithoutApiKeyException;
        }
    }

    /**
     * Makes a POST request to the Locale API's given segment.
     *
     * @param string $segment
     * @param array $args
     *
     * @return array|\WP_Error
     */
    public function post($segment, $args = array())
    {
        $args['headers'] = $this->getHeadersWithBearerToken($args);

        return $this->response(wp_remote_post($this->url($segment), $args));
    }

    /**
     * Makes a GET request to the Locale API's given segment.
     *
     * @param string $segment
     * @param array $args
     *
     * @return array|\WP_Error
     */
    public function get($segment, $args = array())
    {
        $args['headers'] = $this->getHeadersWithBearerToken($args);

        return $this->response(wp_remote_get($this->url($segment), $args));
    }

    /**
     * Returns the headers from the given request argument, then supplies the Bearer
     * token in it.
     *
     * @param $args
     *
     * @return array
     */
    protected function getHeadersWithBearerToken($args)
    {
        $headers = Arr::get($args, 'headers', []);

        if (Arr::get($headers, 'Authorization') !== null) {
            return $headers;
        }

        return ['Authorization' => "Bearer {$this->apiKey}"] + $headers;
    }

    /**
     * Returns the base url appended with the given segment.
     *
     * @param $segment
     *
     * @return string
     */
    protected function url($segment)
    {
        return "{$this->baseUrl}/$segment";
    }

    /**
     * Not under all expected conditions does WordPress return a WP_Error instance
     * when an error occurs with a request. On top of the WP_Error response check,
     * this method checks for the HTTP code in the response, and returns a WP_Error
     * if it is higher than 400.
     *
     * @param array|\WP_Error $res
     *
     * @return array|\WP_Error
     */
    protected function response($res)
    {
        if ($res instanceof WP_Error) {
            return $res;
        }

        $code = Arr::get((array) $res, 'response.code', 200);

        if ($code >= 400) {
            $body = Str::isJson(Arr::get($res, 'body'))
                ? json_decode(Arr::get($res, 'body'), true)
                : [];
            $errors = Arr::where(array_map(
                function ($error) {
                    return Arr::get($error, 'message');
                },
                Arr::get($body, 'errors', [])
            ));

            if (! empty($errors)) {
                $message = implode(', ', $errors);
            } else {
                $message = Arr::get(
                    $res,
                    'response.message',
                    "A {$code} error has occurred with the HTTP request. Please try again later."
                );
            }

            return new WP_Error($code, $message);
        }

        return $res;
    }
}
