// Attendance page

function vAtt() {
    $('view').innerHTML =
        '<div class="bar">' +
        '<input type="date" id="ad" value="' + today() + '" onchange="attRows()">' +
        classSel('ac', 'attRows') +
        '<button class="btn ghost" onclick="allP()">Mark all present</button>' +
        '<button class="btn" onclick="saveAtt()">Save attendance</button>' +
        '</div>' +
        '<div id="amsg" class="note"></div>' +
        '<div class="tw" id="atab"></div>';
    attRows();
}

function attRows() {
    var date = $('ad').value;
    var cls = $('ac').value;
    var rec = db.att[date] || {};
    var list = db.students.filter(function (s) { return !cls || s.cls === cls; });
    var choices = [['P', 'Present'], ['L', 'Late'], ['A', 'Absent']];

    var rows = list.map(function (s) {
        var current = rec[s.id] || 'P';
        var radios = choices.map(function (o) {
            return '<label class="r"><input type="radio" name="a' + s.id + '" value="' + o[0] + '"' +
                (current === o[0] ? ' checked' : '') + '> ' + o[1] + '</label>';
        }).join('');
        return '<tr><td>' + esc(s.name) + '</td><td>' + esc(s.cls) + '</td><td>' + radios + '</td></tr>';
    }).join('');

    $('atab').innerHTML =
        '<table><tr><th>Student</th><th>Class</th><th>Status</th></tr>' + rows + '</table>';
}

function allP() {
    document.querySelectorAll('#atab input[value=P]').forEach(function (r) {
        r.checked = true;
    });
}

function saveAtt() {
    var date = $('ad').value;
    if (!date) {
        $('amsg').textContent = 'Choose a date first.';
        return;
    }

    db.att[date] = db.att[date] || {};
    var count = 0;
    db.students.forEach(function (s) {
        var r = document.querySelector('input[name=a' + s.id + ']:checked');
        if (r) {
            db.att[date][s.id] = r.value;
            count++;
        }
    });
    save();
    $('amsg').innerHTML = '<b class="ok">Attendance saved for ' + count + ' students on ' + date + '.</b>';
}

// ---------- start ----------
if (shell('Attendance')) vAtt();
