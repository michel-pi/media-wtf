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
        $token = $this->extract_token($request->Uri);

        if (empty($random_id) || empty($token))
        {
            $response->StatusCode = StatusCode::BAD_REQUEST;
            $response->Data = "invalid url";
        }
        else
        {
            $db = new MediaDatabase($media_config["DB_HOST"], $media_config["DB_USERNAME"], $media_config["DB_PASSWORD"], $media_config["DB_DATABASE"], $media_config["DB_PORT"]);

            $filename = $db->delete_file($random_id, $token);

            if (empty($filename))
            {
                $response->StatusCode = StatusCode::BAD_REQUEST;
                $response->Data = "invalid file id or deletion token";
            }
            else
            {
                unlink($media_config["MEDIA_DIRECTORY"] . $filename);

                Cloudflare::purge_cache(array(
                    $media_config["MEDIA_REAL_URL"] . $random_id,
                    $media_config["MEDIA_REAL_URL"] . $random_id . "." . pathinfo($filename, PATHINFO_EXTENSION)
                ));

                $response->StatusCode = StatusCode::OKAY;
                $response->Data = "Success!";
            }
        }

        $response->disable_caching();
    }

    private function extract_random_id($url)
    {
        if (preg_match("@([0-9]{8})@i", $url, $matches))
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