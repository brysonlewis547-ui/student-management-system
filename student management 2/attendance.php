<?php
require 'config.php';
require 'layout.php';
require_login();

$date = $_GET['date'] ?? $_POST['date'] ?? date('Y-m-d');
$d = DateTime::createFromFormat('Y-m-d', $date);
if (!$d || $d->format('Y-m-d') !== $date) $date = date('Y-m-d');
$cls = $_GET['cls'] ?? $_POST['cls'] ?? '';

function load_students(string $cls): array {
    $st = db()->prepare('SELECT * FROM students ' . ($cls !== '' ? 'WHERE class_name = ? ' : '') . 'ORDER BY name');
    $st->execute($cls !== '' ? [$cls] : []);
    return $st->fetchAll();
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $up = db()->prepare("INSERT INTO attendance (student_id, att_date, status) VALUES (?, ?, ?)
                         ON DUPLICATE KEY UPDATE status = VALUES(status)");
    $count = 0;
    foreach (load_students($cls) as $s) {
        $v = $_POST['status'][$s['id']] ?? '';
        if (in_array($v, ['P', 'L', 'A'], true)) { $up->execute([$s['id'], $date, $v]); $count++; }
    }
    $msg = "Attendance saved for $count students on $date.";
}

$students = load_students($cls);
$st = db()->prepare('SELECT student_id, status FROM attendance WHERE att_date = ?');
$st->execute([$date]);
$rec = $st->fetchAll(PDO::FETCH_KEY_PAIR);
$choices = ['P' => 'Present', 'L' => 'Late', 'A' => 'Absent'];

page_start('Attendance', 'Attendance');
?>
<form method="get" class="bar">
    <input type="date" name="date" value="<?= e($date) ?>" onchange="this.form.submit()">
    <?= class_select($cls) ?>
</form>

<form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="date" value="<?= e($date) ?>">
    <input type="hidden" name="cls" value="<?= e($cls) ?>">
    <div class="bar">
        <button type="button" class="btn ghost" onclick="document.querySelectorAll('input[value=P]').forEach(r => r.checked = true)">Mark all present</button>
        <button type="submit" class="btn">Save attendance</button>
    </div>
    <div class="note"><?php if ($msg): ?><b class="ok"><?= e($msg) ?></b><?php endif; ?></div>

    <div class="tw"><table>
        <tr><th>Student</th><th>Class</th><th>Status</th></tr>
        <?php foreach ($students as $s): $cur = $rec[$s['id']] ?? 'P'; ?>
        <tr>
            <td><?= e($s['name']) ?></td>
            <td><?= e($s['class_name']) ?></td>
            <td>
                <?php foreach ($choices as $k => $label): ?>
                    <label class="r"><input type="radio" name="status[<?= $s['id'] ?>]" value="<?= $k ?>"<?= $cur === $k ? ' checked' : '' ?>> <?= $label ?></label>
                <?php endforeach; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table></div>
</form>
<?php page_end();
