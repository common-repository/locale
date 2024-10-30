<?php

namespace Locale\Locale\Hooks\Meta;

use function add_post_meta;
use function get_term;
use function update_post_meta;
use Locale\Utils\NetworkState;
use Locale\Locale\Hooks\Action;
use Locale\Locale\Entities\WordpressPost;
use Locale\Locale\Entities\JobTaxonomy;

/**
 * When the `_locale_order_imported_at` meta of a job gets set with a value, each
 * of the posts under it will have their `_post_is_translated` meta set to (int) 1.
 *
 * @package Locale\Locale\Hooks\Meta
 *
 * @author Peter Cortez <peter@locale.to>
 */
class MarkJobItemsAsTranslated extends Action
{
    /**
     * @inheritdoc
     */
    protected $hookArgsNames = [
        'metaId',
        'objectId',
        'metaKey',
        'metaValue',
    ];

    /**
     * @var int
     */
    protected $metaId;

    /**
     * @var int
     */
    protected $objectId;

    /**
     * @var string
     */
    protected $metaKey;

    /**
     * @var mixed
     */
    protected $metaValue;

    /**
     * @var \Locale\Utils\NetworkState
     */
    protected $networkState;

    public function __construct()
    {
        $this->networkState = NetworkState::create();
    }

    /**
     * @inheritDoc
     */
    protected function shouldRun()
    {
        return $this->metaKey === '_locale_order_imported_at'
            && $this->metaValue !== null;
    }

    /**
     * @inheritDoc
     */
    protected function handle()
    {
        $jobItems = JobTaxonomy::getItemsIncludingOrigPost(
            get_term($this->objectId)
        );

        foreach ($jobItems as $jobItem) {
            $post = (new WordpressPost($jobItem))->getSourcePost();

            if (WordpressPost::isJobItem($jobItem)) {
                $this->networkState->switch_to($jobItem->_locale_target_id);
                add_post_meta($post->ID, '_post_is_translated', 1, true);
                $this->networkState->restore();
            } else {
                add_post_meta($post->ID, '_post_is_translated', 1, true);
            }
        }
    }
}
