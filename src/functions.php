<?php

if (! function_exists('debug')) {
    /**
     * Tracy\Debugger::barDump() shortcut.
     *
     * @tracySkipLocation
     *
     * @param mixed $var
     */
    function debug($var)
    {
        call_user_func_array('Tracy\Debugger::barDump', func_get_args());

        return $var;
    }
}
