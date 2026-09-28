<?php

$options = getopt('', [
    'host::',
    'port::',
    'username::',
    'password::',
    'dbname::'
]);

$host     = $options['host']     ?? '127.0.0.1';
$port     = $options['port']     ?? '3306';
$username = $options['username'] ?? 'root';
$password = $options['password'] ?? '';
$dbname   = $options['dbname']   ?? 'chronomatch';

try {
    echo " Connexion au serveur MySQL...\n";
    
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo " Création de la base de données '$dbname' (si absente)...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    
    $pdo->exec("USE `$dbname`;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `datings` ( `id` int NOT NULL AUTO_INCREMENT, `user_id1` int NOT NULL, `user_id2` int NOT NULL, `date` datetime NOT NULL, PRIMARY KEY (`id`)) ");
    $pdo->exec("CREATE TABLE IF NOT EXISTS `inscriptions` ( `id` int NOT NULL AUTO_INCREMENT, `user_id1` int NOT NULL, `date` date NOT NULL, `created_at` datetime NOT NULL, PRIMARY KEY (`id`)) ");
    $pdo->exec("CREATE TABLE IF NOT EXISTS `likes` ( `id` int NOT NULL AUTO_INCREMENT, `sender` int NOT NULL, `receiver` int NOT NULL, PRIMARY KEY (`id`)) ");
    $pdo->exec("CREATE TABLE IF NOT EXISTS `matchs` ( `id` int NOT NULL AUTO_INCREMENT, `user_id1` int NOT NULL, `user_id2` int NOT NULL, PRIMARY KEY (`id`)) ");
    $pdo->exec("CREATE TABLE IF NOT EXISTS `messages` ( `id` int NOT NULL AUTO_INCREMENT, `sender` int NOT NULL, `receiver` int NOT NULL, `content` text COLLATE utf8mb4_unicode_ci NOT NULL, `match_id` int NOT NULL, `createdAt` datetime NOT NULL, PRIMARY KEY (`id`)) ");
    $pdo->exec("CREATE TABLE IF NOT EXISTS `rooms` ( `id` int NOT NULL AUTO_INCREMENT, `user_id1` int NOT NULL, `user_id2` int NOT NULL, `date` datetime NOT NULL, PRIMARY KEY (`id`)) ");
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` ( `id` int NOT NULL AUTO_INCREMENT, `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `gender` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `search` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `description` text COLLATE utf8mb4_unicode_ci NOT NULL, `roles` json NOT NULL, PRIMARY KEY (`id`)) ");

    echo " Base de données '$dbname' initialisée avec succès !\n";

} catch (Exception $e) {
    echo " Erreur lors de l'initialisation : " . $e->getMessage() . "\n";
    exit(1);
}