<?php

use SuperFrameworkEngine\Foundation\Container;
use SuperFrameworkEngine\Helpers\Collection;

if (!function_exists('simple_collect')) {
    /**
     * @param array $dataArray
     * @return Collection
     */
    function simple_collect(array $dataArray): Collection
    {
        return new Collection($dataArray);
    }
}

if (!function_exists('array_unique_multi')) {
    /**
     * @param array $array
     * @param string $key
     * @return array
     */
    function array_unique_multi(array $array, string $key): array
    {
        $temp = array_unique(array_column($array, $key));
        return array_intersect_key($array, $temp);
    }
}

if (!function_exists("put_singleton")) {
    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    function put_singleton(string $key, mixed $value): void
    {
        if (extension_loaded('apcu')) {
            apcu_add($key, $value, 5);
        } else {
            Container::getInstance()->singleton($key, fn() => $value);
        }
    }
}

if (!function_exists("get_singleton")) {
    /**
     * @param string $key
     * @return mixed
     */
    function get_singleton(string $key): mixed
    {
        if (extension_loaded('apcu')) {
            return apcu_fetch($key);
        }

        try {
            return Container::getInstance()->make($key);
        } catch (Exception) {
            return null;
        }
    }
}

if (!function_exists("url")) {
    /**
     * @param string|null $path
     * @return string
     */
    function url(?string $path = null): string
    {
        return base_url($path);
    }
}

if (!function_exists("asset")) {
    /**
     * @param string|null $path
     * @return string
     */
    function asset(?string $path = null): string
    {
        return base_url($path);
    }
}

if (!function_exists("redirect_back")) {
    /**
     * @param array $with_session_data
     * @return never
     */
    function redirect_back(array $with_session_data = []): never
    {
        if ($with_session_data) {
            session_flash($with_session_data);
        }

        header("location: " . $_SERVER['HTTP_REFERER'], false, 301);
        exit;
    }
}

if (!function_exists("redirect")) {
    /**
     * @param string $path
     * @param array $with_session_data
     * @return never
     */
    function redirect(string $path, array $with_session_data = []): never
    {
        if ($with_session_data) {
            session_flash($with_session_data);
        }

        $url = str_contains($path, 'http') ? $path : base_url($path);
        header("location: " . $url, false, 301);
        exit;
    }
}

if (!function_exists("logging")) {
    /**
     * @param mixed $content
     * @param string $type
     * @return void
     */
    function logging(mixed $content, string $type = "error"): void
    {
        file_put_contents((string) base_path("/logs/" . date("Y-m-d") . ".log"), "[" . date("Y-m-d H:i:s") . "][" . $type . "] - " . $content . "\n\n", FILE_APPEND);
    }
}

if (!function_exists("var_min_export")) {
    /**
     * @param mixed $expression
     * @param bool $return
     * @return mixed
     */
    function var_min_export(mixed $expression, bool $return = false): mixed
    {
        $export = var_export($expression, true);
        $export = (string) preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $export);
        $array = preg_split("/\r\n|\n|\r/", $export);
        $array = (array) preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [null, ']$1', ' => ['], (array) $array);
        $export = implode(PHP_EOL, array_filter(["["] + $array));
        $export = (string) preg_replace("/[0-9]+ \=\>/i", '', $export);
        if ($return) {
            return $export;
        }
        echo $export;
        return null;
    }
}

if (!function_exists("config")) {
    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    function config(string $name, mixed $default = null): mixed
    {
        $name = str_contains($name, ".") ? $name : "default." . $name;
        $split_name = explode(".", $name);
        $singleton_key = "config_" . $split_name[0];

        if ($config_data = get_singleton($singleton_key)) {
            return $config_data[$split_name[1]] ?? $default;
        }

        try {
            if ($split_name[0] === "default") {
                $config_data = include base_path("configs/App.php");
            } else {
                $config_data = include base_path("app/Modules/" . $split_name[0] . "/Configs/App.php");
            }
            put_singleton($singleton_key, $config_data);
            return $config_data[$split_name[1]] ?? $default;
        } catch (Exception) {
            return $default;
        }
    }
}

if (!function_exists("public_path")) {
    /**
     * @param string|null $path
     * @return string
     */
    function public_path(?string $path = null): string
    {
        return BASE_PATH . "/public/" . $path;
    }
}

if (!function_exists("base_path")) {
    /**
     * @param string|null $path
     * @return string
     */
    function base_path(?string $path = null): string
    {
        return BASE_PATH . DIRECTORY_SEPARATOR . $path;
    }
}

if (!function_exists('base_path_uri')) {
    /**
     * @param string|null $path
     * @return string
     */
    function base_path_uri(?string $path = null): string
    {
        $tmpURL = BASE_DIR;
        $tmpURL = str_replace(chr(92), '/', $tmpURL);
        $tmpURL = str_replace($_SERVER['DOCUMENT_ROOT'], '', $tmpURL);
        $tmpURL = ltrim($tmpURL, '/');
        $tmpURL = rtrim($tmpURL, '/');

        return $path ? $tmpURL . "/" . $path : $tmpURL;
    }
}

if (!function_exists("base_url")) {
    /**
     * @param string|null $path
     * @param string|null $default
     * @return string
     */
    function base_url(?string $path = null, ?string $default = null): string
    {
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';

        $tmpURL = BASE_DIR;
        $tmpURL = str_replace(chr(92), '/', $tmpURL);
        $tmpURL = str_replace($_SERVER['DOCUMENT_ROOT'], '', $tmpURL);
        $tmpURL = ltrim($tmpURL, '/');
        $tmpURL = rtrim($tmpURL, '/');

        if ($tmpURL !== $_SERVER['HTTP_HOST']) {
            $base_url .= ($tmpURL) ? $_SERVER['HTTP_HOST'] . '/' . $tmpURL . '/' : $_SERVER['HTTP_HOST'] . '/';
        } else {
            $base_url .= $tmpURL . '/';
        }

        return $path ? $base_url . $path : $base_url . ($default ?? '');
    }
}

if (!function_exists("dd")) {
    /**
     * @param mixed ...$args
     * @return never
     */
    function dd(mixed ...$args): never
    {
        foreach ($args as $arg) {
            echo "<pre><code>";
            print_r($arg);
            echo "</code></pre>";
        }
        exit;
    }
}

if (!function_exists("get_current_url")) {
    /**
     * @param array $param
     * @param bool $with_query
     * @return string
     */
    function get_current_url(array $param = [], bool $with_query = true): string
    {
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $url = (string) strtok($url, "?");

        if ($with_query) {
            $param_array = array_merge($_GET, $param);
            $param_string = http_build_query($param_array);
            $param_string = ($param_string) ? "?" . $param_string : "";
            $url .= $param_string;
        }

        return $url;
    }
}