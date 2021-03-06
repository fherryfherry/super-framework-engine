<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 9/3/2020
 * Time: 4:06 PM
 */

namespace SuperFrameworkEngine\App\UtilSecurity;


class Hash
{
    /**
     * @param $string_text
     * @return bool|string
     */
    static function make($string_text) {
        return password_hash(config('hash_salt_key').$string_text, PASSWORD_BCRYPT);
    }

    /**
     * @param $input_password
     * @param $hash_password
     * @return bool
     */
    static function check($input_password, $hash_password): bool
    {
        return (boolean) password_verify(config('hash_salt_key').$input_password, $hash_password);
    }
}