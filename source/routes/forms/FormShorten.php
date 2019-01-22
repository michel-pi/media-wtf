<?php

require_once __DIR__."/../../library/MediaConfig.php";
require_once __DIR__."/../../library/MediaDatabase.php";

require_once __DIR__."/../../MinimalRouter.php";

class FormShorten
{
    public function execute(Request &$request, Response &$response)
    {
        global $media_config;

        $url = $request->get_data("url");

        if (empty($url))
        {
            $response->StatusCode = StatusCode::BAD_REQUEST;
            $response->Data = "missing url parameter";
        }
        else
        {
            $chosenUrl = parse_url($media_config["MEDIA_URL"], PHP_URL_HOST);

            if (strstr($chosenUrl, "www."))
            {
                $chosenUrl = str_replace("www.", "", $chosenUrl);
            }

            $chosenUrl = $media_config["MEDIA_HTTP"] . $chosenUrl . "/";

            $db = new MediaDatabase($media_config["DB_HOST"], $media_config["DB_USERNAME"], $media_config["DB_PASSWORD"], $media_config["DB_DATABASE"], $media_config["DB_PORT"]);

            $url_index = $db->generate_random_url_index();
            $random_id = $db->insert_url_at($url_index, $url);
            $deletion_token = $db->get_url_deletion_token_for($random_id);

            $resultUrl = $chosenUrl . $random_id;
            $deletionUrl = $chosenUrl . $random_id . "/" . $deletion_token;

            $formData = $this->generate_form_group("inputLink", "Link", $resultUrl);
            $formData .= $this->generate_form_group("inputDeletionLink", "Deletion Link", $deletionUrl);
        
            $template = new Template(__DIR__."/../../assets/html/");

            $template->load("result.html");
    
            $template->replace("./../", "./assets/");
            $template->assign("TITLE", "Media.wtf");

            $template->assign("FORM_DATA", $formData);

            $response->StatusCode = StatusCode::OKAY;
            $response->ContentType = ContentType::TEXT_HTML;

            $response->Data = $template->display();
        }

        $response->disable_caching();
    }

    private function generate_form_group($id, $key, $value)
    {
        $value = htmlentities($value);
        return '<div class="form-group"><label>' . $key . '</label><input id="' . $id . '" onclick="clipboard(this);showTooltip(this)" onblur="hideTooltip(this)" data-toggle="tooltip" data-trigger="manual" title="Copied" class="form-control" value="' . $value . '" readonly></div>';
    }
}

?>