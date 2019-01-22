<?php

require_once __DIR__."/../../library/MediaConfig.php";
require_once __DIR__."/../../library/Cloudflare.php";
require_once __DIR__."/../../library/MediaDatabase.php";

require_once __DIR__."/../../MinimalRouter.php";


class Delete
{
    public function execute(Request &$request, Response &$response)
    {
        global $media_config;

        $random_id = $this->extract_random_id($request->Uri);
        $deletion_token = $this->extract_token($request->Uri);

        if (empty($random_id) || empty($deletion_token))
        {
            $response->StatusCode = StatusCode::BAD_REQUEST;
            $response->Data = "missing parameter";
        }
        else
        {
            $db = new MediaDatabase($media_config["DB_HOST"], $media_config["DB_USERNAME"], $media_config["DB_PASSWORD"], $media_config["DB_DATABASE"], $media_config["DB_PORT"]);

            if ($db->delete_url($random_id, $deletion_token))
            {
                Cloudflare::purge_cache($media_config["MEDIA_REAL_URL"] . $random_id);

                $response->StatusCode = StatusCode::OKAY;
                $response->Data = "Success!";
            }
            else
            {
                $response->StatusCode = StatusCode::OKAY;
                $response->Data = "invalid deletion token";
            }
        }

        $response->disable_caching();
    }

    private function extract_random_id($url)
    {
        if (preg_match("@([a-z]{8})@i", $url, $matches))
        {
            return $matches[1];
        }
        else
        {
            return false;
        }
    }

    private function extract_token($url)
    {
        if (preg_match("@([0-9a-z]{32})@i", $url, $matches))
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