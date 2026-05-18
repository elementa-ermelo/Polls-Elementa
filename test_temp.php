<?php
$tempdir = sys_get_temp_dir();
echo "Temp directory: " . $tempdir . "\n";
echo "Is writable: " . (is_writable($tempdir) ? 'YES' : 'NO') . "\n";

if (is_writable($tempdir)) {
    $test = tempnam($tempdir, 'test');
    if ($test) {
        echo "Can create temp file: YES\n";
        echo "Created: " . $test . "\n";
        unlink($test);
    } else {
        echo "Can create temp file: NO\n";
    }
} else {
    echo "ERROR: Temp directory not writable!\n";
}
