<?php


if(!function_exists("request_url_is")) {
    /**
     * To detect if current url is contain specific asterisk
     * @param array|string $path_param
     * @return bool
     */
    function request_url_is($path_param) {
        $paths = is_array($path_param)?$path_param : [ $path_param ];
        $currentURL = get_current_url(null, false);
        $currentURL = parse_url($currentURL)['path'];
        $currentURL = ltrim($currentURL, '/');
        foreach($paths as $path) {
            $path = ltrim($path,'/');
            if(substr($path,-1,1)=="*") {
                $pattern = str_replace("*","", $path);
                $pattern = str_replace("/","\/", $pattern);
                $pattern = '/^'.$pattern.'/';
            } else {
                $pattern = str_replace("/","\/", $path);
                $pattern = '/^'.$pattern.'$/';
            }

            $check = preg_match($pattern, $currentURL);
            if($check) {
                return true;
            }
        }
        return false;
    }
}

if(!function_exists("request_method_is")) {
    /**
     * @param $method
     * @return bool
     */
    function request_method_is($method) {
        return strtolower($_SERVER["REQUEST_METHOD"]) === strtolower($method);
    }
}

if(!function_exists("request_method_is_post")) {
    /**
     * @return bool
     */
    function request_method_is_post() {
        return $_SERVER["REQUEST_METHOD"] === 'POST';
    }
}

if(!function_exists("request_method_is_get")) {
    /**
     * @return bool
     */
    function request_method_is_get() {
        return $_SERVER["REQUEST_METHOD"] === 'GET';
    }
}


if(!function_exists('request')) {
    /**
     * @param null|string $name
     * @param null|string $default
     * @param bool $sanitize
     * @return null|string|array
     */
    function request($name = null, $default = null, $sanitize = false) {
        $value = $_REQUEST;
        $value = array_merge($value, $_FILES);
        if($name) {
            $value = (isset($value[$name]) && $value[$name])?$value[$name]:$default;
            $value = ($sanitize) ? htmlspecialchars($value, ENT_COMPAT, "UTF-8") : $value;
        } else {
            if($sanitize) {
                foreach($value as $key=>$val) $value[$key] = htmlspecialchars($val);
            }
        }
        return $value;
    }
}


if(!function_exists("request_url")) {
    /**
     * @param $name
     * @param null $default
     * @param bool $sanitize
     * @return mixed|null
     * @throws Exception
     */
    function request_url($name, $default = null, $sanitize = false) {
        $value = $_REQUEST;
        $value = (isset($value[$name]) && $value[$name])?$value[$name]:$default;
        $value = ($sanitize) ? htmlspecialchars($value, ENT_COMPAT, "UTF-8") : $value;
        $value = filter_var($value, FILTER_SANITIZE_URL);
        if(filter_var($value, FILTER_VALIDATE_URL)) return $value;
        else throw new Exception("Invalid url value of `".$name."`");
    }
}

if(!function_exists("request_int")) {
    /**
     * @param $name
     * @param null $default
     * @param bool $sanitize
     * @return mixed|null
     * @throws Exception
     */
    function request_int($name, $default = null, $sanitize = false) {
        $value = $_REQUEST;
        $value = (isset($value[$name]) && $value[$name])?$value[$name]:$default;
        $value = ($sanitize) ? htmlspecialchars($value, ENT_COMPAT, "UTF-8") : $value;
        $value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
        if(filter_var($value, FILTER_VALIDATE_INT)) return $value;
        else throw new Exception("Invalid integer value of `".$name."`");
    }
}

if(!function_exists("request_string")) {
    /**
     * @param $name
     * @param null $default
     * @param bool $sanitize
     * @return mixed|null
     */
    function request_string($name, $default = null, $sanitize = false) {
        $value = $_REQUEST;
        $value = (isset($value[$name]) && $value[$name] && $value[$name] != "null")?$value[$name]:$default;
        $value = ($sanitize) ? htmlspecialchars($value, ENT_COMPAT, "UTF-8") : $value;
        return filter_var($value, FILTER_SANITIZE_STRING);
    }
}

