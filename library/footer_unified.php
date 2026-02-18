        </div>
    </main>
</div>

<style>
/* DataTables wrapper - Show X entries and Search layout */
.dataTables_wrapper .dataTables_length {
    float: left;
    margin-bottom: 1rem;
}
.dataTables_wrapper .dataTables_filter {
    float: right;
    margin-bottom: 1rem;
}
.dataTables_wrapper::after {
    content: '';
    display: block;
    clear: both;
}
.dataTables_wrapper .dataTables_length label,
.dataTables_wrapper .dataTables_filter label {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #64748b;
}
.dataTables_wrapper .dataTables_length select {
    margin: 0 0.25rem;
    padding: 0.25rem 0.5rem;
    border: 1px solid #cbd5e1;
    border-radius: 0.375rem;
    background: #fff;
}
.dataTables_wrapper .dataTables_filter input {
    margin-left: 0.5rem;
    padding: 0.375rem 0.75rem;
    border: 1px solid #cbd5e1;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    min-width: 200px;
}
/* DataTables pagination as buttons */
.dataTables_wrapper .dataTables_paginate .paginate_button,
.dataTables_wrapper .dataTables_paginate .paginate_button.previous,
.dataTables_wrapper .dataTables_paginate .paginate_button.next {
    display: inline-block;
    margin: 0 2px;
    padding: 0.5rem 0.75rem;
    border: 1px solid #cbd5e1;
    border-radius: 0.5rem;
    background: #fff;
    color: #475569;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s, color 0.15s;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover,
.dataTables_wrapper .dataTables_paginate .paginate_button.previous:hover,
.dataTables_wrapper .dataTables_paginate .paginate_button.next:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
    color: #1e293b;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current,
.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
.dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
    opacity: 0.5;
    cursor: not-allowed;
    background: #f8fafc;
}
/* DataTables info and pagination - "Showing 1 to 10 of 13 entries" */
.dataTables_wrapper .dataTables_info {
    padding: 0.75rem 0;
    font-size: 0.875rem;
    color: #64748b;
    float: left;
}
.dataTables_wrapper .dataTables_paginate {
    float: right;
    padding: 0.75rem 0;
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        if ($('.data-table').length) {
            $('.data-table').DataTable({
                "pageLength": 10,
                "responsive": true,
                "language": {
                    "search": "Search:",
                    "searchPlaceholder": "Type to search...",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "infoEmpty": "No entries to show",
                    "infoFiltered": "(filtered from _MAX_ total entries)",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Next",
                        "previous": "Previous"
                    }
                },
                "initComplete": function() {
                    $('.dataTables_filter input').attr('placeholder', 'Type to search...');
                }
            });
        }
    });

    function confirmDelete(item, id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#ef4444',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `delete_${item}.php?id=${id}`;
            }
        });
    }
</script>
<?php
if (!defined('APP_ROOT')) { require_once dirname(__DIR__) . '/auth/path_config_loader.php'; }
include (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/partials/unified_footer.php';
?>
