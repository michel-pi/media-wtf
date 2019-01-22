<?php

require_once __DIR__."/../../MinimalRouter.php";
require_once __DIR__."/../../library/MediaConfig.php";

class Error404
{
    public function execute(Request &$request, Response &$response)
    {
        $template = new Template(__DIR__."/../../assets/html/");

        $template->load("404.html");

        $template->replace("./../", "./assets/");
        $template->assign("TITLE", "Media.wtf");

        $response->StatusCode = StatusCode::OKAY;
        $response->ContentType = ContentType::TEXT_HTML;

        $response->Data = $template->display();

        $response->enable_caching();
    }
}

?>