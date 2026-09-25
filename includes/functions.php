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

function readEnv(string $key): string
{
    $file = fopen(__DIR__ . "/../.env", "r");

    if(!$file)
    {
        return "";
    }

    while(1)
    {
        $line = fgets($file);
        if(!$line)
        {
            break;
        }

        if ($line === "" || $line[0] === "#" || !str_contains($line, "="))
        {
            continue;
        }

        $line = trim($line);

        [$k, $v] = explode("=", $line, 2);

        $k = trim($k);
        $v = trim($v);

        $v = trim($v, '"\'');

        if ($k === $key)
        {
            return $v;
        }
    }

    fclose($file);

    return "";
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
    $sth = $pdo->prepare("SELECT * FROM inscriptions WHERE user_id=:user_id AND date=:date");
    $sth->execute(['user_id' => $_SESSION["user"]["id"], "date" => $date->format("Y-m-d")]);
    $res = $sth->fetch();

    if(!$res)
    {
        return false;
    }

    return true;
}

function validRoom(PDO $pdo): bool
{
    $now = new DateTime();
    $now->setTimezone(new DateTimeZone("Europe/Paris"));

    $dayOfWeek = $now->format("w");

    if($dayOfWeek !== '2' && $dayOfWeek !== '4' && $dayOfWeek !== '0')
    {
        return false;
    }

    $hour = intval($now->format('H'));
    
    if($hour !== 21)
    {
        return false;
    }

    $sth = $pdo->prepare("SELECT * FROM inscriptions WHERE user_id=:user_id AND :now = date");
    $sth->execute(['user_id' => $_SESSION["user"]["id"], "now" => $now->format("Y-m-d")]);
    $res = $sth->fetch();

    if(!$res)
    {
        return false;
    }

    return true;
}

function generateJWT(array $payload, string $secret, int $expiryInSeconds = 30): string 
{
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    
    $payload['iat'] = time();
    $payload['exp'] = time() + $expiryInSeconds;
    $payloadJson = json_encode($payload);

    $base64UrlHeader  = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode($payloadJson);

    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
    $base64UrlSignature = base64UrlEncode($signature);

    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

function base64UrlEncode(string $data): string 
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function encryptId(int|string $id): string
{
    $key = readEnv('SECRET_KEY');
    $iv  = readEnv('IV_KEY');

    $tag = '';
    
    $encrypted = openssl_encrypt(
        (string) $id,
        'aes-128-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag,
        '',
        16
    );

    if ($encrypted === false)
    {
        throw new \Exception("Échec du chiffrement de l'ID.");
    }

    $encryptedHex = bin2hex($encrypted);
    $tagHex       = bin2hex($tag);

    return "{$encryptedHex}:{$tagHex}";
}

function decryptId(string $cipheredId): int 
{
    [$encryptedHex, $tagHex] = explode(':', $cipheredId);
    
    $key = readEnv('SECRET_KEY');
    $iv  = readEnv('IV_KEY');
    
    $encrypted = hex2bin($encryptedHex);
    $tag       = hex2bin($tagHex);
    
    $decrypted = openssl_decrypt(
        $encrypted,
        'aes-128-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );

    if ($decrypted === false) {
        throw new \Exception("Déchiffrement échoué ou ID altéré");
    }

    return (int) $decrypted;
}