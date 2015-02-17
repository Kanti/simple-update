<?php

namespace Kanti;

class SimpleUpdater
{
    protected $updateAbleFile = "version.json";
    protected $server = "";

    public function __construct($server, $updateAbleFile = null)
    {
        if ($updateAbleFile) {
            $this->updateAbleFile = $updateAbleFile;
        }
        $this->server = $server;

    }

    public function isUpdateAble()
    {
        if (file_exists($this->updateAbleFile)) {
            if (file_get_contents($this->updateAbleFile) == @file_get_contents($this->server)) {
                return false;
            }
        }
        return true;
    }

    public static function download($src, $tmp)
    {
        $file = @fopen($src, 'r');
        if ($file == false) {
            return false;
        }
        file_put_contents($tmp, $file);
        fclose($file);
        return true;
    }


    protected function unZip($file)
    {
        $zip = new \ZipArchive();
        if ($zip->open($file) === true) {
            $zip->extractTo("./");
            $zip->close();
            return true;
        } else {
            return false;
        }
    }

    // http://stackoverflow.com/questions/3338123/how-do-i-recursively-delete-a-directory-and-its-entire-contents-files-sub-dir
    public static function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") rrmdir($dir . "/" . $object); else unlink($dir . "/" . $object);
                }
            }
            reset($objects);
            rmdir($dir);
        } else if (is_file($dir)) {
            unlink($dir);
        }
    }

    public function update()
    {
        $tmp = "tmp.zip";
        $data = json_decode(file_get_contents($this->server));

        if (property_exists($data,"download")
            && static::download($data->download, $tmp)
            && static::unzip($tmp)
        ) {
            if (property_exists($data,"delete")) {
                foreach ($data->delete as $file) {
                    static::rrmdir($file);
                }
            }
            return true;
        }
        static::rrmdir($tmp);
        return false;
    }
} 