<?php
return [
    // Varsayılan istasyon kodu (env ile override edilebilir)
    'default_code' => env('DEFAULT_STATION_CODE', '0000'),

    // IP -> istasyon kodu eşlemesi. Gerekirse burada çoğaltın.
    // Örnek:
    // '172.20.10.2' => '1511',
    // '172.20.10.3' => '1512',
    'ip_map' => [
    ],
];
