<?php

use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\HttpFoundation\Request;
use Api\Library\Shared\Website;
use Sil\PhpEnv\Env; // https://github.com/silinternational/php-env#class-env-summary-of-functions

require_once __DIR__ . "/vendor/autoload.php";
require_once "config.php";
require_once "version.php";

// The name of THIS file
define("SELF", basename(__FILE__));

/*--------------------------------------------------------------------
 * Load the bootstrap App
 *--------------------------------------------------------------------
 */

$app = new Silex\Application();

/*---------------------------------------------------------------
 * Error Reporting and Debugging
 *---------------------------------------------------------------
 *
 * Different environments will require different levels of error reporting and debugging.
 * By default development will show errors but testing and live will hide them.
 * By default development will have debugging on but testing and live will turn it off.
 */

switch (ENVIRONMENT) {
    case "development":
        error_reporting(E_ALL);
        $app["debug"] = true;
        break;

    case "testing":
    case "production":
        error_reporting(0);
        $app["debug"] = false;
        break;

    default:
        exit(
            "Error: The application environment is not set correctly. Please open the following file and correct this: " .
                self
        );
}

/*--------------------------------------------------------------------
 * Error Handling
 *--------------------------------------------------------------------
 */

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app["debug"]) {
        /** @noinspection PhpInconsistentReturnPointsInspection */
        return;
    }

    return Site\Handler\ErrorHandler::response($e, $code, $app);
});

ExceptionHandler::register($app["debug"]);

/*---------------------------------------------------------------
 * APPLICATION FOLDER NAME
 *---------------------------------------------------------------
 *
 * If you want this front controller to use a different "application"
 * folder then the default one you can set its name here. The folder
 * can also be renamed or relocated anywhere on your server.  If
 * you do, use a full server path.
 *
 * NO TRAILING SLASH!
 *
 */

$application_folder = realpath(__DIR__);

// The path to the "application" folder
if (is_dir($application_folder)) {
    define("APPPATH", $application_folder . DIRECTORY_SEPARATOR);
} else {
    exit(
        "Error: Your application folder path does not appear to be set correctly. Please open the following file and correct this: " .
            self
    );
}

/*---------------------------------------------------------------
 * Website available to controllers and templates
 *---------------------------------------------------------------
 */

global $WEBSITE;
$WEBSITE = Website::get();
if ($WEBSITE) {
    $app["website"] = $WEBSITE;
} else {
    exit("Dead: could not get website instance. Please open the following file and correct this: " . self);
}

/*--------------------------------------------------------------------
 * Templates
 *--------------------------------------------------------------------
 */

switch (ENVIRONMENT) {
    case "development":
        define("TWIG_CACHE_PATH", false);
        break;
    case "testing":
    case "production":
    default:
        define("TWIG_CACHE_PATH", APPPATH . "cache");
}

$app->register(new Silex\Provider\TwigServiceProvider(), [
    "twig.path" => [
        __DIR__,

        // angular-app
        __DIR__ . "/angular-app",

        // pages
        __DIR__ . "/Site/views/" . $WEBSITE->base . "/theme/" . $WEBSITE->theme . "/page",
        __DIR__ . "/Site/views/" . $WEBSITE->base . "/theme/" . $WEBSITE->theme,
        __DIR__ . "/Site/views/" . $WEBSITE->base . "/theme/default/page",
        __DIR__ . "/Site/views/" . $WEBSITE->base . "/theme/default",
        __DIR__ . "/Site/views/" . $WEBSITE->base . "/container",
        __DIR__ . "/Site/views/" . $WEBSITE->base,
        __DIR__ . "/Site/views/shared/page",
        __DIR__ . "/Site/views/shared/container",

        // errors
        __DIR__ . "/Site/views/" . $WEBSITE->base . "/error",
        __DIR__ . "/Site/views/shared/error",
        __DIR__ . "/Site/views",
    ],
    "twig.options" => [
        "cache" => TWIG_CACHE_PATH,
        "debug" => false,
    ],
]);

/*--------------------------------------------------------------------
 * Authentication
 *--------------------------------------------------------------------
 */

$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\SecurityServiceProvider());
$app->register(new Silex\Provider\RememberMeServiceProvider());

