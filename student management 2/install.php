<?php
// Run once: http://localhost/sms/install.php  (creates the database, tables and sample data)
require 'config.php';
$log = [];
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS,
                   [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
    $pdo->exec('USE `' . DB_NAME . '`');

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(30) NOT NULL
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        class_name VARCHAR(50) NOT NULL
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        att_date DATE NOT NULL,
        status ENUM('P','L','A') NOT NULL,
        UNIQUE KEY uq_att (student_id, att_date),
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS marks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        subject VARCHAR(50) NOT NULL,
        score TINYINT UNSIGNED NOT NULL,
        UNIQUE KEY uq_mark (student_id, subject),
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $log[] = 'Database and tables ready.';

    if (!$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn()) {
        $u = $pdo->prepare('INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)');
        $u->execute(['admin',   password_hash('Admin@123', PASSWORD_DEFAULT), 'Administrator']);
        $u->execute(['teacher', password_hash('Teach@123', PASSWORD_DEFAULT), 'Teacher']);
        $log[] = 'Users created: admin / Admin@123 and teacher / Teach@123.';
    }

    if (!$pdo->query('SELECT COUNT(*) FROM students')->fetchColumn()) {
        $names = [['Amina Wanjiru', 'Form 1'], ['Brian Otieno', 'Form 1'], ['Cynthia Njeri', 'Form 1'],
                  ['David Kamau', 'Form 2'], ['Esther Achieng', 'Form 2'], ['Felix Mutua', 'Form 2']];
        $si = $pdo->prepare('INSERT INTO students (name, class_name) VALUES (?, ?)');
        $mi = $pdo->prepare('INSERT INTO marks (student_id, subject, score) VALUES (?, ?, ?)');
        $ai = $pdo->prepare('INSERT INTO attendance (student_id, att_date, status) VALUES (?, ?, ?)');
        foreach ($names as $i => $n) {
            $si->execute($n);
            $id = (int)$pdo->lastInsertId();
            foreach (SUBJECTS as $j => $subject) $mi->execute([$id, $subject, 45 + (($i * 13 + $j * 17) % 50)]);
            for ($k = 1; $k <= 5; $k++) {
                $v = ($id * $k) % 7;
                $ai->execute([$id, date('Y-m-d', strtotime("-$k day")), $v === 0 ? 'A' : ($v === 1 ? 'L' : 'P')]);
            }
        }
        $log[] = 'Sample students, marks and attendance added.';
    }
} catch (PDOException $e) {
    $log[] = 'ERROR: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><title>Install</title><link rel="stylesheet" href="style.css"></head>
<body><main>
<h1>Setup</h1>
<?php foreach ($log as $l): ?><p><?= e($l) ?></p><?php endforeach; ?>
<p><a class="btn" href="index.php" style="text-decoration:none;display:inline-block">Go to login</a></p>
<p class="note">For safety, delete <b>install.php</b> after setup.</p>
</main></body></html>
