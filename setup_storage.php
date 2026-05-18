<?php
$target = __DIR__ . '/storage/app/public';
$link = __DIR__ . '/public/storage';

if (is_link($link)) {
    echo "Symlink already exists\n";
} else if (is_dir($link)) {
    echo "Directory exists (not a symlink)\n";
} else {
    // Windows: use mklink instead
    $cmd = 'mklink /D "' . $link . '" "' . $target . '"';
    exec($cmd, $output, $returnVar);

    if ($returnVar === 0) {
        echo "Symlink created successfully!\n";
    } else {
        echo "Failed to create symlink. Error: " . implode("\n", $output) . "\n";
        echo "Try running as Administrator or use this command:\n";
        echo 'mklink /D "public\storage" "storage\app\public"' . "\n";
    }
}
?>
