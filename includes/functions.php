<?php

function createToken()
{
    $nbChars = random_int(30, 60);
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $token = "";

    for($i = 0 ; $i < $nbChars ; $i++)
    {
        $index = random_int(0, strlen($chars) - 1);
        $token .= substr($chars, $index, 1);
    }

    return $token;
}

function readEnv()
{
    $file = fopen("../.env", "r");

    if(!$file)
    {
        return [];
    }

    $res = [];

    while(1)
    {
        $line = fgets($file);
        if(!$line)
        {
            break;
        }

        if($line[0] === "#")
        {
            continue;
        }

        $key = substr($line, 0, strpos($line, "="));
        $value = substr($line, strpos($line, "=") + 1);

        if($value[0] === "\"")
        {
            $value = substr($value, 1);
        }

        if($value[strlen($value) - 1] === "\"")
        {
            $value = substr($value, 0, strlen($value) - 1);
        }

        if($key !== "" && $value !== "")
        {
            $res[$key] = $value;
        }
    }

    fclose($file);

    return $res;
}