<?php

namespace Locale\Locale\Entities;

use Locale\Api\ApiException;
use Locale\Locale\Support\Arr;
use Locale\Locale\Support\HttpRequest;
use function json_decode;

/**
 * Represents a translation job from Locale
 *
 * @package Locale\Locale\Entities
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class TranslationJob
{
    /**
     * @var string
     */
    const STATUS_INITIALIZED = 'initialized';

    /**
     * @var string
     */
    const STATUS_IN_PROGRESS = 'inProgress';

    /**
     * @var string
     */
    const STATUS_DELIVERED = 'delivered';

    /**
     * @var string
     */
    const STATUS_IMPORTED = 'imported';

    /**
     * A list of a translation job's humanized status, to be used for display to the
     * user.
     *
     * @var array
     */
    const HUMAN_READABLE_STATUSES = [
        self::STATUS_INITIALIZED => 'Initialized',
        self::STATUS_IN_PROGRESS => 'In Progress',
        self::STATUS_DELIVERED => 'Delivered',
        self::STATUS_IMPORTED => 'Imported',
    ];

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $isLiveUpdate;

    /**
     * The links associated with the translation job.
     *
     * @var array
     */
    protected $links = [];

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Finds the translation job with the given id, and returns an instance of this
     * class.
     *
     * @param $id
     *
     * @return \Locale\Locale\Entities\TranslationJob
     */
    public static function find($id)
    {
        $job = new static($id);

        return $job->hydrate();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @see Issue#1334 Automatically set the job's status to "in progress" when
     *      it is initialized
     *
     * @param string $status
     *
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status === self::STATUS_INITIALIZED
            ? self::STATUS_IN_PROGRESS
            : $status;
    }

    /**
     * @return string
     */
    public function isLiveUpdate()
    {
        return $this->isLiveUpdate;
    }

    /**
     * @param string $isLiveUpdate
     *
     * @return void
     */
    public function setIsLiveUpdate($isLiveUpdate)
    {
        $this->isLiveUpdate = $isLiveUpdate;
    }

    /**
     * @param array $links
     *
     * @return void
     */
    public function setLinks(array $links)
    {
        $this->links = $links;
    }

    /**
     * Returns the download link for a JSON file containing the translation strings.
     *
     * @return array|\ArrayAccess|mixed
     */
    public function getDownloadLink()
    {
        return Arr::get($this->links, 'download');
    }

    /**
     * Determines whether the translation job already has a download link ready for
     * use.
     *
     * @return bool
     */
    public function hasDownloadLink()
    {
        return $this->getDownloadLink() !== null;
    }

    /**
     * Updates the translation job instance's status and live update properties.
     *
     * @return \Locale\Locale\Entities\TranslationJob
     */
    public function hydrate()
    {
        $request = new HttpRequest();
        $response = $request->get("jobs/{$this->getId()}");

        if (is_wp_error($response)) {
            throw new ApiException($response->get_error_message());
        }

        $responseBody = json_decode($response['body'], true);
        $links = Arr::get($responseBody, 'links', []);

        $this->setIsLiveUpdate($responseBody['livemode']);
        $this->setStatus($responseBody['status']);
        $this->setLinks($links);

        return $this;
    }
}
