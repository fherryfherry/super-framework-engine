<?php

if(!function_exists("convert_snake_to_CamelCase")) {
    function convert_snake_to_CamelCase($snake_case_string, $capitalise_first_char = false) {
        $str = str_replace(' ','',ucwords(str_replace(['-',' ','_'], ' ', $snake_case_string)));
        if (!$capitalise_first_char) {
            $str = lcfirst($str);
        }
        return $str;
    }
}

if(!function_exists("convert_UpperCamel_to_snake")) {
    /**
     * @param $UpperCamel
     * @param string $separator
     * @return string
     */
    function convert_UpperCamel_to_snake($UpperCamel, $separator = "-") {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', $separator.'$0', $UpperCamel));
    }
}

if(!function_exists("string_random")) {
    /**
     * @param int $length
     * @return string
     */
    function random_string($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}

if(!function_exists('str_slug'))
{
    function str_slug($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}