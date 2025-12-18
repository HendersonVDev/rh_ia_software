<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Mailbox Driver
     |--------------------------------------------------------------------------
     |
     | O driver a ser usado para verificar e processar e-mails.
     | Usaremos 'imap' para conectar a um servidor de e-mail remoto.
     |
     */

    'driver' => env('MAILBOX_DRIVER', 'imap'),

    /*
     |--------------------------------------------------------------------------
     | Configurações IMAP
     |--------------------------------------------------------------------------
     |
     | Configurações para conectar ao seu servidor IMAP.
     | Todos os valores devem ser definidos no seu arquivo .env.
     |
     */

    'imap' => [
        'host'          => env('MAILBOX_HOST'),
        'port'          => env('MAILBOX_PORT', 993),
        'protocol'      => env('MAILBOX_PROTOCOL', 'imap'),
        'encryption'    => env('MAILBOX_ENCRYPTION', 'ssl'),
        'validate_cert' => env('MAILBOX_VALIDATE_CERT', true),
        'username'      => env('MAILBOX_USERNAME'),
        'password'      => env('MAILBOX_PASSWORD'),
        'manager'       => env('MAILBOX_MANAGER', 'INBOX'), // Pasta onde buscar os e-mails
        'delete_after_processing' => env('MAILBOX_DELETE', false),
    ],

    /*
     |--------------------------------------------------------------------------
     | Configurações de Queue (Fila)
     |--------------------------------------------------------------------------
     |
     | O nome da fila a ser usada ao despachar os Jobs de Inbound Mail.
     |
     */
     'queue' => env('MAILBOX_QUEUE', 'default'),
];
