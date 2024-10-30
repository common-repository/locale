<?php

/**
 * Job Item Table List
 *
 * @since   1.0.0
 * @package Locale\TableList
 */

namespace Locale\TableList;

use Locale\Exception\UnexpectedEntityException;
use Locale\Functions;
use Locale\Request;
use WP_Query;
use WP_Term;
use WP_User;

/**
 * Class JobItem
 *
 * @since   1.0.0
 * @package Locale\TableList
 */
final class JobItem extends TableList
{

    /**
     * JobItem constructor
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct(
            [
                'plural' => 'posts',
                'singular' => 'post',
                'ajax' => false,
                'screen' => 'job_item',
            ]
        );

        $this->items = [];
    }

    /**
     * @inheritdoc
     */
    public function has_items()
    {
        return count($this->items());
    }

    /**
     * @inheritdoc
     */
    public function column_cb($post)
    {
        if (!current_user_can('edit_post', $post->ID)) {
            return;
        }
        ?>

        <label class="screen-reader-text" for="cb-select-<?php echo esc_attr($post->ID); ?>">
            <?php
            echo esc_html(
                sprintf(__('Select %s', 'locale'), $post->post_title)
            );
            ?>
        </label>

        <input id="cb-select-<?php echo esc_attr($post->ID); ?>"
               type="checkbox"
               name="post[]"
               value="<?php echo esc_attr($post->ID); ?>"/>

        <?php
    }

    /**
     * @inheritdoc
     */
    public function get_columns()
    {
        $columns = parent::get_columns();

        $columns = $this->column_job($columns);
        $columns = $this->column_languages($columns);

        $columns['locale_added_by'] = esc_html__('Added by', 'locale');
        $columns['locale_added_at'] = esc_html__('Added on', 'locale');

        return $columns;
    }

    /**
     * Filter Sortable Columns
     *
     * @return array The filtered sortable columns
     * @since 1.0.0
     */
    public function get_sortable_columns()
    {
        return [
            'locale_added_by' => 'locale_added_by',
            'locale_added_at' => 'locale_added_at',
            'locale_target_language_column' => 'locale_target_language_column',
        ];
    }

    /**
     * @inheritdoc
     */
    public function prepare_items()
    {
        add_action(
            'pre_get_posts',
            function (WP_Query &$query) {

                // Filter By Language.
                $lang_id = filter_input(
                    INPUT_POST,
                    'locale_target_language_filter',
                    FILTER_SANITIZE_NUMBER_INT
                );
                if ($lang_id && 'all' !== $lang_id) {
                    $query->set(
                        'meta_query',
                        [
                            [
                                'key' => '_locale_target_id',
                                'value' => intval($lang_id),
                                'compare' => '=',
                            ],
                        ]
                    );
                }

                // Filter By User ID.
                $user_id = filter_input(
                    INPUT_POST,
                    'locale_added_by_filter',
                    FILTER_SANITIZE_NUMBER_INT
                );
                if ($user_id && 'all' !== $user_id) {
                    $query->set('author', $user_id);
                }
            }
        );

        (new Request\JobItemBulk())->handle();

        $this->set_pagination();
    }

    /**
     * @inheritdoc
     */
    public function views()
    {
        /**
         * Action Job Item Table Views
         *
         * Fired before the table list.
         *
         * @param \Locale\TableList\JobItem $this Instance of this class.
         *
         * @since 1.0.0
         */
        do_action('locale_job_item_table_views', $this);
    }

    /**
     * Fill the Items list with posts instances
     *
     * @return array A list of \WP_Post elements
     * @since 1.0.0
     */
    public function items()
    {
        if (!$this->items) {
            try {
                $job = $this->job_id_by_request();

                if (!$job) {
                    return [];
                }
            } catch (UnexpectedEntityException $e) {
                return [];
            }

            $this->items = Functions\get_job_items(
                $job->term_id,
                [
                    'posts_per_page' => $this->get_items_per_page("edit_{$this->screen->id}_per_page"),
                    'paged' => filter_input(INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT),
                ]
            );
        }

        return $this->items;
    }

