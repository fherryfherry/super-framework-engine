<?php

if(!function_exists('cache_tag_forget')) {
    /**
     * Forget the existing cache file
     * @param string $tag
     */
    function cache_tag_forget( $tag = "general") {
        $tag = md5($tag);
        $cacheFiles = glob(base_path("bootstrap/cache/".$tag.".*"));
        foreach($cacheFiles as $cacheFile) {
            unlink($cacheFile);
        }
    }
}

if(!function_exists('cache_forget')) {
    /**
     * Forget the existing cache file
     * @param $key
     * @param string $tag
     */
    function cache_forget($key, $tag = "general") {
        $tag = md5($tag);
        $key = md5($key);
        $file_path = base_path("bootstrap/cache/".$tag.".".$key);
        if(file_exists($file_path)) unlink($file_path);
    }
}

if(!function_exists("cache")) {
    /**
     * Create or retrieve a cache
     * @param string|array $key
     * @param null $value
     * @param null $tag
     * @param int $minutes
     * @return null|string|mixed
     */
    function cache($key, $value = null, $tag = "general", $minutes = 60) {
        if(is_array($key)) {
            foreach($key as $k=>$v) {
                file_put_contents(base_path("bootstrap/cache/".md5($tag).".".md5($k)), serialize([
                    "expired"=>strtotime("+".$minutes." minutes"),
                    "content"=>$value
                ]));
            }
        } else {
            if($value) {
                file_put_contents(base_path("bootstrap/cache/".md5($tag).".".md5($key)), serialize([
                    "expired"=>strtotime("+".$minutes." minutes"),
                    "content"=>$value
                ]));
            } else {
                $tag = md5($tag);
                $key = md5($key);
                if(file_exists(base_path("bootstrap/cache/".$tag.".".$key))) {
                    $cache = file_get_contents(base_path("bootstrap/cache/".$tag.".".$key));
                    $cache = unserialize($cache);
                    if($cache['expired'] > time()) {
                        return $cache['content'];
                    } else {
                        unlink(base_path("bootstrap/cache/".$tag.".".$key));
                    }
                }
            }
        }

        return null;
    }
}