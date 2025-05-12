@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const exportModal = document.getElementById('exportModal');
        const typeSelect = document.getElementById('type');

        exportModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const exportType = button.getAttribute('data-export-type');
            if (exportType) {
                typeSelect.value = exportType;
                typeSelect.dispatchEvent(new Event('change'));
            }
        });
    });
</script>
@endpush
