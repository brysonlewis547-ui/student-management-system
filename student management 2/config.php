<?php
// ===== Database settings (XAMPP defaults) =====
const DB_HOST = 'localhost';
const DB_NAME = 'sms';
const DB_USER = 'root';
const DB_PASS = '';          // XAMPP root has no password by default

const SUBJECTS = ['Mathematics', 'English', 'Science', 'History'];

session_start();

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            die('Database connection failed. Start MySQL in XAMPP and run <a href="install.php">install.php</a> first.');
        }
    }
    return $pdo;
}

function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// ---------- session ----------
function require_login(): void {
    if (empty($_SESSION['user'])) { header('Location: index.php'); exit; }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        die('Invalid request. Go back and try again.');
    }
}

// ---------- calculations ----------
function grade($a): string {
    if ($a === null) return '–';
    if ($a >= 80) return 'A';
    if ($a >= 70) return 'B';
    if ($a >= 60) return 'C';
    if ($a >= 50) return 'D';
    return 'F';
}

function status_of($att, $avg): array {
    $lowAtt = $att !== null && $att < 75;
    $lowAvg = $avg !== null && $avg < 50;
    if ($lowAtt && $lowAvg) return ['Urgent', 'bad'];
    if ($lowAtt || $lowAvg) return ['At risk', 'warn'];
    return ['On track', 'ok'];
}

function classes(): array {
    return db()->query('SELECT DISTINCT class_name FROM students ORDER BY class_name')
               ->fetchAll(PDO::FETCH_COLUMN);
}

/** Students with attendance % and average mark (null when no data). */
function students_with_stats(string $cls = ''): array {
    $sql = "SELECT s.id, s.name, s.class_name,
              (SELECT ROUND(100 * SUM(a.status <> 'A') / COUNT(*)) FROM attendance a WHERE a.student_id = s.id) AS att,
              (SELECT ROUND(AVG(m.score)) FROM marks m WHERE m.student_id = s.id) AS avg
            FROM students s " . ($cls !== '' ? 'WHERE s.class_name = ? ' : '') . 'ORDER BY s.name';
    $st = db()->prepare($sql);
    $st->execute($cls !== '' ? [$cls] : []);
    $rows = $st->fetchAll();
    foreach ($rows as &$r) {
        $r['att'] = $r['att'] === null ? null : (int)$r['att'];
        $r['avg'] = $r['avg'] === null ? null : (int)$r['avg'];
        $r['status'] = status_of($r['att'], $r['avg']);
    }
    return $rows;
}

function class_select(string $selected): string {
    $h = '<select name="cls" onchange="this.form.submit()"><option value="">All classes</option>';
    foreach (classes() as $c) {
        $h .= '<option value="' . e($c) . '"' . ($c === $selected ? ' selected' : '') . '>' . e($c) . '</option>';
    }
    return $h . '</select>';
}
