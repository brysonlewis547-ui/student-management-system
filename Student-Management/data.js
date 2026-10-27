// ===== Shared data, calculations and page shell =====
// ---------- settings ----------
var SUBJ = ['Mathematics', 'English', 'Science', 'History'];
var USERS = {
    admin: { p: 'Admin@123', role: 'Administrator' },
    teacher: { p: 'Teach@123', role: 'Teacher' }
};
var db = load();

var PAGES = [
    ['Dashboard', 'dashboard.html'],
    ['Students', 'students.html'],
    ['Attendance', 'attendance.html'],
    ['Grades', 'grades.html'],
    ['Reports', 'reports.html']
];

// ---------- data ----------
function seed() {
    var names = [
        ['Amina Wanjiru', 'Form 1'],
        ['Brian Otieno', 'Form 1'],
        ['Cynthia Njeri', 'Form 1'],
        ['David Kamau', 'Form 2'],
        ['Esther Achieng', 'Form 2'],
        ['Felix Mutua', 'Form 2']
    ];
    var d = { next: 7, students: [], att: {}, marks: {} };

    names.forEach(function (x, i) {
        d.students.push({ id: i + 1, name: x[0], cls: x[1] });
        d.marks[i + 1] = {};
        SUBJ.forEach(function (s, j) {
            d.marks[i + 1][s] = 45 + ((i * 13 + j * 17) % 50);
        });
    });

    var t = new Date();
    for (var k = 1; k <= 5; k++) {
        var dt = new Date(t - k * 864e5).toISOString().slice(0, 10);
        d.att[dt] = {};
        d.students.forEach(function (s) {
            var v = (s.id * k) % 7;
            d.att[dt][s.id] = v === 0 ? 'A' : v === 1 ? 'L' : 'P';
        });
    }
    return d;
}

function load() {
    try {
        var r = localStorage.getItem('sms_db');
        if (r) return JSON.parse(r);
    } catch (e) { }
    return seed();
}

function save() {
    try {
        localStorage.setItem('sms_db', JSON.stringify(db));
    } catch (e) { }
}

function $(id) {
    return document.getElementById(id);
}

function esc(s) {
    return String(s).replace(/[&<>"]/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
    });
}

function today() {
    return new Date().toISOString().slice(0, 10);
}

// ---------- session ----------
function getUser() {
    try {
        return JSON.parse(sessionStorage.getItem('sms_user'));
    } catch (e) {
        return null;
    }
}

function shell(active) {
    var u = getUser();
    if (!u) {
        location.href = 'index.html';
        return false;
    }
    $('who').textContent = u.name + ' (' + u.role + ')';
    $('nav').innerHTML = PAGES.map(function (p) {
        return '<a href="' + p[1] + '"' + (p[0] === active ? ' class="on"' : '') + '>' + p[0] + '</a>';
    }).join('');
    return true;
}

function logout() {
    try {
        sessionStorage.removeItem('sms_user');
    } catch (e) { }
    location.href = 'index.html';
}

// ---------- calculations ----------
function attPct(id) {
    var present = 0, total = 0;
    for (var d in db.att) {
        var v = db.att[d][id];
        if (v) {
            total++;
            if (v !== 'A') present++;
        }
    }
    return total ? Math.round(present / total * 100) : null;
}

function avg(id) {
    var m = db.marks[id] || {};
    var v = SUBJ.map(function (s) { return m[s]; })
        .filter(function (x) { return x !== '' && x != null; });
    return v.length
        ? Math.round(v.reduce(function (a, b) { return a + b; }, 0) / v.length)
        : null;
}

function grade(a) {
    if (a == null) return '–';
    if (a >= 80) return 'A';
    if (a >= 70) return 'B';
    if (a >= 60) return 'C';
    if (a >= 50) return 'D';
    return 'F';
}

function status(id) {
    var a = attPct(id);
    var g = avg(id);
    var lowAtt = a != null && a < 75;
    var lowAvg = g != null && g < 50;

    if (lowAtt && lowAvg) return ['Urgent', 'bad'];
    if (lowAtt || lowAvg) return ['At risk', 'warn'];
    return ['On track', 'ok'];
}

function classes() {
    var c = [];
    db.students.forEach(function (s) {
        if (c.indexOf(s.cls) < 0) c.push(s.cls);
    });
    return c.sort();
}

function classSel(id, fn) {
    return '<select id="' + id + '" onchange="' + fn + '()">' +
        '<option value="">All classes</option>' +
        classes().map(function (c) {
            return '<option>' + esc(c) + '</option>';
        }).join('') +
        '</select>';
}
