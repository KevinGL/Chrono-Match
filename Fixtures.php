<?php

require_once __DIR__ . '/vendor/autoload.php';

$options = getopt('', [
    'nb::',
    'host::',
    'port::',
    'username::',
    'password::',
    'dbname::'
]);

$nb       = $options['nb']       ?? 10;
$host     = $options['host']     ?? '127.0.0.1';
$port     = $options['port']     ?? '3306';
$username = $options['username'] ?? 'root';
$password = $options['password'] ?? '';
$dbname   = $options['dbname']   ?? 'chronomatch';

if($nb < 1)
{
    echo "Erreur, 1 user min accepté";
    die();
}

try {
    echo "Connexion au serveur MySQL...\n";
    
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $faker = Faker\Factory::create('fr_FR');

    $sql = "INSERT INTO users (username, email, password, phone, gender, search, city, description, roles) VALUES ";

    $rows = [];
    $values = [];

    echo "Création users...\n";

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
}
catch (Exception $e) {
    echo " Erreur lors de l'initialisation : " . $e->getMessage() . "\n";
    exit(1);
}