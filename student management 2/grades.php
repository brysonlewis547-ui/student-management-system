<?php
require 'config.php';
require 'layout.php';
require_login();

$cls = $_GET['cls'] ?? $_POST['cls'] ?? '';

function load_students(string $cls): array {
    $st = db()->prepare('SELECT * FROM students ' . ($cls !== '' ? 'WHERE class_name = ? ' : '') . 'ORDER BY name');
    $st->execute($cls !== '' ? [$cls] : []);
    return $st->fetchAll();
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $up  = db()->prepare("INSERT INTO marks (student_id, subject, score) VALUES (?, ?, ?)
                          ON DUPLICATE KEY UPDATE score = VALUES(score)");
    $del = db()->prepare('DELETE FROM marks WHERE student_id = ? AND subject = ?');
    foreach (load_students($cls) as $s) {
        foreach (SUBJECTS as $j => $subject) {
            $v = trim($_POST['marks'][$s['id']][$j] ?? '');
            if ($v === '') { $del->execute([$s['id'], $subject]); }
            elseif (is_numeric($v)) { $up->execute([$s['id'], $subject, max(0, min(100, (int)round($v)))]); }
        }
    }
    $msg = 'Marks saved.';
}

$students = students_with_stats($cls);
$marks = [];
foreach (db()->query('SELECT student_id, subject, score FROM marks') as $r) {
    $marks[$r['student_id']][$r['subject']] = $r['score'];
}

page_start('Grades', 'Grades');
?>
<form method="get" class="bar">
    <?= class_select($cls) ?>
</form>

<form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="cls" value="<?= e($cls) ?>">
    <div class="bar">
        <button class="btn" type="submit">Save marks</button>
        <span class="note" style="margin:0">Enter marks out of 100.</span>
        <?php if ($msg): ?><b class="ok"><?= e($msg) ?></b><?php endif; ?>
    </div>
    <div class="tw"><table>
        <tr><th>Student</th><?php foreach (SUBJECTS as $s): ?><th><?= e($s) ?></th><?php endforeach; ?><th>Average</th><th>Grade</th></tr>
        <?php foreach ($students as $s): ?>
        <tr>
            <td><?= e($s['name']) ?></td>
            <?php foreach (SUBJECTS as $j => $subject): ?>
            <td><input type="number" min="0" max="100" name="marks[<?= $s['id'] ?>][<?= $j ?>]"
                       value="<?= e($marks[$s['id']][$subject] ?? '') ?>"
                       aria-label="<?= e($s['name'] . ' ' . $subject) ?>"></td>
            <?php endforeach; ?>
            <td><?= $s['avg'] === null ? '–' : $s['avg'] . '%' ?></td>
            <td><b><?= grade($s['avg']) ?></b></td>
        </tr>
        <?php endforeach; ?>
    </table></div>
</form>
<?php page_end();
