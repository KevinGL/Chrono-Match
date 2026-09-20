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
    $file = fopen(__DIR__ . "/../.env", "r");

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

function getNextSessions(): array
{
    $nb = 20;

    $date = new DateTimeImmutable("now");

    $date = $date->setTimezone(new DateTimeZone("Europe/Paris"));
    $date = $date->setTime(21, 0, 0);

    $res = [];

    while(1)
    {
        $day = $date->format("w");
        
        if($day === '2' || $day === '4' || $day === '0')
        {
            array_push($res, $date);
        }

        if(count($res) === $nb)
        {
            break;
        }

        $timestamp = $date->getTimestamp();
        $timestamp += 24 * 3600;
        $date = $date->setTimestamp($timestamp);
    }

    return $res;
}

function registered(PDO $pdo, DateTimeImmutable $date): bool
{
    $sth = $pdo->prepare("SELECT * FROM inscriptions WHERE user_id=:id AND date=:date");
    $sth->execute(['id' => $_SESSION["user"]["id"], "date" => $date->format("Y-m-d H:i:s")]);
    $res = $sth->fetch();

    if(!$res)
    {
        return false;
    }

    return true;
}
