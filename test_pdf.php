<?php

require 'vendor/autoload.php';

try {
    $app = require_once 'bootstrap/app.php';
    
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    
    echo "Laravel app loaded\n";
    
    // Bootstrap the app
    $app->make('cache');
    $app->make('config');
    
    // Get the Pdf class directly (not via facade)
    $dompdf = $app->make('dompdf.wrapper');
    
    $html = '<h1>Test PDF</h1><p>This is a test PDF.</p>';
    
    $pdf = $dompdf->loadHTML($html)
        ->setPaper('a4')
        ->setOption('defaultFont', 'Arial')
        ->setOption('isHtml5ParserEnabled', true);
    
    echo "PDF generated successfully\n";
    echo "PDF object: " . get_class($pdf) . "\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
