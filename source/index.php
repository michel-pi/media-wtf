<?php

display_all_errors();

require_once __DIR__."/MinimalRouter.php";

$router = new Router(__DIR__."/routes");

// uploader
$router->add_route("/delete/[0-9]{8}/[0-9a-z]{32}", "/uploader/Delete.php");
$router->add_route("/[0-9]{8}/[0-9a-z]{32}", "/uploader/Delete.php");
$router->add_route("/upload", "/uploader/Upload.php");

// shortener
$router->add_route("/delete/[a-z]{8}/[0-9a-z]{32}", "/shortener/Delete.php");
$router->add_route("/[a-z]{8}/[0-9a-z]{32}", "/shortener/Delete.php");
$router->add_route("/shorten", "/shortener/Shorten.php");

// forms
$router->add_route("/formupload", "/forms/FormUpload.php");
$router->add_route("/formshorten", "/forms/FormShorten.php");

// web pages
$router->add_route("/config", "/pages/Config.php");
$router->add_route("/donate", "/pages/Donate.php");
$router->add_route("/terms", "/pages/Terms.php");
$router->add_route("/privacy", "/pages/Privacy.php");
$router->add_route(":index", "/pages/Index.php");

// uploaded files
$router->add_route("/[0-9]{8}([./]|$)", "/uploader/GetFile.php");

// shortened urls
$router->add_route("/[a-z]{8}([./]|$)", "/shortener/GetUrl.php");

$router->add_route("/", "/pages/Error404.php"); // must be last (default)

if (!$router->handle_request())
{
    $router->throw();
}

function display_all_errors()
{
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}

?>