$app["security.firewalls"] = [
    "site" => [
        "pattern" => '^.*$',
        "anonymous" => true,
        "form" => ["login_path" => "/auth/login", "check_path" => "/app/login_check"],
        "remember_me" => ["key" => Env::requireEnv("REMEMBER_ME_SECRET")],
        "logout" => ["logout_path" => "/auth/logout", "target_url" => "/auth/login", "invalidate_session" => true],
        "users" => $app->share(function () use ($WEBSITE) {
            return new \Site\Provider\AuthUserProvider($WEBSITE);
        }),
    ],
];
$app["security.role_hierarchy"] = [
    "ROLE_system_admin" => ["ROLE_SITE_project_creator"],
    "ROLE_SITE_project_creator" => ["ROLE_user", "ROLE_ALLOWED_TO_SWITCH"],
];
$app["security.access_rules"] = [["^/app", "ROLE_user"], ["^/upload", "ROLE_user"], ["^/script", "ROLE_system_admin"]];
$app["security.encoder.digest"] = $app->share(function () {
    return new \Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder(BCRYPT_COST);
});
$app["security.authentication.success_handler.site"] = $app->share(function () use ($app) {
    return new \Site\Handler\AuthenticationSuccessHandler(
        $app["security.http_utils"],
        [
            "default_target_path" => "/app",
            "login_path" => "/auth/login",
        ],
        "site"
    );
});
$app["security.authentication.logout_handler.site"] = $app->share(function () use ($app) {
    return new \Site\Handler\LogoutSuccessHandler($app["security.http_utils"], "/", $app["session"]);
});

/*--------------------------------------------------------------------
 * Accept JSON Request Body
 *--------------------------------------------------------------------
 */

$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get("Content-Type"), "application/json")) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : []);
    }
});

/*--------------------------------------------------------------------
 * Routing
 *--------------------------------------------------------------------
 */

// secured
$app->post("/upload/{appType}/{mediaType}", "Site\Controller\Upload::receive");

$app->get("/app/{appName}/{projectId}/", "Site\Controller\App::view");
$app->get("/app/{appName}/{projectId}", "Site\Controller\App::view");
$app->get("/app/{appName}/", "Site\Controller\App::view")->value("appName", "projects");
$app->get("/app/{appName}", "Site\Controller\App::view")->value("appName", "projects");
$app->get("/redirect/project/", "Site\Controller\Redirect::view");
$app->get("/redirect/project", "Site\Controller\Redirect::view");
$app->get("/script/{folder}/{scriptName}/{runType}", "Site\Controller\Script::run");
$app->get("/script/{folder}/{scriptName}/", "Site\Controller\Script::run");
$app->get("/script/{folder}/{scriptName}", "Site\Controller\Script::run");
$app->get("/script/", "Site\Controller\Script::run");
$app->get("/script", "Site\Controller\Script::run");

//public
$app->post("/api/{apiName}", "Site\Controller\Api::service");

$app->get("/invite/{inviteToken}", "Site\Controller\Validate::processInviteAndRedirect");

$app->get("/public/{appName}/{projectId}/", "Site\Controller\App::view");
$app->get("/public/{appName}/{projectId}", "Site\Controller\App::view");
$app->get("/public/{appName}/", "Site\Controller\App::view");
$app->get("/public/{appName}", "Site\Controller\App::view");

$app->get("/validate/{validateKey}", "Site\Controller\Validate::checkAndRedirect");
$app->get("/auth/reset_password/{resetPasswordKey}", "Site\Controller\Auth::view")->value("appName", "reset_password");
$app->get("/auth/{appName}", "Site\Controller\Auth::view")->value("appName", "login");
$app->post("/auth/forgot_password", "Site\Controller\Auth::forgotPassword")->bind("auth_forgot_password");

$app->get("/oauthcallback/google", "Site\OAuth\GoogleOAuth::oauthCallback");
$app->get("/oauthcallback/facebook", "Site\OAuth\FacebookOAuth::oauthCallback");

$app->post("/oauth/jwt", "Site\OAuth\OAuthJWTToken::validateOAuthToken");

$app->get("/download/assets/{appName}/{projectSlug}/audio/{filename}", "Site\Controller\Download::assets");
$app->get("/download/assets/{appName}/{projectSlug}/{filename}", "Site\Controller\Download::assets");
$app->get("/{pageName}/", "Site\Controller\Page::view")->value("pageName", "home");
$app->get("/{pageName}", "Site\Controller\Page::view")->value("pageName", "home");

/*--------------------------------------------------------------------
 * And away we go...
 *--------------------------------------------------------------------
 */

$app->run();

/* End of file index.php */
/* Location: ./index.php */
