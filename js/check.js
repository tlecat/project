document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('jobType').addEventListener('change', function() {
        var jobSubType = document.getElementById('jobSubType');
        jobSubType.innerHTML = '';

        var options = [];
        if (this.value === 'พัฒนาซอฟต์แวร์') {
            options = [
                { value: 'เลือกตำแหน่ง', text: 'เลือกตำแหน่ง' },
                { value: 'HIS', text: 'HIS' },
                { value: 'โปรแกรมสนับสนุน', text: 'โปรแกรมสนับสนุน' },
                { value: 'API', text: 'API' },
                { value: 'งานพัฒนาเว็บไซต์', text: 'งานพัฒนาเว็บไซต์' }
            ];
        } else if (this.value === 'ไอทีซัพพอร์ต') {
            options = [
                { value: 'เลือกตำแหน่ง', text: 'เลือกตำแหน่ง' },
                { value: 'ซ่อมคอมพิวเตอร์', text: 'ซ่อมคอมพิวเตอร์' },
                { value: 'ซ่อมปริ้นเตอร์', text: 'ซ่อมปริ้นเตอร์' },
                { value: 'เปลี่ยนหมึกปริ้นเตอร์', text: 'เปลี่ยนหมึกปริ้นเตอร์' },
                { value: 'งานสอนการใช้โปรแกรม', text: 'งานสอนการใช้โปรแกรม' }
            ];
        } else if (this.value === 'เครือข่าย') {
            options = [
                { value: 'เลือกตำแหน่ง', text: 'เลือกตำแหน่ง' },
                { value: 'ตัดต่อเก็บสายLAN', text: 'ตัดต่อเก็บสายLAN' },
                { value: 'จัดการเครือข่าย', text: 'จัดการเครือข่าย' },
                { value: 'งานสนับสนุน', text: 'งานสนับสนุน' },
                { value: 'ดูแลความปลอดภัย', text: 'ดูแลความปลอดภัย' }
            ];
        } else {
            options = [
                { value: 'ประเภทย่อย0', text: 'เลือกตำแหน่ง' }
            ];
        }

        options.forEach(function(option) {
            var opt = document.createElement('option');
            opt.value = option.value;
            opt.text = option.text;
            jobSubType.appendChild(opt);
        });
    });
});