    /**
     * @inheritdoc
     */
    protected function extra_tablenav($which)
    {
        ?>
        <div class="alignleft actions">
            <?php
            if ('top' === $which && !is_singular()) {
                ob_start();

                do_action('restrict_manage_job', $this->screen->id, $which);

                // Filters.
                $this->target_language_filter_template();
                $this->added_by_filter_template();

                $output = ob_get_clean();

                if (!empty($output)) {
                    echo Functions\kses_post($output); // phpcs:ignore
                    submit_button(
                        esc_html__('Filter', 'locale'),
                        '',
                        'filter_action',
                        false,
                        ['id' => 'post-query-submit']
                    );
                }
            }
            ?>
        </div>
        <?php
    }

    /**
     * @inheritdoc
     */
    protected function get_bulk_actions()
    {
        if (current_user_can('manage_options')) {
            $actions['trash'] = esc_html__('Remove from job', 'locale');
        }

        return $actions;
    }

    /**
     * Job Column
     *
     * @param \WP_Post $item The post instance.
     *
     * @param          $column_name
     *
     * @since 1.0.0
     */
    protected function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'locale_source_language_column':
                $languages = Functions\current_language();

                if ($languages) {
                    echo esc_html($languages->get_label());
                    break;
                }

                // In case of failure.
                echo esc_html__('Unknown', 'locale');
                break;

            case 'locale_target_language_column':
                $lang_id = get_post_meta($item->ID, '_locale_target_id', true);
                $languages = Functions\get_languages();

                if ($lang_id && isset($languages[$lang_id])) {
                    printf(
                        '<a href="%1$s">%2$s</a>',
                        esc_url(get_blog_details(intval($lang_id))->siteurl),
                        esc_html($languages[$lang_id]->get_label())
                    );
                    break;
                }

                // In case of failure.
                echo esc_html__('Unknown', 'locale');
                break;

            case 'locale_added_by':
                $user = new WP_User(get_post($item->ID)->post_author);
                echo esc_html(Functions\username($user));
                break;

