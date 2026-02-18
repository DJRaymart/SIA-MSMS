        </div>
    </main>
</div>

<?php
$app_root = dirname(__DIR__);
if (!defined('BASE_URL')) { require_once $app_root . '/auth/path_config_loader.php'; }
include $app_root . '/partials/unified_footer.php';
?>
