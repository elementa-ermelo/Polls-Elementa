<?php
// Clear all opcache
if (function_exists('opcache_reset')) {
    opcache_reset();
}

// Clear Laravel cache
shell_exec('php artisan cache:clear');
shell_exec('php artisan view:clear');
shell_exec('php artisan config:clear');

echo "Cache cleared!";
?>
