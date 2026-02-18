<?php $base = (defined('BASE_URL') ? rtrim(BASE_URL, '/') : '') . '/'; ?>
<script>
document.querySelectorAll('.logout-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        var url = this.getAttribute('href');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Log Out?',
                text: 'Are you sure you want to log out?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Log out'
            }).then(function(r) {
                if (r.isConfirmed) window.location.href = url;
            });
        } else { window.location.href = url; }
    });
});
</script>
<script src="<?php echo htmlspecialchars($base); ?>assets/js/lab-loader.js"></script>
<script src="<?php echo htmlspecialchars($base); ?>assets/js/global-alert.js"></script>
<script src="<?php echo htmlspecialchars($base); ?>assets/js/global-prompt.js"></script>
</body>
</html>
