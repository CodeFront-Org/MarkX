@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const exportModal = document.getElementById('exportModal');
        const typeSelect = document.querySelector('select[name="type"]');
        const exportForm = document.getElementById('exportForm');

        if (exportModal && typeSelect) {
            exportModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const exportType = button.getAttribute('data-export-type');
                if (exportType) {
                    typeSelect.value = exportType;
                    typeSelect.dispatchEvent(new Event('change'));
                }
            });
        }

        // Handle form submission and page refresh after export
        if (exportForm) {
            exportForm.addEventListener('submit', function() {
                // Close modal and refresh page after a short delay to allow download
                setTimeout(function() {
                    const modal = bootstrap.Modal.getInstance(exportModal);
                    if (modal) {
                        modal.hide();
                    }
                    window.location.reload();
                }, 2000);
            });
        }
    });
</script>
@endpush
