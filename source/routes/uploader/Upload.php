<?php

require_once __DIR__."/../../library/MediaConfig.php";
require_once __DIR__."/../../library/FileUpload.php";
require_once __DIR__."/../../library/MediaDatabase.php";

require_once __DIR__."/../../MinimalRouter.php";

class Upload
{
    public function execute(Request &$request, Response &$response)
    {
        global $media_config;

        if (FileUpload::IsFileUpload())
        {
            $alwaysAppendExtension = $request->get_data("always_append_extension");
            $chosenUrl = $request->get_data("chosen_url");
            $random = $request->get_data("random");

            $alwaysAppendExtension = $this->make_bool($alwaysAppendExtension);
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

            $mime_type = FileUpload::GetMimeType();
            $name = FileUpload::GetName();
            $extension = FileUpload::GetExtension();

            $db = new MediaDatabase($media_config["DB_HOST"], $media_config["DB_USERNAME"], $media_config["DB_PASSWORD"], $media_config["DB_DATABASE"], $media_config["DB_PORT"]);

            $file_index = $db->generate_random_file_index();
            $random_id = $db->insert_file_at($file_index, $name, $extension, $mime_type);
            $deletion_token = $db->get_file_deletion_token_for($random_id);

            FileUpload::Move($media_config["MEDIA_DIRECTORY"], $random_id . "." . $extension);

            $url = $chosenUrl . $random_id;

            if (ContentType::IsGif($mime_type)) // append file extension for display in chats (discord)
            {
                $url .= ".gif";
            }
            else if (ContentType::IsVideo($mime_type))
            {
                $url .= "." . $extension;
            }
            else if ($alwaysAppendExtension)
            {
                $url .= "." . $extension;
            }

            $array = array(
                "Url" => $url,
                "ThumbnailUrl" => $chosenUrl . $random_id . "." . $extension,
                "DeletionUrl" => $chosenUrl . $random_id . "/" . $deletion_token
            );

            $response->StatusCode = StatusCode::OKAY;
            $response->ContentType = ContentType::APPLICATION_JSON;
            $response->Data = json_encode($array);
        }
        else
        {
            $response->StatusCode = StatusCode::BAD_REQUEST;
            $response->Data = "no file uploaded";
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