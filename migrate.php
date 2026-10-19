<?php
// CLI only: php migrate.php [--seed]

if (PHP_SAPI !== 'cli') {
    exit('Run this script from the command line.');
}

require_once __DIR__ . '/app/bootstrap.php';

use App\Config\Database;
use App\Models\User;

Database::createDatabaseIfMissing();
$db = Database::connect();

$db->exec("
    CREATE TABLE IF NOT EXISTS migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL UNIQUE,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

$files = glob(__DIR__ . '/database/migrations/*.php');
sort($files);

foreach ($files as $file) {
    $name = basename($file);

    $stmt = $db->prepare('SELECT COUNT(*) FROM migrations WHERE migration = :m');
    $stmt->execute(['m' => $name]);

    if ((int) $stmt->fetchColumn() > 0) {
        echo "Already migrated: {$name}\n";
        continue;
    }

    try {
        // MySQL commits DDL implicitly, so there is no transaction wrapper here.
        $run = require $file;
        $run($db);
        $db->prepare('INSERT INTO migrations (migration) VALUES (:m)')->execute(['m' => $name]);
        echo "Migrated: {$name}\n";
    } catch (Throwable $e) {
        echo "Migration failed: {$name}\n" . $e->getMessage() . "\n";
        exit(1);
    }
}

if (in_array('--seed', $argv, true)) {
    $users = new User();
    $demo = [
        ['Mohammad Sobuj', 'admin@uptms.test', 'admin'],
        ['Dr. Jannatul Naeem', 'teacher@uptms.test', 'teacher'],
        ['Sobuj (Student)', 'student@uptms.test', 'student'],
    ];

    foreach ($demo as [$name, $email, $role]) {
        if ($users->findByEmail($email)) {
            echo "Seed exists: {$email}\n";
            continue;
        }
        $users->create($name, $email, 'password123', $role);
        echo "Seeded {$role}: {$email} / password123\n";
    }
}
