<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Fux\Database\DB;
use Fux\Http\Request;
use Fux\View\FuxViewComposerManager;




$__FUX_SERVICE_ARE_BOOTSTRAPPED = false;
function bootstrapServiceProviders()
{
    global $__FUX_SERVICE_ARE_BOOTSTRAPPED;
    $files = array_merge(rglob(PROJECT_ROOT_DIR . "/app/Packages/*/Services/*.php"), rglob(PROJECT_ROOT_DIR . "/services/*.php"));
    foreach ($files as $fileName) {
        include_once $fileName;
    }
    if (!$__FUX_SERVICE_ARE_BOOTSTRAPPED) {
        $classes = get_declared_classes();
        foreach ($classes as $className) {
            if (strpos($className, "Service")) {
                $implementations = class_implements($className);
                if (isset($implementations['IServiceProvider'])) {
                    $className::bootstrap();
                }
            }
        }
        $__FUX_SERVICE_ARE_BOOTSTRAPPED = true;
    }
}


$__FUX_SERVICE_ARE_DISPOSED = false;
function disposeServiceProviders()
{
    global $__FUX_SERVICE_ARE_DISPOSED, $__FUX_SERVICE_ARE_BOOTSTRAPPED;
    if (!$__FUX_SERVICE_ARE_DISPOSED && $__FUX_SERVICE_ARE_BOOTSTRAPPED) {
        $classes = get_declared_classes();
        foreach ($classes as $className) {
            if (strpos($className, "Service")) {
                $implementations = class_implements($className);
                if (isset($implementations['IServiceProvider'])) {
                    $className::dispose();
                }
            }
        }
        $__FUX_SERVICE_ARE_DISPOSED = true;
    }
}





/**
 * Dinamically incldue a view file. The passed view data will be declared variables in the view context.
 *
 * @param string $viewName The path of the view file (with or without php extension, without le starting "/")
 * @param array $viewData An associative array of variables to use in the view context. Each key will be the variable
 * name associated to the value passed.
 * @param string | null $package If it is a string, it represents the name of the Package folder. In the package folder must
 * exists a "Views" folder that will be used as base dir to search for the viewName
 *
 * @return string
 */
function view($viewName, $viewData = [], $package = null)
{
    global $mysqli, $lang, $lang_id;
    //$lang = LanguageService::getCurrentLanguageCode();
    if (!preg_match("/(.*)\.php/", $viewName)) $viewName .= ".php";

    foreach ($viewData as $varName => $value) {
        ${$varName} = $value;
    }

    if ($package) {
        include(PROJECT_ROOT_DIR . "/app/Packages/$package/Views/$viewName");
    } else {
        include(PROJECT_ROOT_DIR . "/views/$viewName");
    }

    return "";
}

function viewCompose($viewAlias, $ovverideData = [], $params = [])
{
    $fuxView = FuxViewComposerManager::getView($viewAlias);
    if ($fuxView) {
        return view(
            $fuxView->getPath(),
            array_merge($fuxView->getData($params), $ovverideData),
            $fuxView->getPackage()
        );
    }
    return '';
}

/**
 * Return the absolute asset URL.
 *
 * @param string $asset The asset path relative to the "/public" directory (or package's "/Assets" directory)
 * @param string | null $package If it is a string, it represents the name of the Package folder. In the package folder must
 * exists an "Assets" folder that will be used as base dir to search for the specified asset
 */
function asset($asset, $package = null)
{
    if (substr($asset, 0, 1) === "/") {
        $asset = substr($asset, 1);
    }
    if ($package) {
        return PROJECT_HTTP_SCHEMA . "://" . DOMAIN_NAME . PROJECT_DIR . "/app/Packages/$package/Assets/" . $asset;
    }
    return PROJECT_HTTP_SCHEMA . "://" . DOMAIN_NAME . PROJECT_DIR . "/public/" . $asset;
}


$__FUX_INCLUDED_ASSETS = [];
function assetOnce($asset, $type)
{
    global $__FUX_INCLUDED_ASSETS;
    if (!isset($__FUX_INCLUDED_ASSETS[$asset . '_' . $type])) {
        $assetURL = asset($asset);
        $__FUX_INCLUDED_ASSETS[$asset . '_' . $type] = true;
        switch ($type) {
            case 'script':
                return "<script src='$assetURL'></script>";
                break;
            case 'CSS':
                return "<link rel='stylesheet' type='text/css' href='$assetURL'>";
                break;
            case 'dynamicCSS':
                return "
                    <script>
                        (function(){
                            var file = document.createElement('link');
                            file.setAttribute('rel', 'stylesheet');
                            file.setAttribute('type', 'text/css');
                            file.setAttribute('href', '$assetURL');
                            document.head.appendChild(file);
                        })();
                    </script>
                ";
                break;
        }
    }
    return '';
}

