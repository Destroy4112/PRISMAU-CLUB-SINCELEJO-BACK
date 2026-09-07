<?php

return [
   'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
   'front_url' => env('FRONT_URL'),
   'back_url' => env('BACK_URL'),
   'currency' => env('MERCADOPAGO_CURRENCY', 'COP'),
];
