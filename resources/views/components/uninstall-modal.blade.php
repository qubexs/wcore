<!-- Uninstall Modal Component  resources\views\components\uninstall-modal.blade.php-->
<div id="uninstallModal" class="modal" style="display: none;">
  <div class="modal-content">
    <h3>Uninstall Module</h3>
    <p>Do you want to keep the database tables and data, or remove everything?</p>
    <div class="modal-buttons">
      <button type="button" id="keepDataBtn" class="btn btn-secondary">Keep Tables</button>
      <button type="button" id="fullUninstallBtn" class="btn btn-danger">Full Uninstall</button>
      <button type="button" id="cancelBtn" class="btn btn-light">Cancel</button>
    </div>
  </div>
</div>

@once
<style>
.modal {
    position: fixed;
    z-index: 9999;
    left: 0; top: 0; width: 100%; height: 100%;
    background-color: rgba(0,0,0,0.5);
    justify-content: center;
    align-items: center;
}
.modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    width: 350px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}
.modal-buttons {
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
    gap: 10px;
}
.modal-buttons button {
    flex: 1;
    padding: 8px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
.btn-secondary { background-color: #6c757d; color: #fff; }
.btn-secondary:hover { background-color: #5a6268; }
.btn-danger { background-color: #dc3545; color: #fff; }
.btn-danger:hover { background-color: #c82333; }
.btn-light { background-color: #f8f9fa; color: #000; border: 1px solid #dee2e6; }
.btn-light:hover { background-color: #e2e6ea; }
</style>

<script>
(function() {
    let currentForm = null;

    window.confirmUninstall = function(form) {
        currentForm = form;
        const modal = document.getElementById('uninstallModal');
        modal.style.display = 'flex';
        return false;
    };

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('uninstallModal');
        const keepBtn = document.getElementById('keepDataBtn');
        const fullBtn = document.getElementById('fullUninstallBtn');
        const cancelBtn = document.getElementById('cancelBtn');

        keepBtn.addEventListener('click', function() {
            if (currentForm) {
                currentForm.querySelector('input[name="keep_data"]').value = '1';
                modal.style.display = 'none';
                currentForm.submit();
            }
        });

        fullBtn.addEventListener('click', function() {
            if (currentForm) {
                currentForm.querySelector('input[name="keep_data"]').value = '0';
                modal.style.display = 'none';
                currentForm.submit();
            }
        });

        cancelBtn.addEventListener('click', function() {
            modal.style.display = 'none';
            currentForm = null;
        });

        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
                currentForm = null;
            }
        });
    });
})();
</script>
@endonce