<?php

if (!function_exists('is_associative'))
{
    function is_associative($array)
    {
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }
}