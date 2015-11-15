<?php

if (!function_exists('array_is_associative')) {

    function array_is_associative($array)
    {
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }
}
