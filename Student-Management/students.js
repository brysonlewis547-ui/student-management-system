// Students page

function vStu() {
    var options = classes().map(function (c) {
        return '<option value="' + esc(c) + '">' + esc(c) + '</option>';
    }).join('');

    var rows = db.students.map(function (s) {
        return '<tr>' +
            '<td>' + s.id + '</td>' +
            '<td>' + esc(s.name) + '</td>' +
            '<td>' + esc(s.cls) + '</td>' +
            '<td><button class="btn ghost sm" onclick="delStu(' + s.id + ')">Remove</button></td>' +
            '</tr>';
    }).join('');

    $('view').innerHTML =
        '<div class="bar">' +
        '<input id="sn" placeholder="Full name">' +
        '<input id="sc" placeholder="Class (e.g. Form 1)" list="cl">' +
        '<datalist id="cl">' + options + '</datalist>' +
        '<button class="btn" onclick="addStu()">Add student</button>' +
        '</div>' +
        '<div class="err" id="serr"></div>' +
        '<div class="tw"><table>' +
        '<tr><th>ID</th><th>Name</th><th>Class</th><th></th></tr>' +
        rows +
        '</table></div>';
}

function addStu() {
    var name = $('sn').value.trim();
    var cls = $('sc').value.trim();

    if (!name || !cls) {
        $('serr').textContent = 'Enter both a name and a class.';
        return;
    }
    var id = db.next++;
    db.students.push({ id: id, name: name, cls: cls });
    db.marks[id] = {};
    save();
    vStu();
}

function delStu(id) {
    if (!confirm('Remove this student and all their attendance and marks?')) return;

    db.students = db.students.filter(function (s) { return s.id !== id; });
    delete db.marks[id];
    for (var d in db.att) delete db.att[d][id];
    save();
    vStu();
}

// ---------- start ----------
if (shell('Students')) vStu();
