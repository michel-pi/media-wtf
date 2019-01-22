<?php

class FileUpload
{
    public static function IsFileUpload()
    {
        return isset($_FILES) && isset($_FILES['file']) && $_FILES['file']['size'] > 0;
    }

    public static function GetMimeType()
    {
        return mime_content_type($_FILES['file']['tmp_name']);
    }

    public static function GetName()
    {
        return pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
    }

    public static function GetExtension()
    {
        return strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
    }

    public static function Move($directory, $name)
    {
        move_uploaded_file($_FILES['file']['tmp_name'], self::join_paths($directory, $name));
    }

    private static function join_paths()
    {
        $paths = array();
    
        foreach (func_get_args() as $arg) {
            if ($arg !== '') { $paths[] = $arg; }
        }
    
        return preg_replace('#/+#','/',join('/', $paths));
    }
}

?>