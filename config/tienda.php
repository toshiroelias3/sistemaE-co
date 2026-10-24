<?php

/*
|--------------------------------------------------------------------------
| Datos de la tienda TEZAN
|--------------------------------------------------------------------------
| Se completan en .env. Cada elemento de la web que depende de un dato
| (botón de WhatsApp, íconos de redes, datos del proveedor en el Libro de
| Reclamaciones) solo se muestra cuando ese dato está configurado.
*/

return [
    'razon_social' => env('TIENDA_RAZON_SOCIAL', 'TEZAN S.A.C.'),
    'ruc' => env('TIENDA_RUC'),
    'direccion' => env('TIENDA_DIRECCION'),
    'email' => env('TIENDA_EMAIL', 'soporte@tezan.com'),

    // Número con código de país, solo dígitos (ej. 51942817550)
    'whatsapp' => env('TIENDA_WHATSAPP'),

    'redes' => [
        'instagram' => env('TIENDA_INSTAGRAM'),
        'facebook' => env('TIENDA_FACEBOOK'),
        'tiktok' => env('TIENDA_TIKTOK'),
    ],
];
