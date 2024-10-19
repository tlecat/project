function searchTable() {
    var input, filter, table, tr, td, i, txtValue, found;
    input = document.getElementById("searchInput");
    filter = input.value.toUpperCase();
    table = document.getElementById("jobTable");
    tr = table.getElementsByTagName("tr");
    found = false;

    // ลบแถว "ไม่พบงาน" หากมีอยู่
    var noResultsRow = document.getElementById("noResultsRow");
    if (noResultsRow) {
        noResultsRow.remove();
    }

    for (i = 1; i < tr.length; i++) {
        tr[i].style.display = "none";
        td = tr[i].getElementsByTagName("td");
        
        // Debugging: ตรวจสอบว่าฟิลด์ต่างๆ ถูกต้องหรือไม่
        console.log("Emp ID: " + td[0].textContent);
        console.log("Emp Name: " + td[1].textContent);
        console.log("Job Title: " + td[2].textContent);

        if (td[0] || td[1] || td[2]) { // ค้นหาในคอลัมน์รหัสพนักงาน ชื่อ-นามสกุล และชื่องาน
            let empId = td[0].textContent || td[0].innerText;
            let empName = td[1].textContent || td[1].innerText;
            let jobTitle = td[2].textContent || td[2].innerText;

            if (empId.toUpperCase().indexOf(filter) > -1 || empName.toUpperCase().indexOf(filter) > -1 || jobTitle.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
                found = true;
            }
        }
    }

    if (!found) {
        var tableBody = document.getElementById("jobTable");
        var row = tableBody.insertRow(-1);
        row.id = "noResultsRow";
        var cell = row.insertCell(0);
        cell.colSpan = 8;
        cell.className = "text-center";
        cell.textContent = "ไม่พบงาน";
    }
}
