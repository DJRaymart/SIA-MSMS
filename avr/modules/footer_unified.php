        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php
if (!defined('APP_ROOT')) { require_once dirname(__DIR__, 2) . '/auth/path_config_loader.php'; }
include (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/partials/unified_footer.php';
?>
