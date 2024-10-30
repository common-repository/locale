<?php

namespace Locale\Locale\Hooks;

use Exception;
use function esc_html__;
use function func_get_args;
use Locale\Notice\TransientNoticeService;
use Locale\Locale\Hooks\Support\HasHookArguments;

/**
 * Represents a posts list column.
 *
 * @package Locale\Locale\Hooks
 *
 * @author  Peter Cortez <peter@locale.to>
 */
abstract class ListColumn
{
    use HasHookArguments;

    /**
     * Returns the unique column name for this column, and is used for distinguishing
     * this column from the rest.
     *
     * @return mixed
     */
    abstract protected function getName();

    /**
     * The name of the column that will be displayed to the user.
     *
     * @return mixed
     */
    abstract protected function getDisplayName();

    /**
     * The value of the cell that will be displayed to the user.
     *
     * @return mixed
     */
    abstract protected function getCellValue();

    /**
     * @var false|mixed
     */
    protected $isCell;

    /**
     * @param $isCell
     */
    protected function __construct($isCell = false)
    {
        $this->isCell = $isCell;
    }

    /**
     * Returns an instance of this class that echoes a value that is used in the
     * posts' list cell.
     *
     * @return static
     */
    public static function asCell()
    {
        return new static(true);
    }

    /**
     * Returns an instance of this class that returns a value that can be used in the
     * posts' list column.
     *
     * @return static
     */
    public static function asColumn()
    {
        return new static(false);
    }

    public function __invoke()
    {
        $this->setArgs(func_get_args());
        $this->argsToProperties();

        try {
            if ($this->isCell) {
                echo $this->getCellValue();
            } else {
                // This will be displayed as a column, so we're just going to append
                // the field name to the rest of the columns.
                $cells = func_get_args()[0];

                return $cells + [$this->getName() => $this->getDisplayName()];
            }
        } catch (Exception $e) {
            TransientNoticeService::add_notice(
                esc_html__($e->getMessage(), 'locale'),
                'error'
            );
        }
    }
}
