<?php
require 'config.php';
require 'layout.php';
require_login();

$students = students_with_stats();
$mean = function (array $a) { return $a ? (int)round(array_sum($a) / count($a)) : 0; };
$rates  = array_filter(array_column($students, 'att'), fn($v) => $v !== null);
$scores = array_filter(array_column($students, 'avg'), fn($v) => $v !== null);
$risk   = array_filter($students, fn($s) => $s['status'][1] !== 'ok');

page_start('Dashboard', 'Dashboard');
?>
<div class="cards">
    <div class="card"><b><?= count($students) ?></b><span>Students</span></div>
    <div class="card"><b><?= count(classes()) ?></b><span>Classes</span></div>
    <div class="card"><b><?= $mean($rates) ?>%</b><span>Average attendance</span></div>
    <div class="card"><b><?= $mean($scores) ?>%</b><span>Average score</span></div>
</div>

<h3 style="margin-bottom:10px">Students needing attention (<?= count($risk) ?>)</h3>
<div class="tw"><table>
    <tr><th>Student</th><th>Class</th><th>Attendance</th><th>Average</th><th>Status</th></tr>
    <?php if (!$risk): ?>
        <tr><td colspan="5">Everyone is on track.</td></tr>
    <?php endif; ?>
    <?php foreach ($risk as $s): ?>
        <tr>
            <td><?= e($s['name']) ?></td>
            <td><?= e($s['class_name']) ?></td>
            <td><?= $s['att'] === null ? '–' : $s['att'] . '%' ?></td>
            <td><?= $s['avg'] === null ? '–' : $s['avg'] . '%' ?></td>
            <td><span class="tag <?= $s['status'][1] ?>"><?= $s['status'][0] ?></span></td>
        </tr>
    <?php endforeach; ?>
</table></div>
<p class="note">At risk means attendance below 75% or average below 50%.</p>
<?php page_end();
