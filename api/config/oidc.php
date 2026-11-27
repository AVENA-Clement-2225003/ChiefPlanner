<?php

return [
    'issuer' => env('OIDC_ISSUER'),
    'client_id' => env('OIDC_CLIENT_ID'),
    'client_secret' => env('OIDC_CLIENT_SECRET'),
    'redirect_uri' => env('OIDC_REDIRECT_URI', rtrim((string) env('APP_URL'), '/').'/api/auth/callback'),
    'group_user' => env('OIDC_GROUP_USER', 'chiefplanner-dev-user'),
    'group_admin' => env('OIDC_GROUP_ADMIN', 'chiefplanner-dev-admin'),

    // Authentik management API, used only to add or remove members of the admin group (ADR 0002).
    'authentik' => [
        'api_url' => env('AUTHENTIK_API_URL'),
        'api_token' => env('AUTHENTIK_API_TOKEN'),
        'admin_group_id' => env('AUTHENTIK_ADMIN_GROUP_ID'),
    ],
];
