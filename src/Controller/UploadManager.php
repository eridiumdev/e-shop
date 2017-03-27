<?php
namespace App\Controller;

class UploadManager extends AdminController
{
    public function showUploadsPage()
    {
        if ($handle = opendir('/img')) {

            while (false !== ($entry = readdir($handle))) {
                print_r($entry);
            }

            closedir($handle);
        }
    }
}
