<?php

die();

require_once "config/db.php";
require_once __DIR__ . '/vendor/autoload.php';

$faker = Faker\Factory::create('fr_FR');

$nb = 200;

$sql = "INSERT INTO users (username, email, password, phone, gender, search, city, description, roles) VALUES ";

$rows = [];
$values = [];

for($i = 0 ; $i < $nb ; $i++)
{
    $rows[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $values[] = $faker->userName();
    $values[] = $faker->email();
    $values[] = password_hash("1234", PASSWORD_BCRYPT);
    $values[] = $faker->phoneNumber();
    $values[] = random_int(0, 1) === 0 ? "man" : "woman";
    $values[] = random_int(0, 1) === 0 ? "man" : "woman";
    $values[] = $faker->city();
    $values[] = $faker->paragraph();
    $values[] = json_encode(['ROLE_USER']);
}

$sql .= implode(', ',$rows);

$stmt = $pdo->prepare($sql);
$stmt->execute($values);

echo "$nb utilisateurs insérés avec succès !";