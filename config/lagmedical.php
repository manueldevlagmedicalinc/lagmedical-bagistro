<?php

return [
    'enabled' => true,

    'quote_mode_channels' => [
        'default',
    ],

    'quote_mode' => [
        'hide_prices' => true,
        'hide_totals' => true,
        'hide_checkout_payment' => true,
        'hide_coupons' => true,
        'hide_tax' => true,
        'hide_shipping_amounts' => true,
        'cart_button_label' => [
            'en' => 'Submit Order Request',
            'es' => 'Enviar solicitud de pedido',
            'pt_BR' => 'Enviar solicitacao de pedido',
        ],
        'checkout_button_label' => [
            'en' => 'Submit Order Request',
            'es' => 'Enviar solicitud de pedido',
            'pt_BR' => 'Enviar solicitacao de pedido',
        ],
        'review_message' => [
            'en' => 'Orders are reviewed and confirmed by our sales department before processing.',
            'es' => 'Los pedidos son revisados y confirmados por nuestro departamento de ventas antes de su procesamiento.',
            'pt_BR' => 'Os pedidos sao revisados e confirmados pelo nosso departamento comercial antes do processamento.',
        ],
    ],

    'whatsapp' => [
        'enabled' => true,
        'phone' => '17862503040',
        'button_label' => [
            'en' => 'Ask on WhatsApp',
            'es' => 'Consultar por WhatsApp',
            'pt_BR' => 'Consultar no WhatsApp',
        ],
        'message_template' => [
            'en' => 'Hello Lag Medical, I need this product: :product_name. I saw it at this link: :product_url',
            'es' => 'Hola Lag Medical, necesito este producto: :product_name. Lo vi en este link: :product_url',
            'pt_BR' => 'Ola Lag Medical, preciso deste produto: :product_name. Eu vi neste link: :product_url',
        ],
    ],
];
