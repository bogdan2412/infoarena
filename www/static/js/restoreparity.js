function toggleParity(row) {
    if (row.className.search(/\beven\b/) != -1) {
        row.className = row.className.replace(/\beven\b/, "odd");
    } else if (row.className.search(/\bodd\b/) != -1) {
        row.className = row.className.replace(/\bodd\b/, "even");
    }
}

function recolorRow(parity, row) {
    if (parity == "odd") {
        row.className = row.className.replace(/\beven\b/, "odd");
    } else if (parity == "even") {
        row.className = row.className.replace(/\bodd\b/, "even");
    };
}

function recolorTable(table) {
    if (table.getElementsByTagName("tbody").length == 0) {
        return;
    }
    var tbody = table.getElementsByTagName("tbody")[0];

    if (tbody.getElementsByTagName("tr").length == 0) {
        return;
    }
    var trs = tbody.getElementsByTagName("tr");

    for (var i = 0; i < trs.length; ++i) {
        recolorRow((i % 2) ? "even" : "odd", trs[i]);
    }
}
