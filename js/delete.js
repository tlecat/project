document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-job');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const jobId = this.getAttribute('data-job-id');

            Swal.fire({
                title: 'คุณแน่ใจหรือไม่?',
                text: "คุณต้องการยกเลิกงานนี้หรือไม่?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ใช่',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`../delete_job.php?id=${jobId}`, {
                        method: 'GET'
                    }).then(response => {
                        if (response.ok) {
                            Swal.fire(
                                'ยกเลิกแล้ว!',
                                'งานของคุณถูกยกเลิกเรียบร้อย.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'ยกเลิกไม่สำเร็จ!',
                                'มีข้อผิดพลาดในการยกเลิกงาน.',
                                'error'
                            );
                        }
                    }).catch(error => {
                        Swal.fire(
                            'ยกเลิกไม่สำเร็จ!',
                            'มีข้อผิดพลาดในการยกเลิกงาน.',
                            'error'
                        );
                    });
                }
            });
        });
    });
});
