<?php

require_once __DIR__."/../../library/MediaConfig.php";
require_once __DIR__."/../../library/MediaDatabase.php";

require_once __DIR__."/../../MinimalRouter.php";

class GetUrl
{
    public function execute(Request &$request, Response &$response)
    {
        global $media_config;

        $random_id = $this->extract_random_id($request->Uri);

        if (empty($random_id))
        {
            $response->StatusCode = StatusCode::BAD_REQUEST;
            $response->Data = "invalid url";

            $response->disable_caching();
        }
        else
        {
            $db = new MediaDatabase($media_config["DB_HOST"], $media_config["DB_USERNAME"], $media_config["DB_PASSWORD"], $media_config["DB_DATABASE"], $media_config["DB_PORT"]);

            $url = $db->get_url_info($random_id);

            if (empty($url))
            {
                $response->StatusCode = StatusCode::BAD_REQUEST;
                $response->Data = "url not found";

                $response->disable_caching();
            }
            else
            {
                // temporary to support deletion and re-use
                $response->StatusCode = StatusCode::TEMPORARY_REDIRECT;
                $response->add_header("Location: " . $url);

                $response->enable_caching();
            }
        }
    }

    private function extract_random_id($url)
    {
        if (preg_match("@([a-z]+)@i", $url, $matches))
        {
            return $matches[1];
        }
        else
        {
            return false;
        }
    }
}

?>