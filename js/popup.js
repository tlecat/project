    document.addEventListener('DOMContentLoaded', function() {
        const modal = new bootstrap.Modal(document.getElementById('jobDetailsModal'));

        document.querySelectorAll('.view-details').forEach(button => {
            button.addEventListener('click', function() {
                const jobId = this.getAttribute('data-job-id');
                
                fetch(`assingnment_admin.php?id=${jobId}`)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('modalBody').innerHTML = data;
                        modal.show();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });
        });
    });
