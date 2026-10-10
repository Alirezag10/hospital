<?php
/** @var string $name */
$paths = [
    'dashboard' => 'M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z',
    'patient' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0M19 8v6m-3-3h6',
    'doctor' => 'M6 3v5a4 4 0 0 0 8 0V3M4 3h4m4 0h4M10 12v4a5 5 0 0 0 10 0v-2M22 12a2 2 0 1 1-4 0 2 2 0 0 1 4 0',
    'ward' => 'M3 21V7h5m8 0h5v14M8 21V3h8v18M3 21h18M11 6h2m-2 4h2m-2 4h2m-2 7v-3h2v3M5 11h1m-1 4h1m12-4h1m-1 4h1',
    'admission' => 'M9 4H5v17h14V4h-4M9 2h6v4H9zM8 11h8m-8 4h5',
    'service' => 'M4 3h16v18H4zM12 7v6m-3-3h6M8 17h8',
    'discharge' => 'M13 3H4v18h9M10 12h11m-4-4 4 4-4 4',
];
?>
<svg class="ui-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="<?= $paths[$name] ?? $paths['dashboard'] ?>" /></svg>