            case 'locale_added_at':
                echo esc_html(
                    get_the_date(
                        get_option('date_format') . ' ' . get_option('time_format'),
                        $item->ID
                    )
                );
                break;
        }
    }

    /**
     * Set languages found in posts
     *
     * The function store all of the target languages found in the job items.
     * This list is then used to build the target language filter.
     *
     * @return array A list of Languages instances
     * @since 1.0.0
     */
    private function languages()
    {
        static $languages = [];

        if (empty($languages)) {
            $all_languages = Functions\get_languages();

            foreach ($all_languages as $index => $language) {
                $languages[$index] = esc_html($language->get_label());
            }
        }

        return $languages;
    }

    /**
     * Retrieve Users
     *
     * @return array An array of \WP_Users instances
     * @since 1.0.0
     */
    private function users()
    {
        static $users = null;

        if (null === $users) {
            $users = get_users(
                [
                    'fields' => 'all',
                ]
            );

            $users = $this->filter_users_by_items($users);
        }

        return $users;
    }

    /**
     * Filter users that are also post authors for the items in the list
     *
     * @param \WP_User[] $users The users list to filter.
     *
     * @return array The filtered users
     * @since 1.0.0
     */
    private function filter_users_by_items($users)
    {
        // Retrieve all of the users that has an item.
        $userItems = array_map(
            function ($item) {

                return (int)$item->post_author;
            },
            $this->items
        );

        // Filter the user that has an item associated.
        $users = array_filter(
            $users,
            function ($user) use ($userItems) {

                return in_array($user->ID, $userItems, true);
            }
        );

        return $users;
    }

    /**
     * The Target Language Filter
     *
     * @return void
     * @since 1.0.0
     */
    private function target_language_filter_template()
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        $bind = (object)[
            'class_attribute' => 'target-language-filter',
            'name_attribute' => 'locale_target_language_filter',
            'options' => [
                    'all' => esc_html__('All Languages', 'locale'),
                ] + $this->languages(),
            'current_value' => (int)filter_input(
                INPUT_POST,
                'locale_target_language_filter',
                FILTER_SANITIZE_STRING
            ),
        ];

        include Functions\get_template('/views/type/select.php');
    }

    /**
     * The User Filter
     *
     * @return void
     * @since 1.0.0
     */
    private function added_by_filter_template()
    {
        $users = [];
        foreach ($this->users() as $user) {
            $users[$user->ID] = Functions\username($user);
        }

        /** @noinspection PhpUnusedLocalVariableInspection */
        $bind = (object)[
            'class_attribute' => 'added-by-filter',
            'name_attribute' => 'locale_added_by_filter',
            'options' => ['all' => esc_html__('All Users', 'locale')] + $users,
            'current_value' => (int)filter_input(
                INPUT_POST,
                'locale_added_by_filter',
                FILTER_SANITIZE_STRING
            ),
        ];
        unset($users);

        include Functions\get_template('/views/type/select.php');
    }

    /**
     * Filter Job Column
     *
     * @param array $columns The columns items to filter.
     *
     * @return array The filtered columns
     * @since 1.0.0
     */
    private function column_job($columns)
    {
        static $request = null;

        if (null === $request) {
            $request = $_GET;
            foreach ($request as $key => $val) {
                $request[$key] = sanitize_text_field(filter_input(
                    INPUT_GET,
                    $key,
                    FILTER_SANITIZE_STRING
                ));
            }

            $request = wp_parse_args(
                $request,
                [
                    'locale_job_id' => '-1',
                ]
            );
        }

        if (isset($request['post_status']) && 'trash' === $request['post_status']) {
            // This is trash so we show no job column.
            return $columns;
        }

        if ($request['locale_job_id']) {
            // Term/Job filter is active so this col is not needed.
            return $columns;
        }

        $columns['locale_job'] = esc_html__('Job', 'locale');

        return $columns;
    }

    /**
     * Filter Column Language
     *
     * @param array $columns The columns items to filter.
     *
     * @return array The filtered columns
     * @since 1.0.0
     */
    private function column_languages($columns)
    {
        $columns['locale_source_language_column'] = esc_html__(
            'Source Language',
            'locale'
        );
        $columns['locale_target_language_column'] = esc_html__(
            'Target Language',
            'locale'
        );

        return $columns;
    }

    /**
     * Set Pagination
     *
     * @return void
     * @since 1.0.0
     */
    private function set_pagination()
    {
        try {
            $job_id = $this->job_id_by_request()->term_id;
        } catch (UnexpectedEntityException $e) {
            return;
        }

        if (!$job_id) {
            return;
        }

        $count = count(Functions\get_job_items($job_id));

        $this->set_pagination_args(
            [
                'total_items' => $count,
                'per_page' => $this->get_items_per_page("edit_{$this->screen->id}_per_page"),
            ]
        );
    }

    /**
     * Retrieve Job ID By GET request
     *
     * @return WP_Term The term retrieved by the request.
     * @throws UnexpectedEntityException
     */
    private function job_id_by_request()
    {
        $job = Functions\filter_input(
            ['locale_job_id' => FILTER_SANITIZE_NUMBER_INT],
            INPUT_GET
        );

        if (is_array($job) && array_key_exists('locale_job_id', $job)) {
            $job = $job['locale_job_id'];
        }

        if (!$job) {
            return null;
        }

        $job = get_term($job, 'locale_job');

        if (!$job instanceof WP_Term) {
            throw UnexpectedEntityException::forTermValue($job, '');
        }

        return $job;
    }
}
