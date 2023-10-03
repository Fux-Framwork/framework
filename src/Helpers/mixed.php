<?php


function print_r_pre($value)
{
    echo "<pre>";
    print_r($value);
    echo "</pre>";
}


function array_combine_keep_copy($keys, $values)
{
    $result = array();
    foreach ($keys as $i => $k) {
        $result[$k][] = $values[$i];
    }
    //array_walk($result, create_function('&$v', '$v = (count($v) == 1)? array_pop($v): $v;'));
    return $result;
}


function my_array_unique($array)
{
    $a = [];
    foreach ($array as $k => $v)
        $a[$v] = true;
    return array_keys($a);
}


function plural_string($n, $single, $more, $zero = null)
{
    if ($n == 0 && $zero) {
        return $zero;
    }
    if ($n > 1) {
        return $more;
    }
    return $single;
}

function issetDef(&$check, $default)
{
    return isset($check) ? $check : $default;
}

function request()
{
    return new Request();
}

/* Performs an echo operations of $data after converting it to JSON format */
function json($data)
{
    echo json_encode($data);
    return "";
}