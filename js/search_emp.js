function searchTable() {
    var input, filter, table, tr, td, i, txtValue, found;
    input = document.getElementById("searchInput");
    filter = input.value.toUpperCase();
    table = document.getElementById("employeeTable"); // เปลี่ยนให้สอดคล้องกับ id ของตาราง
    tr = table.getElementsByTagName("tr");
    found = false;

    // ลบแถว "ไม่พบพนักงาน" หากมีอยู่
    var noResultsRow = document.getElementById("noResultsRow");
    if (noResultsRow) {
        noResultsRow.remove();
    }

    for (i = 1; i < tr.length; i++) { // เริ่มจาก index 1 เพื่อข้ามหัวตาราง
        tr[i].style.display = "none";
        td = tr[i].getElementsByTagName("td");

        if (td) {
            let empId = td[2].textContent || td[2].innerText; // รหัสพนักงานอยู่ที่ index 2
            let empFirstName = td[3].textContent || td[3].innerText; // ชื่อพนักงานอยู่ที่ index 3
            let empLastName = td[4].textContent || td[4].innerText; // นามสกุลพนักงานอยู่ที่ index 4

            // เงื่อนไขสำหรับการค้นหาในทั้งรหัสพนักงาน, ชื่อ, และนามสกุล
            if (
                empId.toUpperCase().indexOf(filter) > -1 ||
                empFirstName.toUpperCase().indexOf(filter) > -1 ||
                empLastName.toUpperCase().indexOf(filter) > -1
            ) {
                tr[i].style.display = "";
                found = true;
            }
        }
    }

    if (!found) {
        var tableBody = document.getElementById("employeeTable");
        var row = tableBody.insertRow(-1);
        row.id = "noResultsRow";
        var cell = row.insertCell(0);
        cell.colSpan = 9; // ครอบคลุมทุกคอลัมน์
        cell.className = "text-center";
        cell.textContent = "ไม่พบพนักงาน";
    }
}
