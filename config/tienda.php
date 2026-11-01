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
    'email' => env('TIENDA_EMAIL', 'tezancompany@gmail.com'),

    // Clave del admin al crearlo por primera vez; vacía = se genera una aleatoria
    'admin_password' => env('ADMIN_INITIAL_PASSWORD'),

    // Plazos que se prometen al cliente, contados desde que se confirma el pago
    'entrega_lima' => env('TIENDA_ENTREGA_LIMA', 'dentro de las 12 horas'),
    'entrega_provincias' => env('TIENDA_ENTREGA_PROVINCIAS', 'de 1 a 2 días'),

    // Tiempo para pagar; después el pedido se cancela y el stock se libera
    'horas_para_pagar' => (int) env('TIENDA_HORAS_PARA_PAGAR', 24),

    // Yape: número al que el cliente paga y titular que verá en su app
    'yape' => env('TIENDA_YAPE'),
    'yape_titular' => env('TIENDA_YAPE_TITULAR', 'TEZAN S.A.C.'),

    // WhatsApp: número con código de país, solo dígitos (ej. 51942817550)
    'whatsapp' => env('TIENDA_WHATSAPP'),

    'redes' => [
        'instagram' => env('TIENDA_INSTAGRAM'),
        'facebook' => env('TIENDA_FACEBOOK'),
        'tiktok' => env('TIENDA_TIKTOK'),
    ],
];
