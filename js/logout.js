// ตั้งค่าระยะเวลาในการล็อกเอาท์อัตโนมัติ (หน่วยเป็นมิลลิวินาที)
const logoutTime = 30 * 60 * 1000; // 30 นาที

let logoutTimer;

function resetLogoutTimer() {
    clearTimeout(logoutTimer);
    logoutTimer = setTimeout(() => {
        window.location.href = 'logout.php';
    }, logoutTime);
}

// ตรวจจับเหตุการณ์ที่เกิดขึ้น
window.onload = resetLogoutTimer;
window.onmousemove = resetLogoutTimer;
window.onkeypress = resetLogoutTimer;
window.onclick = resetLogoutTimer;
window.onscroll = resetLogoutTimer;