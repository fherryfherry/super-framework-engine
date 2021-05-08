<?php


if(!function_exists('response')) {
    /**
     * @return \SuperFrameworkEngine\App\UtilResponse\Response
     */
    function response() {
        return (new \SuperFrameworkEngine\App\UtilResponse\Response());
    }
}