<?php

namespace Locale\Locale\Support;

use Exception;
use Locale\Api\ApiException;
use Locale\Locale\Entities\JobTaxonomy;
use Locale\Locale\Entities\TranslationJob;
use Locale\Locale\Exceptions\UnauthorizedProjectCreationException;
use Locale\Locale\Support\Contracts\Arrayable;
use WP_Error;
use WP_Term;
use function array_map;
use Locale\Locale\Entities\Translation;
use function is_wp_error;
use function json_decode;
use function json_encode;

/**
 * Represents a translation request job from Locale.
 *
 * Given a set of WordPress post translators, this will request for a translation job
 * which can then be retrieved later.
 *
 * @package Locale\Locale\Support
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class TranslationJobRequest implements Arrayable
{
    /**
     * The translations associated with a post's latest revision.
     *
     * @var \Locale\Locale\Entities\Translation[]
     */
    protected $current = [];

    /**
     * The translations associated with a post's last published revision.
     *
     * @var \Locale\Locale\Entities\Translation[]
     */
    protected $previous = [];

    /**
     * @var \WP_Term
     */
    protected $job;

    /**
     * @param \WP_Term $job
     */
    public function __construct(WP_Term $job)
    {
        $this->job = $job;
    }

    /**
     * Adds a current translation
     *
     * @param \Locale\Locale\Entities\Translation $translation
     *
     * @return void
     */
    public function add(Translation $translation)
    {
        $this->current[] = $translation;
    }

    /**
     * Adds a previous translation
     *
     * @param \Locale\Locale\Entities\Translation $translation
     *
     * @return void
     */
    public function addPrevious(Translation $translation)
    {
        $this->previous[] = $translation;
    }

    /**
     * @inheritDoc
     *
     * @see https://docs.locale.to/#operation/createJob
     */
    public function toArray()
    {
        $current = [
            'segments' => array_map(
                function ($c) { return $c->toArray(); },
                $this->current
            ),
        ];
        $previous = [
            'segments' => array_map(
                function ($p) { return $p->toArray(); },
                $this->previous
            ),
        ];

        return JobTaxonomy::isIncremental($this->job)
            ? ['current' => $current, 'previous' => $previous]
            : $current;
    }

    /**
     * Submits the translation job to Locale for processing.
     *
     * @return \Locale\Locale\Entities\TranslationJob|void
     */
    public function submit()
    {
        if (empty($this->current) && empty($this->previous)) {
            return;
        }

        $request = new HttpRequest;
        $response = $request->post(
            'jobs',
            ['body' => json_encode($this->toArray())]
        );

        if (is_wp_error($response)) {
            if ($response->get_error_code() === 401) {
                throw new UnauthorizedProjectCreationException;
            }
            throw new ApiException($response->get_error_message());
        }

        $responseBody = json_decode($response['body']);
        $job = TranslationJob::find($responseBody->id);
        $job->setStatus($responseBody->status);
        $job->setIsLiveUpdate($responseBody->livemode);

        return $job;
    }
}
