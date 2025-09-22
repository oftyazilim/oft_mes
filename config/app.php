<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'oft_mes'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'tr'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'tr'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'tr_TR'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Public Photo Base URL
    |--------------------------------------------------------------------------
    | Fotoğrafların servis edildiği public URL kökü. .env'de PHOTO_BASE_URL ile
    | override edilebilir. Örn: http://your-host:8080/photos/
    */
    'photo_base_url' => env('PHOTO_BASE_URL', 'http://192.6.2.110:8080/photos/'),

    /*
    |--------------------------------------------------------------------------
    | File System External Mounts (SMB/CIFS)
    |--------------------------------------------------------------------------
    | Bu değerler kod içinde env() yerine config() ile okunur; config:cache ile
    | birlikte güvenli ve öngörülebilir davranır.
    */
    'photo_kk_dir' => env('PHOTO_KK_DIR', '\\\\192.6.2.4\\canovate_elektronik\\01_GENEL\\15_OFT\\fotolar\\kk-fotolari\\'),
    'photo_sk_dir' => env('PHOTO_SK_DIR', '\\\\192.6.2.4\\canovate_elektronik\\01_GENEL\\15_OFT\\fotolar\\sk-fotolari\\'),
    'technical_drawings_dir' => env('TECHNICAL_DRAWINGS_DIR', '\\\\192.6.2.4\\canovate_elektronik\\12_DOKUMANTASYON\\YAYINLI RESİMLER\\ÜRETİM'),
    'file_search_base_dir' => env('FILE_SEARCH_BASE_DIR', '\\\\192.6.2.110\\oft\\TeknikResimler'),

];
