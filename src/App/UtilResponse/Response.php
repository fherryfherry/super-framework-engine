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

    function abortError($errorName, $errorDescription = null, $headerCode = "500 Internal Server Error") {
        header("HTTP/1.0 ".$headerCode);
        $data['error_name'] = $errorName;
        $data['error_description'] = $errorDescription;
        return view_custom("app/Error","general", $data);
    }

    public function abortError404() {
        header("HTTP/1.0 404 Not Found");
        $data['error_name'] = "404 Page Not Found";
        return view_custom("app/Error","404", $data);
    }

}