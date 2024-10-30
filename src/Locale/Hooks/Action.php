<?php

namespace Locale\Locale\Hooks;

use Exception;
use function esc_html__;
use function func_get_args;
use Locale\Notice\TransientNoticeService;
use Locale\Locale\Hooks\Support\HasHookArguments;

/**
 * Represents a WordPress action hook.
 *
 * @package Locale\Locale\Hooks
 *
 * @author  Peter Cortez <peter@locale.to>
 */
abstract class Action
{
    use HasHookArguments;

    /**
     * Determines whether the hook should run or not.
     *
     * @return bool
     */
    abstract protected function shouldRun();

    /**
     * Contains the actual logic of the hook, and is run when Hook::shouldRun()
     * returns true.
     *
     * @return void
     */
    abstract protected function handle();

    /**
     * @return void
     */
    public function __invoke()
    {
        $this->setArgs(func_get_args());
        $this->argsToProperties();

        try {
            if (! $this->shouldRun()) {
                return;
            }

            $this->handle();

            $this->postHandlerActions();
        } catch (Exception $e) {
            TransientNoticeService::add_notice(
                esc_html__($e->getMessage(), 'locale'),
                'error'
            );
        }
    }

    /**
     * Run right after the \Locale\Locale\Hooks\Action::handle() is run, allows for
     * additional actions to be done.
     *
     * @todo Instead of running a post handler method, emit events instead, that can
     *       be hooked on by other listeners.
     *
     * @return void
     */
    protected function postHandlerActions()
    {
        // ..
    }
}
