<?php

require_once __DIR__."/../../MinimalRouter.php";
require_once __DIR__."/../../library/MediaConfig.php";

class Terms
{
    public function execute(Request &$request, Response &$response)
    {
        global $media_config;

        $template = new Template(__DIR__."/../../assets/html/");

        $template->load("terms.html");

        $template->replace("./../", "./assets/");
        $template->assign("TITLE", "Media.wtf");
        $template->assign("ROBOTS", $media_config["MEDIA_ROBOTS"]);

        $response->StatusCode = StatusCode::OKAY;
        $response->ContentType = ContentType::TEXT_HTML;

        $response->Data = $template->display();

        $response->enable_caching();
    }
}

?>