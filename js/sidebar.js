function toggleNav() {
    const sidebar = document.getElementById("mySidebar");
    const main = document.getElementById("main");
    const button = document.getElementById("menuButton");
    if (sidebar.style.width === "270px") {
        sidebar.style.width = "0";
        main.style.marginLeft = "0";
        button.style.left = "10px"; // ย้ายปุ่มไปที่ด้านซ้ายสุดเมื่อ Sidebar ปิด
        menuButton.innerHTML = "☰";
    } else {
        sidebar.style.width = "270px";
        main.style.marginLeft = "270px";
        button.style.left = "10px"; // ย้ายปุ่มไปที่ด้านขวาของ Sidebar เมื่อเปิด
        menuButton.innerHTML = "☰";
    }
}

window.onload = function() {
    document.getElementById("mySidebar").style.width = "270px";
    document.getElementById("main").style.marginLeft = "270px";
    document.getElementById("menuButton").style.left = "10px";
}

jQuery(document).ready(function($) {
    var path = window.location.pathname.split("/").pop();
    if ( path = '' ) {
        path = 'user_page.php';
    }
    var target = $('#accordian ul li a[href="'+path+'"]');
    target.parent().addClass('active');
});

