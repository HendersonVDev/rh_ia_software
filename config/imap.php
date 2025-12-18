<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Account
    |--------------------------------------------------------------------------
    */
    'default' => env('IMAP_DEFAULT_ACCOUNT', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Accounts
    |--------------------------------------------------------------------------
    */
    'accounts' => [

        'default' => [
            // O host IMAP. Exemplo: 'imap.gmail.com'
            'host'  => env('IMAP_HOST', 'localhost'),
            // A porta IMAP. Usando 993 no .env, mas aqui o fallback é 993
            'port'  => env('IMAP_PORT', 993),
            // Protocolo de segurança: 'ssl', 'tls' ou 'null'
            'protocol' => env('IMAP_PROTOCOL', 'ssl'),
            // O nome de usuário para o login (endereço de e-mail)
            'username' => env('IMAP_USERNAME', 'exemplo@seudominio.com'),
            // A senha para o login
            'password' => env('IMAP_PASSWORD', 'sua_senha'),
            // Pasta padrão de entrada (caixa de entrada)
            'folder' => env('IMAP_FOLDER', 'INBOX'),
            // Timeout em segundos
            'timeout' => 30,
            // AQUI ESTÁ A CHAVE DE DEBUG: ATIVADA para registrar a tentativa de conexão no log
            'options' => [
                'debug' => true
            ],
            // Opções de conexão SSL/TLS
            'secure_options' => [
                // DESATIVAR VALIDAÇÃO DE CERTIFICADO PARA AMBIENTES LOCAIS
                'validate_cert' => env('IMAP_VALIDATE_CERT', false),
                // As opções abaixo são redundantes se validate_cert for false, mas permanecem para garantir
                'ssl_allow_self_signed' => false,
                'ssl_verify_peer' => false,
                'ssl_verify_peer_name' => false,
                'allow_self_signed' => false, // Deprecated
            ],
            // Configuração para anexos
            'attachments' => [
                'storage_driver' => 'public', // 'public', 'local', 's3', etc.
                'dir' => 'imap-attachments', // Subdiretório dentro do disco de armazenamento
            ],
            // Configuração para o Cache
            'cache' => [
                // Driver de cache do Laravel: 'file', 'redis', 'database', etc.
                'driver' => env('IMAP_CACHE_DRIVER', 'file'),
                'key' => env('IMAP_CACHE_KEY', 'imap'), // Prefixo da chave de cache
                'lifetime' => env('IMAP_CACHE_LIFETIME', 3600), // Tempo de vida em segundos
            ]
        ],

        // O restante das contas (como 'gmail') foi removido para simplificar
    ],

    /*
    |--------------------------------------------------------------------------
    | GERAL
    |--------------------------------------------------------------------------
    */
    // Define se o cliente IMAP deve criar pastas que não existem automaticamente
    'create_folders' => true,
];
