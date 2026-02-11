<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to delete this record? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Yes, Delete It</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // --- 2. AUTO-HIDE ALERTS ---
    // Finds any element with class 'alert' and hides it after 3 seconds
    setTimeout(function() {
        let alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            let bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 3000); // 3000ms = 3 Seconds

    // --- 3. HANDLE DELETE MODAL ---
    // Captures click on any button with class 'btn-delete'
    let deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
        // Button that triggered the modal
        let button = event.relatedTarget;
        // Extract info from data-href attribute
        let deleteUrl = button.getAttribute('data-href');
        // Update the modal's confirmation button
        let confirmBtn = document.getElementById('confirmDeleteBtn');
        confirmBtn.setAttribute('href', deleteUrl);
    });
</script>

<style>
    .empty-state {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }
    .empty-state i {
        font-size: 50px;
        margin-bottom: 15px;
        opacity: 0.3;
    }
</style>