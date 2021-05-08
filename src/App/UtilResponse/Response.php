<?php

namespace SuperFrameworkEngine\App\UtilResponse;

class Response
{
    /**
     * @param $array
     * @return false|string
     */
    public function json($array) {
        header("Content-Type: application/json");
        $array = is_callable($array)?call_user_func($array):$array;
        return json_encode($array);
    }

    /**
     * @param $content
     * @param $filename
     * @param string $contentType
     * @return mixed
     */
    public function download($content, $filename, $contentType = "application/octet-stream") {
        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"". $filename ."\"");
        return $content;
    }
}