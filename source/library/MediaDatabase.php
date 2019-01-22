<?php

class MediaDatabase
{
    private $sql;
    private $database;

    public function __construct($host, $username, $password, $database, $port)
    {
        $this->sql = new mysqli($host, $username, $password, $database, $port);
        $this->database = $database;
    }

    function __destruct()
    {
        $this->sql->close();
    }

    public function delete_file($random_id, $token)
    {
        $query = $this->sql->query("SELECT `id`,`deletion_token` FROM `media_file_delete` WHERE `random_id` = " . $random_id);

        if (empty($query) || $query->num_rows == 0)
        {
            return false;
        }

        $row = $query->fetch_row();

        $deletion_id = $row[0];
        $deletion_token = $row[1];

        $query->close();

        if ($deletion_token != $token)
        {
            return false;
        }

        $query = $this->sql->query("SELECT `index_id`,`file_id` FROM `media_file_index` WHERE `random_id` = " . $random_id);

        $row = $query->fetch_row();

        $index_id = $row[0];
        $file_id = $row[1];

        $query->close();

        $query = $this->sql->query("SELECT `extension` FROM `media_files` WHERE `id` = " . $file_id);

        $extension = $query->fetch_row()[0];

        $query->close();

        $this->sql->query("DELETE FROM `media_files` WHERE `media_files`.`id` = " . $file_id);
        $this->sql->query("DELETE FROM `media_file_index` WHERE `media_file_index`.`index_id` = " . $index_id);
        $this->sql->query("DELETE FROM `media_file_delete` WHERE `media_file_delete`.`id` = " . $deletion_id);

        return $random_id . "." . $extension;
    }

    public function get_file_info($random_id)
    {
        $query = $this->sql->query("SELECT `file_id` FROM `media_file_index` WHERE `random_id` = " . $random_id);

        if (empty($query) || $query->num_rows == 0)
        {
            // no such file
            return false;
        }

        $file_id = $query->fetch_row()[0];

        $query->close();

        $query = $this->sql->query("SELECT `name`,`extension`,`mime_type` FROM `media_files` WHERE `id` = " . $file_id);

        $row = $query->fetch_row();

        $array = array(
            "Name" => $row[0],
            "Extension" => $row[1],
            "MimeType" => $row[2]
        );

        $query->close();

        return $array;
    }

    public function generate_random_file_index()
    {
        $this->sql->query("LOCK media_file_index");

        while (true)
        {
            $random = mt_rand(10000000, 99999999);

            $query = $this->sql->query("SELECT `index_id` FROM `media_file_index` WHERE `random_id` = " . $random);

            if (empty($query) || $query->num_rows == 0)
            {
                $query->close();

                $this->sql->query("INSERT INTO `media_file_index` (`index_id`, `random_id`, `file_id`) VALUES (NULL, '" . $random . "', '0')");

                $insert_id = $this->sql->insert_id;

                $this->sql->query("UNLOCK media_file_index");

                return $insert_id;
            }
            else
            {
                $query->close();
            }
        }
    }

    public function insert_file_at($index, $name, $extension, $mime_type)
    {
        $query = $this->sql->query("SELECT `random_id` FROM `media_file_index` WHERE `index_id` = " . $index);

        $random_id = $query->fetch_row()[0];

        $query->close();

        $this->sql->query("INSERT INTO `media_files` (`id`, `name`, `extension`, `mime_type`) VALUES (NULL, '" . $name . "', '" . $extension . "', '" . $mime_type . "')");

        $file_id = $this->sql->insert_id;

        $this->sql->query("UPDATE `" . $this->database . "`.`media_file_index` SET `file_id` = " . $file_id . " WHERE `index_id` = " . $index);

        return $random_id;
    }

    public function get_file_deletion_token_for($random_id)
    {
        $token = md5(rand());

        $this->sql->query("INSERT INTO `media_file_delete` (`id`, `random_id`, `deletion_token`) VALUES (NULL, '" . $random_id . "', '" . $token . "')");

        return $token;
    }

    public function generate_random_url_index()
    {
        $this->sql->query("LOCK media_url_index");

        while (true)
        {
            $random = $this->random_string();

            $query = $this->sql->query("SELECT `index_id` FROM `media_url_index` WHERE `random_id` = '" . $random . "'");

            if (empty($query) || $query->num_rows == 0)
            {
                $query->close();

                $this->sql->query("INSERT INTO `media_url_index` (`index_id`, `random_id`, `url_id`) VALUES ('', '" . $random . "', '0')");

                $insert_id = $this->sql->insert_id;

                $this->sql->query("UNLOCK media_url_index");

                return $insert_id;
            }
            else
            {
                $query->close();
            }
        }
    }

    public function insert_url_at($index_id, $url)
    {
        $url = $this->sql->real_escape_string($url);

        $query = $this->sql->query("SELECT `random_id` FROM `media_url_index` WHERE `index_id` = " . $index_id);

        $random_id = $query->fetch_row()[0];

        $query->close();

        $this->sql->query("INSERT INTO `media_urls` (`id`, `url`) VALUES (NULL, '" . $url . "')");

        $url_id = $this->sql->insert_id;

        $this->sql->query("UPDATE `" . $this->database . "`.`media_url_index` SET `url_id` = " . $url_id . " WHERE `index_id` = " . $index_id);

        return $random_id;
    }

    public function get_url_deletion_token_for($random_id)
    {
        $token = md5(rand());

        $this->sql->query("INSERT INTO `media_url_delete` (`id`, `random_id`, `deletion_token`) VALUES (NULL, '" . $random_id . "', '" . $token . "')");

        return $token;
    }

    public function get_url_info($random_id)
    {
        $query = $this->sql->query("SELECT `url_id` FROM `media_url_index` WHERE `random_id` = '" . $random_id . "'");

        if (empty($query) || $query->num_rows == 0)
        {
            return false;
        }

        $url_id = $query->fetch_row()[0];

        $query->close();

        $query = $this->sql->query("SELECT `url` FROM `media_urls` WHERE `id` = " . $url_id);

        if (empty($query) || $query->num_rows == 0)
        {
            return false;
        }

        $url = $query->fetch_row()[0];

        $query->close();

        return $url;
    }

    public function delete_url($random_id, $token)
    {
        $query = $this->sql->query("SELECT `id`,`deletion_token` FROM `media_url_delete` WHERE `random_id` = '" . $random_id . "'");

        if (empty($query) || $query->num_rows == 0)
        {
            return false;
        }

        $row = $query->fetch_row();

        $deletion_id = $row[0];
        $deletion_token = $row[1];

        $query->close();

        if ($deletion_token != $token)
        {
            return false;
        }

        $query = $this->sql->query("SELECT `index_id`,`url_id` FROM `media_url_index` WHERE `random_id` = '" . $random_id . "'");

        $row = $query->fetch_row();

        $index_id = $row[0];
        $url_id = $row[1];

        $query->close();

        $this->sql->query("DELETE FROM `media_urls` WHERE `media_urls`.`id` = " . $url_id);
        $this->sql->query("DELETE FROM `media_url_index` WHERE `media_url_index`.`index_id` = " . $index_id);
        $this->sql->query("DELETE FROM `media_url_delete` WHERE `media_url_delete`.`id` = " . $deletion_id);

        return true;
    }

    private function random_string()
    {
        $chars = "abcdefghijklmnopqrstuvwxyz";

        $result = "";

        for ($i = 0; $i < 8; $i++)
        {
            $result .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        return $result;
    }
}

?>