<?php

require_once __DIR__."/../../MinimalRouter.php";
require_once __DIR__."/../../library/MediaConfig.php";

class Config
{
    public function execute(Request &$request, Response &$response)
    {
        global $media_config;

        $template = new Template(__DIR__."/../../assets/html/");

        $template->load("config.html");

        $template->replace("./../", "./assets/");
        $template->assign("TITLE", "Media.wtf");
        $template->assign("ROBOTS", $media_config["MEDIA_ROBOTS"]);

        $template->assign("UPLOADER_CONFIG", $this->get_uploader_config());
        $template->assign("SHORTENER_CONFIG", $this->get_shortener_config());

        $response->StatusCode = StatusCode::OKAY;
        $response->ContentType = ContentType::TEXT_HTML;

        $response->Data = $template->display();

        $response->enable_caching();
    }

    private function get_uploader_config()
    {
        $text = file_get_contents(__DIR__."/../../assets/downloads/media.wtf-uploader.sxcu");

        preg_match_all("@\r?\n@", $text, $array);

        $rows = count($array[0]) + 1;

        return '<textarea id="textUploader" class="form-control" onclick="clipboard(this);showTooltip(this)" onblur="hideTooltip(this)" data-toggle="tooltip" data-trigger="manual" title="Copied" rows="' . $rows . '" readonly>' . $text . '</textarea>';
    }

    private function get_shortener_config()
    {
        $text = file_get_contents(__DIR__."/../../assets/downloads/media.wtf-shortener.sxcu");

        preg_match_all("@\r?\n@", $text, $array);

        $rows = count($array[0]) + 1;

        return '<textarea id="textUploader" class="form-control" onclick="clipboard(this);showTooltip(this)" onblur="hideTooltip(this)" data-toggle="tooltip" data-trigger="manual" title="Copied" rows="' . $rows . '" readonly>' . $text . '</textarea>';
    }
}

?>