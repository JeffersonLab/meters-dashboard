<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Connections
    |--------------------------------------------------------------------------
    |
    | This array stores the connections that are used to connect to the
    | ACE and CUE LDAP servers.
    |
    | For detailed explanations of the various options see:
    |
    |    https://adldap2.github.io/Adldap2/#/setup?id=options
    |
    | Options where one sees env(VAR_NAME) can accept alternative values for those parameters
    | specified via an applications .env file.
    |
    */

    'connections' => [
        'ace' => [
            'auto_connect' => false,
            'connection' => \Adldap\Connections\Ldap::class,
            'settings' => [
                'schema' => \Adldap\Schemas\OpenLDAP::class,
                'hosts' => explode(' ', env('ACE_LDAP_HOSTS', 'accidm1.acc.jlab.org')),
                'port' => env('ACE_LDAP_PORT', 636),
                'timeout' => env('ACE_LDAP_TIMEOUT', 10),
                'base_dn' => env('ACE_LDAP_BASE_DN', 'dc=acc,dc=jlab,dc=org'),
                'follow_referrals' => true,
                'use_ssl' => env('ACE_LDAP_USE_SSL', true),
                'use_tls' => env('ACE_LDAP_USE_TLS', false),
                'custom_options'   => [
                    LDAP_OPT_X_TLS_REQUIRE_CERT => env('ACE_LDAPTLS_REQCERT',1),
                ]

            ],
        ],
        'cue' => [
            'auto_connect' => false,
            'connection' => \Adldap\Connections\Ldap::class,
            'settings' => [
                'schema' => \Adldap\Schemas\OpenLDAP::class,
                'hosts' => explode(' ', env('CUE_LDAP_HOSTS', 'jlds-web.jlab.org')),
                'port' => env('CUE_LDAP_PORT', 389),
                'timeout' => env('CUE_LDAP_TIMEOUT', 10),
                'base_dn' => env('CUE_LDAP_BASE_DN', 'DC=lds,DC=jlab,DC=org'),
                'follow_referrals' => true,
                'use_ssl' => env('CUE_LDAP_USE_SSL', false),
                'use_tls' => env('CUE_LDAP_USE_TLS', false),
                'custom_options'   => [
                    LDAP_OPT_X_TLS_REQUIRE_CERT => env('CUE_LDAPTLS_REQCERT',0),
                    LDAP_OPT_X_TLS_CACERTFILE => env('CUE_LDAP_TLS_CACERTFILE','/etc/pki/tls/cert.pem'),
                ]
            ],
        ],
    ],

];
