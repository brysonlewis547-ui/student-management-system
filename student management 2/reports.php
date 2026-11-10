<?php
require 'config.php';
require 'layout.php';
require_login();

$cls = $_GET['cls'] ?? '';
$list = students_with_stats($cls);
usort($list, fn($a, $b) => ($b['avg'] ?? 0) <=> ($a['avg'] ?? 0));

page_start('Reports', 'Reports');
?>
<form method="get" class="bar"><?= class_select($cls) ?></form>
<div class="tw"><table>
    <tr><th>Rank</th><th>Student</th><th>Class</th><th>Attendance</th><th>Average</th><th>Grade</th><th>Status</th></tr>
    <?php foreach ($list as $i => $s): ?>
    <tr>
        <td><?= $i + 1 ?></td>
        <td><?= e($s['name']) ?></td>
        <td><?= e($s['class_name']) ?></td>
        <td><?= $s['att'] === null ? '–' : $s['att'] . '%' ?></td>
        <td><?= $s['avg'] === null ? '–' : $s['avg'] . '%' ?></td>
        <td><b><?= grade($s['avg']) ?></b></td>
        <td><span class="tag <?= $s['status'][1] ?>"><?= $s['status'][0] ?></span></td>
    </tr>
    <?php endforeach; ?>
</table></div>
<p class="note">Attendance counts Present and Late as attended. Grades: A 80+, B 70+, C 60+, D 50+, F below 50.</p>
<?php page_end();
