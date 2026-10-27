// Grades page

function vGr() {
    $('view').innerHTML =
        '<div class="bar">' + classSel('gc', 'grRows') +
        '<span class="note" style="margin:0">Enter marks out of 100. Changes save automatically.</span>' +
        '</div>' +
        '<div class="tw" id="gtab"></div>';
    grRows();
}

function grRows() {
    var cls = $('gc').value;
    var list = db.students.filter(function (s) { return !cls || s.cls === cls; });
    var head = SUBJ.map(function (s) { return '<th>' + s + '</th>'; }).join('');

    var rows = list.map(function (s) {
        var m = db.marks[s.id] || {};
        var a = avg(s.id);
        var cells = SUBJ.map(function (j) {
            return '<td><input type="number" min="0" max="100" value="' + (m[j] == null ? '' : m[j]) + '"' +
                ' onchange="setMark(' + s.id + ',\'' + j + '\',this)"' +
                ' aria-label="' + esc(s.name) + ' ' + j + '"></td>';
        }).join('');
        return '<tr><td>' + esc(s.name) + '</td>' + cells +
            '<td id="av' + s.id + '">' + (a == null ? '–' : a + '%') + '</td>' +
            '<td id="gr' + s.id + '"><b>' + grade(a) + '</b></td></tr>';
    }).join('');

    $('gtab').innerHTML =
        '<table><tr><th>Student</th>' + head + '<th>Average</th><th>Grade</th></tr>' + rows + '</table>';
}

function setMark(id, subject, el) {
    var v = el.value === '' ? '' : Math.max(0, Math.min(100, +el.value));
    el.value = v;

    db.marks[id] = db.marks[id] || {};
    db.marks[id][subject] = v;
    save();

    var a = avg(id);
    $('av' + id).textContent = a == null ? '–' : a + '%';
    $('gr' + id).innerHTML = '<b>' + grade(a) + '</b>';
}

// ---------- start ----------
if (shell('Grades')) vGr();
