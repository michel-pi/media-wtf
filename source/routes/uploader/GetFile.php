<?php

require_once __DIR__."/../../library/MediaConfig.php";
require_once __DIR__."/../../library/MediaDatabase.php";

require_once __DIR__."/../../MinimalRouter.php";

class GetFile
{
    public function execute(Request &$request, Response &$response)
    {
        global $media_config;

        $random_id = $this->extract_parameter($request->Uri);

        if (!empty($random_id))
        {
            $db = new MediaDatabase($media_config["DB_HOST"], $media_config["DB_USERNAME"], $media_config["DB_PASSWORD"], $media_config["DB_DATABASE"], $media_config["DB_PORT"]);

            $info = $db->get_file_info($random_id);

            if (is_array($info))
            {
                $name = $random_id . "." . $info["Extension"];
                $path = $media_config["MEDIA_DIRECTORY"] . $name;
                $mime_type = $info["MimeType"];

                $response->StatusCode = StatusCode::OKAY;
                $response->Data = file_get_contents($path);

                if (ContentType::IsBinary($mime_type))
                {
                    $response->add_header("Content-Description", "File Transfer");
                    $response->add_header("Content-Disposition", "attachment; filename=\"" . $name . "\"");
                    $response->add_header("Content-Transfer-Encoding", "binary");
                
                    $response->ContentType = $mime_type;
                }
                else if (ContentType::IsImage($mime_type)
                    || ContentType::IsVideo($mime_type)
                    || ContentType::IsAudio($mime_type)
                    || ContentType::IsJson($mime_type))
                {
                    $response->ContentType = $mime_type;
                }
                else
                {
                    $response->ContentType = ContentType::TEXT_PLAIN;
                }

                $response->add_header("Accept-Ranges", "bytes");
                $response->add_header("Content-Length", filesize($path));
                $response->add_header("Connection", "keep-alive");
            }
            else
            {
                $response->StatusCode = StatusCode::BAD_REQUEST;
                $response->Data = "file not found";
            }
        }
        else
        {
            $response->StatusCode = StatusCode::BAD_REQUEST;
            $response->Data = "invalid url";
        }

        $response->enable_caching();
    }

    private function extract_parameter($url)
    {
        if (preg_match("@([0-9]+)@i", $url, $matches))
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