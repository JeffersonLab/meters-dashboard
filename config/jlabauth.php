<?php

return [

    /*
      |--------------------------------------------------------------------------
      | Available Methods
      |--------------------------------------------------------------------------
      |
      | This option controls the authentication methods that authenticateAll
      | is permitted to try. Attempts will be performed in the specified order.
      |
      | Supported: "internal", "ace_ldap", "cue_ldap"
      |
    */
    'methods' => explode(' ', env('JLAB_AUTH_METHODS', 'internal ace_ldap cue_ldap')),

    /*
      |--------------------------------------------------------------------------
      | Data for issuing JWT
      |--------------------------------------------------------------------------
      |
      | cookie is the name of the cookie that will store the jwt token
      | secret is used to sign/validate tokens
      | expires is the period of validity (in minutes) of issued tokens
      | send tells whether whether to send the signed token to the user
      |
      | When "send" is falsey, the JWT will be stored in a cookie that the
      | browser will send back and forth.  If an application wants to
      | send it in an authorization header insteas, set this to true so
      | that the signed token gets included in the post authentication response
      | body sent to clients.
      |
    */
    'jwt' => [
        'cookie' => env('JLAB_AUTH_JWT_COOKIE', 'X-JLAB-AUTH'),
        'secret' => env('JLAB_AUTH_JWT_SECRET', ''),
        'expires' => env('JLAB_AUTH_JWT_EXPIRES', 60),
        'send' => false,
    ],

    /*
      |--------------------------------------------------------------------------
      | Ace Staff Service
      |--------------------------------------------------------------------------
      |
      | Enter the url that provides REST API for fetching user info from
      | the ACE staff database.
      |
     */
    'staff_service_url' => env('ACE_STAFF_URL', 'https://accweb.acc.jlab.org/staff/data/users'),

    /*
     |--------------------------------------------------------------------------
     | Known Kerberos Realms
     |--------------------------------------------------------------------------
     |
     | The array of realms from which usernames can be extracted.
     |
     |
    */
    'kerberos_realms' => ['JLAB.ORG', 'ACC.JLAB.ORG'],

    /*
      |--------------------------------------------------------------------------
      | Authentication Model
      |--------------------------------------------------------------------------
      |
      | When using the "Eloquent" authentication driver, we need to know which
      | Eloquent model should be used to retrieve your users. Of course, it
      | is often just the "User" model but you may use descendant classes instead.
      |
     */
    'model' => 'Jlab\Auth\User',

    /*
      |--------------------------------------------------------------------------
      | Authentication Table
      |--------------------------------------------------------------------------
      |
      | When using the "Database" authentication driver, we need to know which
      | table should be used to retrieve your users. We have chosen a basic
      | default value but you may easily change it to any table you like.
      |
     */
    'table' => 'users',

    /*
      |--------------------------------------------------------------------------
      | Default format of User formattedName()
      |--------------------------------------------------------------------------
      |
      | Formats (using John Doe for examples)
      |
      | FIRSTNAME = Doe
      | LASTNAME = Doe
      | FLASTNAME = J_Doe
      | FIRSTLAST = John Doe
      | LASTFIRST = Doe, John
      |
     */
    'name_format' => 'FLASTNAME',

    /*
      |--------------------------------------------------------------------------
      | After Login URL
      |--------------------------------------------------------------------------
      |
      | The URL to which the user should be redirected following a successful login.
      |
     */
    'after_login' => '/',

];