$__FUX_INCLUDED_EXTERNAL_ASSETS = [];
function assetExternalOnce($assetURL, $type)
{
    global $__FUX_INCLUDED_EXTERNAL_ASSETS;
    if (!isset($__FUX_INCLUDED_EXTERNAL_ASSETS[$assetURL . '_' . $type])) {
        switch ($type) {
            case 'script':
                return "<script src='$assetURL'></script>";
                break;
            case 'CSS':
                return "<link rel='stylesheet' type='text/css' href='$assetURL'>";
                break;
        }
        $__FUX_INCLUDED_EXTERNAL_ASSETS[$assetURL . '_' . $type] = true;
    }
    return '';
}

/**
 * @param string $css language=CSS
 */
function addCssToHead($css)
{
    $css = str_replace("\n", "", $css);
    $css = str_replace("'", "\\'", $css);
    return "
        <script>
            (function(){
                var head = document.head || document.getElementsByTagName('head')[0];
                var style = document.createElement('style');
                head.appendChild(style);
                style.appendChild(document.createTextNode('$css'));
            })();
        </script>
    ";
}

function redirect($route)
{
    header("Location: " . PROJECT_HTTP_SCHEMA . "://" . DOMAIN_NAME . PROJECT_DIR . $route);
    exit;
}

function redirect301($route)
{
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: " . PROJECT_HTTP_SCHEMA . "://" . DOMAIN_NAME . PROJECT_DIR . $route);
    exit;
}

function routeFullUrl($route)
{
    return PROJECT_HTTP_SCHEMA . "://" . DOMAIN_NAME . PROJECT_DIR . $route;
}

if (!function_exists('sanitize_post')) {
    function sanitize_post()
    {
        global $_POST_SANITIZED;
        if ($_POST_SANITIZED) return;
        array_walk_recursive($_POST, function (&$leaf) {
            if (is_string($leaf))
                $leaf = DB_ENABLE ? DB::sanitize($leaf) : filter_var($leaf, FILTER_SANITIZE_SPECIAL_CHARS);
        });
        $_POST_SANITIZED = true;
    }
}

if (!function_exists('sanitize_get')) {
    function sanitize_get()
    {
        global $_GET_SANITIZED;
        if ($_GET_SANITIZED) return;
        array_walk_recursive($_GET, function (&$leaf) {
            if (is_string($leaf))
                $leaf = DB_ENABLE ? DB::sanitize($leaf) : filter_var($leaf, FILTER_SANITIZE_SPECIAL_CHARS);
        });
        $_GET_SANITIZED = true;
    }
}
if (!function_exists('sanitize_request')) {
    function sanitize_request()
    {
        global $_REQUEST_SANITIZED;
        if ($_REQUEST_SANITIZED) return;
        array_walk_recursive($_REQUEST, function (&$leaf) {
            if (is_string($leaf))
                $leaf = DB_ENABLE ? DB::sanitize($leaf) : filter_var($leaf, FILTER_SANITIZE_SPECIAL_CHARS);
        });
        $_REQUEST_SANITIZED = true;
    }
}

function sanitize_object(&$object)
{
    array_walk_recursive($object, function (&$leaf) {
        if (is_string($leaf))
            $leaf = DB_ENABLE ? DB::sanitize($leaf) : filter_var($leaf, FILTER_SANITIZE_SPECIAL_CHARS);
    });
}



$_PACKAGES_MANIFEST_FILES = [];
function getPackageVar($var, $package)
{
    global $_PACKAGES_MANIFEST_FILES;
    $manifest = $_PACKAGES_MANIFEST_FILES[$package] ?? include PROJECT_ROOT_DIR . "/app/Packages/$package/manifest.php";
    $_PACKAGES_MANIFEST_FILES[$package] = $manifest;
    return $manifest[$var] ?? null;
}




function sanitize_html($html)
{
    $ALLOWED_HTML_TAGS = ['p', 'b', 'u', 'br', 'i', 'font', 'span'];
    return strip_tags(html_entity_decode($html), "<" . implode('><', $ALLOWED_HTML_TAGS) . ">");
}


