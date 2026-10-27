// Reports page

function vRep() {
    $('view').innerHTML =
        '<div class="bar">' + classSel('rc', 'repRows') + '</div>' +
        '<div class="tw" id="rtab"></div>' +
        '<p class="note">Attendance counts Present and Late as attended. ' +
        'Grades: A 80+, B 70+, C 60+, D 50+, F below 50.</p>';
    repRows();
}

function repRows() {
    var cls = $('rc').value;
    var list = db.students.filter(function (s) { return !cls || s.cls === cls; });
    list.sort(function (a, b) { return (avg(b.id) || 0) - (avg(a.id) || 0); });

    var rows = list.map(function (s, i) {
        var st = status(s.id);
        var a = attPct(s.id);
        var g = avg(s.id);
        return '<tr>' +
            '<td>' + (i + 1) + '</td>' +
            '<td>' + esc(s.name) + '</td>' +
            '<td>' + esc(s.cls) + '</td>' +
            '<td>' + (a == null ? '–' : a + '%') + '</td>' +
            '<td>' + (g == null ? '–' : g + '%') + '</td>' +
            '<td><b>' + grade(g) + '</b></td>' +
            '<td><span class="tag ' + st[1] + '">' + st[0] + '</span></td>' +
            '</tr>';
    }).join('');

    $('rtab').innerHTML =
        '<table><tr><th>Rank</th><th>Student</th><th>Class</th><th>Attendance</th>' +
        '<th>Average</th><th>Grade</th><th>Status</th></tr>' + rows + '</table>';
}

// ---------- start ----------
if (shell('Reports')) vRep();