if(!function_exists("request_email")) {
    /**
     * @param $name
     * @param null $default
     * @param bool $sanitize
     * @return mixed|null
     * @throws Exception
     */
    function request_email($name, $default = null, $sanitize = false) {
        $value = $_REQUEST;
        $value = (isset($value[$name]) && $value[$name])?$value[$name]:$default;
        $value = ($sanitize) ? htmlspecialchars($value, ENT_COMPAT, "UTF-8") : $value;
        $value = filter_var($value, FILTER_SANITIZE_EMAIL);
        if(filter_var($value, FILTER_VALIDATE_EMAIL)) return $value;
        else throw new Exception("Invalid email value of `".$name."`");
    }
}

if(!function_exists("request_float")) {
    /**
     * @param $name
     * @param null $default
     * @param bool $sanitize
     * @return mixed|null
     * @throws Exception
     */
    function request_float($name, $default = null, $sanitize = false) {
        $value = $_REQUEST;
        $value = (isset($value[$name]) && $value[$name])?$value[$name]:$default;
        $value = ($sanitize) ? htmlspecialchars($value, ENT_COMPAT, "UTF-8") : $value;
        $value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT);
        if(filter_var($value, FILTER_VALIDATE_FLOAT)) return $value;
        else throw new Exception("Invalid float value of `".$name."`");
    }
}

if(!function_exists('request_json'))
{
    /**
     * @param null $key
     * @param null $default
     * @param bool $sanitize
     * @return mixed
     */
    function request_json($key = null, $default = null, $sanitize = false)
    {
        $json_data = file_get_contents('php://input');
        $input = json_decode($json_data, TRUE);
        if(isset($key)) {
            $value = isset($input[$key]) ? $input[$key] : $default;
            $value = ($sanitize) ? htmlspecialchars($value, ENT_COMPAT, "UTF-8") : $value;
            return $value;
        } else {
            return $input ? $input : null;
        }
    }
}

if(!function_exists('request_json_string'))
{
    /**
     * @param $key
     * @param null $default
     * @param bool $sanitize
     * @return mixed|null
     */
    function request_json_string($key, $default = null, $sanitize = false)
    {
        // Get Data Raw JSON
        $input = request_json($key);

        if($input) {
            $value = filter_var($input,FILTER_SANITIZE_STRING);
            $value = ($sanitize) ? htmlspecialchars($value, ENT_COMPAT, "UTF-8") : $value;
            return $value ?: $default;
        }
        return null;
    }
}


if(!function_exists('request_json_int'))
{
    /**
     * @param $key
     * @param null $default
     * @param bool $sanitize
     * @return null
     * @throws Exception
     */
    function request_json_int($key, $default = null, $sanitize = false)
    {
        $input = request_json($key);

        if($input) {
            $value = filter_var($input,FILTER_SANITIZE_NUMBER_INT);
            $value = ($sanitize) ? htmlspecialchars($value, ENT_COMPAT, "UTF-8") : $value;
            $final = $value ? : $default;
            if(filter_var($final, FILTER_VALIDATE_INT)) return $final;
            else throw new Exception("Invalid integer value of `".$key."`");
        }
        return null;
    }
}


if(!function_exists('request_json_float'))
{
    /**
     * @param $key
     * @param null $default
     * @param bool $sanitize
     * @return null
     * @throws Exception
     */
    function request_json_float($key, $default = null, $sanitize = false)
    {
        $input = request_json($key);

        if($input) {
            $value = filter_var($input,FILTER_SANITIZE_NUMBER_FLOAT);
            $value = ($sanitize) ? htmlspecialchars($value, ENT_COMPAT, "UTF-8") : $value;
            $final = $value ? : $default;
            if(filter_var($final, FILTER_VALIDATE_FLOAT)) return $final;
            else throw new Exception("Invalid float value of `".$key."`");
        }
        return null;
    }
}

if(!function_exists('request_json_email'))
{
    /**
     * @param $key
     * @param null $default
     * @param bool $sanitize
     * @return null
     * @throws Exception
     */
    function request_json_email($key, $default = null, $sanitize = false)
    {
        $input = request_json($key);

        if($input) {
            $value = filter_var($input,FILTER_SANITIZE_EMAIL);
            $value = ($sanitize) ? htmlspecialchars($value, ENT_COMPAT, "UTF-8") : $value;
            $final = $value ? : $default;
            if(filter_var($final, FILTER_VALIDATE_EMAIL)) return $final;
            else throw new Exception("Invalid email value of `".$key."`");
        }
        return null;
    }
}