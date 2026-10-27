// Dashboard page

function vDash() {
    var rate = [], scores = [];
    db.students.forEach(function (s) {
        var a = attPct(s.id);
        var g = avg(s.id);
        if (a != null) rate.push(a);
        if (g != null) scores.push(g);
    });

    var mean = function (arr) {
        return arr.length
            ? Math.round(arr.reduce(function (x, y) { return x + y; }, 0) / arr.length)
            : 0;
    };
    var risk = db.students.filter(function (s) {
        return status(s.id)[1] !== 'ok';
    });

    var rows = risk.map(function (s) {
        var st = status(s.id);
        return '<tr>' +
            '<td>' + esc(s.name) + '</td>' +
            '<td>' + esc(s.cls) + '</td>' +
            '<td>' + attPct(s.id) + '%</td>' +
            '<td>' + avg(s.id) + '%</td>' +
            '<td><span class="tag ' + st[1] + '">' + st[0] + '</span></td>' +
            '</tr>';
    }).join('') || '<tr><td colspan="5">Everyone is on track.</td></tr>';

    $('view').innerHTML =
        '<div class="cards">' +
        '<div class="card"><b>' + db.students.length + '</b><span>Students</span></div>' +
        '<div class="card"><b>' + classes().length + '</b><span>Classes</span></div>' +
        '<div class="card"><b>' + mean(rate) + '%</b><span>Average attendance</span></div>' +
        '<div class="card"><b>' + mean(scores) + '%</b><span>Average score</span></div>' +
        '</div>' +
        '<h3 style="margin-bottom:10px">Students needing attention (' + risk.length + ')</h3>' +
        '<div class="tw"><table>' +
        '<tr><th>Student</th><th>Class</th><th>Attendance</th><th>Average</th><th>Status</th></tr>' +
        rows +
        '</table></div>' +
        '<p class="note">At risk means attendance below 75% or average below 50%.</p>';
}

// ---------- start ----------
if (shell('Dashboard')) vDash();
