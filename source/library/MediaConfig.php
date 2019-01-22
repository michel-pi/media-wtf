<?php

global $media_config;

$media_config = array();

$media_config["DB_HOST"] = "localhost";
$media_config["DB_PORT"] = 0;
$media_config["DB_USERNAME"] = "";
$media_config["DB_PASSWORD"] = "";
$media_config["DB_DATABASE"] = "";

$media_config["CF_ENABLE"] = false;
$media_config["CF_MAIL"] = "";
$media_config["CF_ZONE"] = "";
$media_config["CF_KEY"] = "";

$media_config["MEDIA_DIRECTORY"] = __DIR__."/../uploads/";
$media_config["MEDIA_HTTP"] = "https://";
$media_config["MEDIA_URL"] = "https://media.wtf/";
$media_config["MEDIA_ROBOTS"] = "index, follow";

$media_config["MEDIA_REAL_URL"] = "https://media.wtf/";

if (!file_exists($media_config["MEDIA_DIRECTORY"]))
{
    mkdir($media_config["MEDIA_DIRECTORY"]);
}

?>