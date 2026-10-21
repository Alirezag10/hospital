<?php
/** @var string $name */
$paths = [
    'refresh' => 'M20 7v5h-5M4 17v-5h5M6 7a7 7 0 0 1 12-1l2 3M4 15l2 3a7 7 0 0 0 12-1',
    'dashboard' => 'M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z',
    'patient' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0M19 8v6m-3-3h6',
    'doctor' => 'M6 3v5a4 4 0 0 0 8 0V3M4 3h4m4 0h4M10 12v4a5 5 0 0 0 10 0v-2M22 12a2 2 0 1 1-4 0 2 2 0 0 1 4 0',
    'ward' => 'M3 21V7h5m8 0h5v14M8 21V3h8v18M3 21h18M11 6h2m-2 4h2m-2 4h2m-2 7v-3h2v3M5 11h1m-1 4h1m12-4h1m-1 4h1',
    'admission' => 'M9 4H5v17h14V4h-4M9 2h6v4H9zM8 11h8m-8 4h5',
    'service' => 'M4 3h16v18H4zM12 7v6m-3-3h6M8 17h8',
    'discharge' => 'M13 3H4v18h9M10 12h11m-4-4 4 4-4 4',
    'eye' => 'M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12m13 0a3 3 0 1 1-6 0 3 3 0 0 1 6 0',
    'eye-off' => 'm3 3 18 18M10.58 10.59a2 2 0 0 0 2.83 2.82M9.88 5.09A10.94 10.94 0 0 1 12 5c6.5 0 10 7 10 7a19.8 19.8 0 0 1-3.05 3.62M6.61 6.61A20 20 0 0 0 2 12s3.5 7 10 7a10.4 10.4 0 0 0 5.39-1.61',
];
?>
<svg class="ui-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="<?= $paths[$name] ?? $paths['dashboard'] ?>" /></svg>
