{{-- Delete FTP User Modal --}}
<x-delete-confirm-modal
    :show="$showDeleteModal"
    title="Delete FTP User"
    message="Are you sure you want to delete this FTP user? This action cannot be undone."
    confirm-action="deleteUser"
    cancel-action="closeModal"
    note="User files will be preserved. Only the FTP account will be removed."
/>
