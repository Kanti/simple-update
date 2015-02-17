<?php

namespace Kanti;

class SimpleUpdater
{
    protected $updateAbleFile = "version.json";
    protected $server = "";
    protected $file = "";

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
            if ((time() - filemtime($this->updateAbleFile) >= 60 * 60)) {//1h
                $this->file = @file_get_contents($this->server);
                if ($this->file == file_get_contents($this->updateAbleFile)) {
                    //touch($this->updateAbleFile);
                    file_put_contents($this->updateAbleFile, $this->file);
                    return false;
                } else {
                    return true;
                }
            } else {
                return false;
            }
        } else {
            $this->file = @file_get_contents($this->server);
            return true;
        }
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
            $zip->extractTo(getcwd());
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
                    if (filetype($dir . "/" . $object) == "dir") static::rrmdir($dir . "/" . $object); else unlink($dir . "/" . $object);
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
        if (!$this->file) {
            return false;
        }
        $tmp = "tmp.zip";
        $data = json_decode($this->file);

        if (is_object($data) && property_exists($data, "download")
            && static::download($data->download, $tmp)
            && static::unzip($tmp)
        ) {
            if (property_exists($data, "delete")) {
                foreach ($data->delete as $file) {
                    static::rrmdir($file);
                }
            }
            file_put_contents($this->updateAbleFile, $this->file);
            static::rrmdir($tmp);
            return true;
        }
        static::rrmdir($tmp);
        return false;
    }
} 