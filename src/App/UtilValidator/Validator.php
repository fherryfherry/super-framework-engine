<?php


namespace SuperFrameworkEngine\App\UtilValidator;


use SuperFrameworkEngine\Exceptions\ValidatorException;

class Validator
{
    /**
     * @param $data
     * @param $rules
     * @throws ValidatorException
     */
    public static function make($data, $rules): void
    {
        foreach($data as $key => $value) {
            foreach($rules as $field => $rule) {
                if($key == $field) {
                    $ruleExp = explode("|",$rule);
                    foreach($ruleExp as $r) {
                        if($r == "required" && empty($value)) {
                            throw new ValidatorException("Column `{$key}` is required");
                        }

                        if($r == "email" && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            throw new ValidatorException("Column `{$key}` should be email");
                        }

                        if($r == "url" && !filter_var($value, FILTER_VALIDATE_URL)) {
                            throw new ValidatorException("Column `{$key}` should be url");
                        }

                        if($r == "int" && !filter_var($value, FILTER_VALIDATE_INT)) {
                            throw new ValidatorException("Column `{$key}` should be integer");
                        }

                        if(substr($r, 0, 6) == "unique") {
                            $tableUnique = substr($r, 7);
                            if($tableUnique) {
                                if(DB($tableUnique)->where("{$key} = '{$value}'")->count()) {
                                    throw new ValidatorException("Data {$key} '{$value}' has already exists!");
                                }
                            } else {
                                throw new \InvalidArgumentException("Unique rule does not has a table value!");
                            }
                        }

                        if (substr($r,0, 6) == "exists") {
                            $exist = explode(",", substr($r, 7));
                            if($exist) {
                                if(count($exist) > 2) {
                                    $tableExist = $exist[0];
                                    $columnExist = $exist[1];
                                } else {
                                    $tableExist = $exist[0];
                                    $columnExist = "id";
                                }
                                if(!db($tableExist)->where("{$columnExist} = '{$value}'")->count()) {
                                    throw new ValidatorException("Data for `{$key}` is not exists!");
                                }
                            } else {
                                throw new \InvalidArgumentException("Exist rule is invalid!");
                            }
                        }
                    }
                }
            }
        }
    }

}