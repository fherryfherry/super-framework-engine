<?php

declare(strict_types=1);

use SuperFrameworkEngine\Exceptions\ValidatorException;

if (!function_exists("request_url_is")) {
    /**
     * To detect if current url is contain specific asterisk
     * @param array|string $path_param
     * @return bool
     */
    function request_url_is(array|string $path_param): bool
    {
        $paths = is_array($path_param) ? $path_param : [$path_param];
        $currentURL = get_current_url([], false);
        $currentURL = parse_url($currentURL)['path'] ?? '';
        $currentURL = ltrim($currentURL, '/');

        foreach ($paths as $path) {
            $path = ltrim($path, '/');
            if (str_ends_with($path, "*")) {
                $pattern = str_replace("*", "", $path);
                $pattern = str_replace("/", "\/", $pattern);
                $pattern = '/^' . $pattern . '/';
            } else {
                $pattern = str_replace("/", "\/", $path);
                $pattern = '/^' . $pattern . '$/';
            }

            if (preg_match($pattern, $currentURL)) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists("request_method_is")) {
    /**
     * @param string $method
     * @return bool
     */
    function request_method_is(string $method): bool
    {
        return strtolower($_SERVER["REQUEST_METHOD"]) === strtolower($method);
    }
}

if (!function_exists("request_method_is_post")) {
    /**
     * @return bool
     */
    function request_method_is_post(): bool
    {
        return $_SERVER["REQUEST_METHOD"] === 'POST';
    }
}

if (!function_exists("request_method_is_get")) {
    /**
     * @return bool
     */
    function request_method_is_get(): bool
    {
        return $_SERVER["REQUEST_METHOD"] === 'GET';
    }
}

if (!function_exists('request')) {
    /**
     * @param string|null $name
     * @param mixed $default
     * @param bool $sanitize
     * @return mixed
     */
    function request(?string $name = null, mixed $default = null, bool $sanitize = false): mixed
    {
        $value = array_merge($_REQUEST, $_FILES);
        if ($name) {
            $val = (isset($value[$name]) && $value[$name]) ? $value[$name] : $default;
            return ($sanitize && is_string($val)) ? htmlspecialchars($val, ENT_COMPAT, "UTF-8") : $val;
        }

        if ($sanitize) {
            foreach ($value as $key => $val) {
                if (is_string($val)) {
                    $value[$key] = htmlspecialchars($val, ENT_COMPAT, "UTF-8");
                }
            }
        }
        return $value;
    }
}

if (!function_exists("request_url")) {
    /**
     * @param string $name
     * @param string|null $default
     * @param bool $sanitize
     * @return string|null
     * @throws Exception
     */
    function request_url(string $name, ?string $default = null, bool $sanitize = false): ?string
    {
        $value = $_REQUEST[$name] ?? $default;
        if ($value === null) {
            return null;
        }

        $value = ($sanitize) ? htmlspecialchars((string) $value, ENT_COMPAT, "UTF-8") : (string) $value;
        $value = filter_var($value, FILTER_SANITIZE_URL);

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return (string) $value;
        }

        throw new Exception("Invalid url value of `" . $name . "`");
    }
}

if (!function_exists("request_int")) {
    /**
     * @param string $name
     * @param int|null $default
     * @param bool $sanitize
     * @return int|null
     * @throws Exception
     */
    function request_int(string $name, ?int $default = null, bool $sanitize = false): ?int
    {
        $value = $_REQUEST[$name] ?? $default;
        if ($value === null) {
            return null;
        }

        $value = ($sanitize) ? htmlspecialchars((string) $value, ENT_COMPAT, "UTF-8") : (string) $value;
        $value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);

        if (filter_var($value, FILTER_VALIDATE_INT) !== false) {
            return (int) $value;
        }

        throw new Exception("Invalid integer value of `" . $name . "`");
    }
}

if (!function_exists("request_string")) {
    /**
     * @param string $name
     * @param string|null $default
     * @param bool $sanitize
     * @return string|null
     */
    function request_string(string $name, ?string $default = null, bool $sanitize = false): ?string
    {
        $value = $_REQUEST[$name] ?? $default;
        if ($value === null || $value === "null") {
            return $default;
        }

        return ($sanitize) ? htmlspecialchars((string) $value, ENT_COMPAT, "UTF-8") : (string) $value;
    }
}

