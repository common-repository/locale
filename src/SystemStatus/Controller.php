<?php

/**
 * System Status
 *
 * @since   1.0.0
 * @package Locale
 */

namespace Locale\SystemStatus;

use Inpsyde\SystemStatus\Builder;

/**
 * Class Controller
 *
 * @since   1.0.0
 * @package Locale
 */
class Controller
{
    /**
     * Information Class Names
     *
     * @since 1.0.0
     *
     * @var array The list of the information we want to show to the user.
     */
    private static $informations = [
        '\\Inpsyde\\SystemStatus\\Data\\Php',
        '\\Locale\\SystemStatus\\Locale',
        '\\Inpsyde\\SystemStatus\\Data\\Wordpress',
        '\\Inpsyde\\SystemStatus\\Data\\Database',
        '\\Inpsyde\\SystemStatus\\Data\\Plugins',
    ];

    /**
     * Create the System Status instance with information
     *
     * @return \Inpsyde\SystemStatus\Builder
     * @since 1.0.0
     */
    public function system_status()
    {
        return new Builder(self::$informations, 'table');
    }

    /**
     * Render System Status
     *
     * @return void
     * @since 1.0.0
     */
    public function render()
    {
        $this->system_status()
            ->build()
            ->render();
    }
}
