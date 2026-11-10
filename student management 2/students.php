<?php
require 'config.php';
require 'layout.php';
require_login();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'add') {
        $name = trim($_POST['name'] ?? '');
        $cls  = trim($_POST['cls'] ?? '');
        if ($name === '' || $cls === '') {
            $error = 'Enter both a name and a class.';
        } else {
            db()->prepare('INSERT INTO students (name, class_name) VALUES (?, ?)')->execute([$name, $cls]);
            header('Location: students.php');
            exit;
        }
    } elseif (($_POST['action'] ?? '') === 'delete') {
        // attendance and marks are removed automatically (ON DELETE CASCADE)
        db()->prepare('DELETE FROM students WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
        header('Location: students.php');
        exit;
    }
}

$students = db()->query('SELECT * FROM students ORDER BY id')->fetchAll();
page_start('Students', 'Students');
?>
<form method="post" class="bar">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <input name="name" placeholder="Full name" value="<?= e($_POST['name'] ?? '') ?>">
    <input name="cls" placeholder="Class (e.g. Form 1)" list="cl" value="<?= e($_POST['cls'] ?? '') ?>">
    <datalist id="cl"><?php foreach (classes() as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?></datalist>
    <button class="btn" type="submit">Add student</button>
</form>
<div class="err"><?= e($error) ?></div>

<div class="tw"><table>
    <tr><th>ID</th><th>Name</th><th>Class</th><th></th></tr>
    <?php foreach ($students as $s): ?>
    <tr>
        <td><?= $s['id'] ?></td>
        <td><?= e($s['name']) ?></td>
        <td><?= e($s['class_name']) ?></td>
        <td>
            <form method="post" onsubmit="return confirm('Remove this student and all their attendance and marks?')">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                <button class="btn ghost sm" type="submit">Remove</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table></div>
<?php page_end();