if (!function_exists("request_email")) {
    /**
     * @param string $name
     * @param string|null $default
     * @param bool $sanitize
     * @return string|null
     * @throws Exception
     */
    function request_email(string $name, ?string $default = null, bool $sanitize = false): ?string
    {
        $value = $_REQUEST[$name] ?? $default;
        if ($value === null) {
            return null;
        }

        $value = ($sanitize) ? htmlspecialchars((string) $value, ENT_COMPAT, "UTF-8") : (string) $value;
        $value = filter_var($value, FILTER_SANITIZE_EMAIL);

        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return (string) $value;
        }

        throw new Exception("Invalid email value of `" . $name . "`");
    }
}

if (!function_exists("request_float")) {
    /**
     * @param string $name
     * @param float|null $default
     * @param bool $sanitize
     * @return float|null
     * @throws Exception
     */
    function request_float(string $name, ?float $default = null, bool $sanitize = false): ?float
    {
        $value = $_REQUEST[$name] ?? $default;
        if ($value === null) {
            return null;
        }

        $value = ($sanitize) ? htmlspecialchars((string) $value, ENT_COMPAT, "UTF-8") : (string) $value;
        $value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        if (filter_var($value, FILTER_VALIDATE_FLOAT) !== false) {
            return (float) $value;
        }

        throw new Exception("Invalid float value of `" . $name . "`");
    }
}

if (!function_exists('request_json')) {
    /**
     * @param string|null $key
     * @param mixed $default
     * @param bool $sanitize
     * @return mixed
     */
    function request_json(?string $key = null, mixed $default = null, bool $sanitize = false): mixed
    {
        $jsonData = file_get_contents('php://input');
        $input = json_decode((string) $jsonData, true);

        if ($key !== null) {
            $value = $input[$key] ?? $default;
            return ($sanitize && is_string($value)) ? htmlspecialchars($value, ENT_COMPAT, "UTF-8") : $value;
        }

        return $input ?: null;
    }
}

if (!function_exists('request_json_string')) {
    /**
     * @param string $key
     * @param string|null $default
     * @param bool $sanitize
     * @return string|null
     */
    function request_json_string(string $key, ?string $default = null, bool $sanitize = false): ?string
    {
        $input = request_json($key);
        if ($input === null) {
            return $default;
        }

        return ($sanitize) ? htmlspecialchars((string) $input, ENT_COMPAT, "UTF-8") : (string) $input;
    }
}

if (!function_exists('request_json_int')) {
    /**
     * @param string $key
     * @param int|null $default
     * @param bool $sanitize
     * @return int|null
     * @throws Exception
     */
    function request_json_int(string $key, ?int $default = null, bool $sanitize = false): ?int
    {
        $input = request_json($key);
        if ($input === null) {
            return $default;
        }

        $value = ($sanitize) ? htmlspecialchars((string) $input, ENT_COMPAT, "UTF-8") : (string) $input;
        $value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);

        if (filter_var($value, FILTER_VALIDATE_INT) !== false) {
            return (int) $value;
        }

        throw new Exception("Invalid integer value of `" . $key . "`");
    }
}

if (!function_exists('request_json_float')) {
    /**
     * @param string $key
     * @param float|null $default
     * @param bool $sanitize
     * @return float|null
     * @throws Exception
     */
    function request_json_float(string $key, ?float $default = null, bool $sanitize = false): ?float
    {
        $input = request_json($key);
        if ($input === null) {
            return $default;
        }

        $value = ($sanitize) ? htmlspecialchars((string) $input, ENT_COMPAT, "UTF-8") : (string) $input;
        $value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        if (filter_var($value, FILTER_VALIDATE_FLOAT) !== false) {
            return (float) $value;
        }

        throw new Exception("Invalid float value of `" . $key . "`");
    }
}

if (!function_exists('request_json_email')) {
    /**
     * @param string $key
     * @param string|null $default
     * @param bool $sanitize
     * @return string|null
     * @throws Exception
     */
    function request_json_email(string $key, ?string $default = null, bool $sanitize = false): ?string
    {
        $input = request_json($key);
        if ($input === null) {
            return $default;
        }

        $value = ($sanitize) ? htmlspecialchars((string) $input, ENT_COMPAT, "UTF-8") : (string) $input;
        $value = filter_var($value, FILTER_SANITIZE_EMAIL);

        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return (string) $value;
        }

        throw new Exception("Invalid email value of `" . $key . "`");
    }
}
