<?php

return [
    /**
     * Your telegram id. This needs to give ability to the bot to recognize you as superadmin
     */
    'owner_id' => env('TELEGRAM_BOT_OWNER_ID'),

    /**
     * You can restrict access to your bot and make users to request you for access by setting this parameter to 0
     */
    'free_access' => env('TELEGRAM_BOT_FREE_ACCESS',1),
    'donation_address' => env('AUTHOR_DONATION_ADDRESS',''),
    'openapi_token' => env('OPENAPI_TOKEN',''),
    'read_only_rating' => env('READ_ONLY_RATING',-10),
];
