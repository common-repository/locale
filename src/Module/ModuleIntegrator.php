<?php

/**
 * Class Loader
 *
 * @since   1.0.0
 * @package Locale\Module
 */

namespace Locale\Module;

use Traversable;

/**
 * Class Loader
 *
 * @package Locale\Module
 */
class ModuleIntegrator
{
    const POST_DATA_NAMESPACE = 'POST';

    /**
     * @var array
     */
    private $modulesProvider;

    /**
     * Loader constructor
     *
     * @param Traversable $modulesProvider
     */
    public function __construct(Traversable $modulesProvider)
    {
        $this->modulesProvider = $modulesProvider;
    }

    /**
     * Register the integrations instances
     */
    public function integrate()
    {
        /** @var Integrable $moduleInstance */
        foreach ($this->modulesProvider as $pluginFilePath => $moduleInstance) {
            if (!$moduleInstance instanceof Integrable) {
                continue;
            }

            $moduleInstance->integrate();
        }
    }
}
