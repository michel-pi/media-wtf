<?php

require_once __DIR__."/MediaConfig.php";

class Cloudflare
{
    public static function purge_cache($url)
    {
        global $media_config;

        if (!$media_config["CF_ENABLE"])
        {
            return true;
        }

        if (!is_array($url))
        {
            $url = array($url);
        }

        $json = array("files" => $url);

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, "https://api.cloudflare.com/client/v4/zones/" . $media_config["CF_ZONE"] . "/purge_cache");
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($json));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "X-Auth-Email: " . $media_config["CF_MAIL"],
            "X-Auth-Key: " . $media_config["CF_KEY"],
            "Content-Type: application/json"
        ));

        curl_exec($curl);

        curl_close($curl);

        return true;
    }
}

?>