<?php

use Sil\PhpEnv\Env; // https://github.com/silinternational/php-env#class-env-summary-of-functions

/*---------------------------------------------------------------
 * Application Environment
 *---------------------------------------------------------------
 *
 * You can load different configurations depending on your
 * current environment. Setting the environment also influences
 * things like logging and error reporting.
 *
 * This can be set to anything, but default usage is:
 *
 *     development
 *     testing
 *     production
 *
 * NOTE: If you change these, also change the error_reporting() code in index.php
 *
 */

define('ENVIRONMENT', Env::requireEnv('ENVIRONMENT'));

/*---------------------------------------------------------------
 * General xForge Configuration
 *---------------------------------------------------------------
 */

if (! defined('SF_DATABASE')) {
    define('SF_DATABASE', 'scriptureforge');
}

if (! defined('MONGODB_CONN')) {
    define('MONGODB_CONN', 'mongodb://db:27017');
}

if (! defined('USE_MINIFIED_JS')) {
    if (defined('ENVIRONMENT') and ENVIRONMENT === 'development') {
        define('USE_MINIFIED_JS', false);
    } else {
        define('USE_MINIFIED_JS', true);
    }
}

if (! defined('USE_CDN')) {
    if (defined('ENVIRONMENT') and ENVIRONMENT === 'development') {
        define('USE_CDN', false);
    } else {
        define('USE_CDN', true);
    }
}

if (! defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', 'googleClientId');
}

if (! defined('GOOGLE_CLIENT_SECRET')) {
    define('GOOGLE_CLIENT_SECRET', 'googleClientSecret');
}

if (! defined('FACEBOOK_CLIENT_ID')) {
    define('FACEBOOK_CLIENT_ID', 'facebookClientId');
}

if (! defined('FACEBOOK_CLIENT_SECRET')) {
    define('FACEBOOK_CLIENT_SECRET', 'facebookClientSecret');
}

if (! defined('LANGUAGE_DEPOT_API_TOKEN')) {
    define('LANGUAGE_DEPOT_API_TOKEN', 'not-a-secret');
}

define('NG_BASE_FOLDER', 'angular-app/');
define('BCRYPT_COST', 7);

if (!defined('BUGSNAG_API_KEY')) {
    define('BUGSNAG_API_KEY', 'missing-bugsnag-api-key');
}

if (!defined('BUGSNAG_NOTIFY_RELEASE_STAGES')) {
    // The real values will be set by gulp
    define('BUGSNAG_NOTIFY_RELEASE_STAGES', 'not-set' );
}
