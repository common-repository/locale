<?php

namespace Locale\Locale\Hooks\Support;

/**
 * Facilitates the manipulation and retrieval of the arguments passed to a WordPress
 * hook.
 *
 * @package Locale\Locale\Hooks\Support
 *
 * @author  Peter Cortez <peter@locale.to>
 */
trait HasHookArguments
{
    /**
     * The names of the arguments passed onto __invoke, in the exact order that it
     * was passed.
     *
     * @var string[]
     */
    protected $hookArgsNames = [];

    /**
     * The hook arguments passed onto __invoke
     *
     * @var array
     */
    protected $args = [];

    /**
     * @return array
     */
    protected function getArgs()
    {
        return $this->args;
    }

    /**
     * @param array $args
     *
     * @return void
     */
    protected function setArgs($args)
    {
        $this->args = $args;
    }

    /**
     * Gets called before __invoke, and after the arguments has been set. This allows
     * us to use the arguments passed to __invoke, and set them accordingly to our
     * object instance, to be used anytime.
     *
     * @return void
     */
    protected function argsToProperties()
    {
        foreach ($this->getArgs() as $index => $arg) {
            $this->{$this->hookArgsNames[$index]} = $arg;
        }
    }
}
