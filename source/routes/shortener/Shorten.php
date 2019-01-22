<?php

require_once __DIR__."/../../library/MediaConfig.php";
require_once __DIR__."/../../library/MediaDatabase.php";

require_once __DIR__."/../../MinimalRouter.php";

class Shorten
{
    public function execute(Request &$request, Response &$response)
    {
        global $media_config;

        $url = $request->get_data("url");

        if (empty($url))
        {
            $response->StatusCode = StatusCode::BAD_REQUEST;
            $response->Data = "missing url param";
        }
        else
        {
            $chosenUrl = $request->get_data("chosen_url");
            $random = $request->get_data("random");

            $random = $this->make_bool($random);

            if ($chosenUrl == false)
            {
                $chosenUrl = $media_config["MEDIA_URL"];
            }
            else if (!strstr($chosenUrl, "http"))
            {
                $chosenUrl = $media_config["MEDIA_URL"];
            }

            $chosenUrl = parse_url($chosenUrl, PHP_URL_HOST);

            if (strstr($chosenUrl, "www."))
            {
                $chosenUrl = str_replace("www.", "", $chosenUrl);
            }

            $chosenUrl = $media_config["MEDIA_HTTP"] . $chosenUrl . "/";

            $db = new MediaDatabase($media_config["DB_HOST"], $media_config["DB_USERNAME"], $media_config["DB_PASSWORD"], $media_config["DB_DATABASE"], $media_config["DB_PORT"]);

            $url_index = $db->generate_random_url_index();
            $random_id = $db->insert_url_at($url_index, $url);
            $deletion_token = $db->get_url_deletion_token_for($random_id);

            $array = array(
                "Url" => $chosenUrl . $random_id,
                "DeletionUrl" => $chosenUrl . $random_id . "/" . $deletion_token
            );

            $response->StatusCode = StatusCode::OKAY;
            $response->ContentType = ContentType::APPLICATION_JSON;
            $response->Data = json_encode($array);
        }

        $response->disable_caching();
    }

    private function make_bool($input)
    {
        if (!empty($input))
        {
            return $input == "true";
        }

        return false;
    }
}

?>