<?php

require_once __DIR__."/../../library/MediaConfig.php";
require_once __DIR__."/../../library/FileUpload.php";
require_once __DIR__."/../../library/MediaDatabase.php";

require_once __DIR__."/../../MinimalRouter.php";

class FormUpload
{
    public function execute(Request &$request, Response &$response)
    {
        global $media_config;

        if (!FileUpload::IsFileUpload())
        {
            $response->StatusCode = StatusCode::BAD_REQUEST;
            $response->Data = "no file uploaded";
        }
        else
        {
            $chosenUrl = parse_url($media_config["MEDIA_URL"], PHP_URL_HOST);

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
            $urlExtension = $url . "." . $extension;

            if (ContentType::IsGif($mime_type) || ContentType::IsVideo($mime_type))
            {
                $url = $urlExtension;
            }

            $formData = $this->generate_form_group("inputLink", "Link", $url);
            $formData .= $this->generate_form_group("inputDirectLink", "Direct Link", $urlExtension);

            if (ContentType::IsAudio($mime_type))
            {
                $formData .= $this->generate_form_group("inputAudioBB", "Audio BB-Code", '[MEDIA=audio]' . $urlExtension . '[/MEDIA]');
                $formData .= $this->generate_form_group("inputHtml", "Html", '<audio controls><source src="' . $urlExtension . '" type="' . $mime_type . '">Your browser does not support the audio element.</audio>');
            }
            else if (ContentType::IsImage($mime_type))
            {
                $formData .= $this->generate_form_group("inputImageBB", "Image BB-Code", "[IMG]" . $urlExtension . "[/IMG]");
                $formData .= $this->generate_form_group("inputLinkedImageBB", "Linked Image BB-Code", "[URL=" . $url . "][IMG]" . $urlExtension . "[/IMG][/URL]");
                $formData .= $this->generate_form_group("inputHtml", "Html", '<a href="' . $url . '"><img src="' . $extension . '" alt=""></a>');
            }
            else if (ContentType::IsVideo($mime_type))
            {
                $formData .= $this->generate_form_group("inputVideoBB", "Video BB-Code", '[MEDIA=video]' . $urlExtension . '[/MEDIA]');
                $formData .= $this->generate_form_group("inputHtml", "Html", '<video controls><source src="' . $urlExtension . '" type="' . $mime_type . '">Your browser does not support the video element.</video>');
            }
            else
            {
                $formData .= $this->generate_form_group("inputDownloadLink", "Html Download Link", '<a href="' . $urlExtension . '" download>Download</a>');
            }
            
            $formData .= $this->generate_form_group("inputDeletionLink", "Deletion Link", $url . "/" . $deletion_token);
        
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