<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Ces trois clés doivent rester synchronisées avec l'ENUM moyen_paiement
    // de la table paiements (migration create_paiements_table) et avec le
    // cahier des charges (section 5.2). Si un nouvel opérateur est ajouté,
    // il faut modifier les DEUX (migration + ce fichier), sinon le webhook
    // recevra un secret introuvable (500) ou un paiement en base rejeté par
    // la contrainte ENUM.
    'wave' => [
        'webhook_secret' => env('WAVE_WEBHOOK_SECRET'),
    ],

    'orange_money' => [
        'webhook_secret' => env('ORANGE_MONEY_WEBHOOK_SECRET'),
    ],

    'free_money' => [
        'webhook_secret' => env('FREE_MONEY_WEBHOOK_SECRET'),
    ],

    'infobip' => [
        'base_url' => env('INFOBIP_BASE_URL'),   // ex: https://xxxxxx.api.infobip.com
        'api_key' => env('INFOBIP_API_KEY'),
        'sms_sender' => env('INFOBIP_SMS_SENDER'), // nom d'expediteur ou numero valide
        'whatsapp_sender' => env('INFOBIP_WHATSAPP_SENDER'), // numero WhatsApp enregistre chez Infobip
        'whatsapp_otp_template' => env('INFOBIP_WHATSAPP_OTP_TEMPLATE', 'otp_verification'),
    ],
    'otp_channel' => env('OTP_CHANNEL', 'sms'),
];
