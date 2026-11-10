<?php
function page_start(string $title, string $active): void {
    $pages = [['Dashboard', 'dashboard.php'], ['Students', 'students.php'], ['Attendance', 'attendance.php'],
              ['Grades', 'grades.php'], ['Reports', 'reports.php']];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($title) ?> - Student Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h2 style="font-size:1.1rem">Student Management System</h2>
    <div>
        <span><?= e($_SESSION['user']['name']) ?> (<?= e($_SESSION['user']['role']) ?>)</span>
        <a class="btn ghost sm" href="logout.php">Sign out</a>
    </div>
</header>
<nav>
<?php foreach ($pages as $p): ?>
    <a href="<?= $p[1] ?>"<?= $p[0] === $active ? ' class="on"' : '' ?>><?= $p[0] ?></a>
<?php endforeach; ?>
</nav>
<main>
<?php
}

function page_end(): void {
    echo "</main>\n</body>\n</html>";
}
