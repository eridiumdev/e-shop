<?php
namespace App\Controller;

const YML_DIRECTORY  = 'yml/';
const PICS_DIRECTORY = 'uploads/img';
const VIDS_DIRECTORY = 'uploads/vid';

class Uploader
{
    public static function uploadFile(string $location, string $name, $file)
    {
        return $file->move(YML_DIRECTORY, $filename);
    }

    public static function readAccountsFromYml(
        AccountManager $controller, array $files
    ) {
        if (empty($files['batch_file'])) {
            $controller->flash('danger', 'No file selected');
            Router::redirect('/admin/accounts');
        }

        $upload = $files['batch_file'];
        $ext = $upload->getClientOriginalExtension();
        $mime = $upload->getMimeType();

        if ($mime != 'text/plain' && $ext != 'yml') {
            $controller->flash('danger',
                "<b>yml</b> file expected, <b>$ext</b> file given"
            );
            Router::redirect('/admin/accounts');
        }

        try {
            $users = \Symfony\Component\Yaml\Yaml::parse(
                file_get_contents($upload)
            );
            return $users;
        } catch (\Exception $e) {
            Logger::log('auth', 'error', 'Failed to parse users YML', $e);
            $controller->flash('danger', 'Could not parse data, check the file');
            Router::redirect('/admin/accounts');
        }
    }

    public static function readIllnessesFromYml(
        IllnessManager $controller, array $files
    ) {
        if (empty($files['batch_file'])) {
            $controller->flash('danger', 'No file selected');
            Router::redirect('/admin/illnesses');
        }

        $upload = $files['batch_file'];
        $ext = $upload->getClientOriginalExtension();
        $mime = $upload->getMimeType();

        if ($mime != 'text/plain' && $ext != 'yml') {
            $controller->flash('danger',
                "<b>yml</b> file expected, <b>$ext</b> file given"
            );
            Router::redirect('/admin/illnesses');
        }

        try {
            $users = \Symfony\Component\Yaml\Yaml::parse(
                file_get_contents($upload)
            );
            return $users;
        } catch (\Exception $e) {
            Logger::log('auth', 'error', 'Failed to parse illnesses YML', $e);
            $controller->flash('danger', 'Could not parse data, check the file');
            Router::redirect('/admin/illnesses');
        }
    }

    public static function uploadPictures(
        UploadManager $controller, array $files
    ) {
        if (empty($files['pics'])) {
            $controller->flash('danger', 'No files selected');
            Router::redirect('/admin/uploads');
        }

        foreach ($files['pics'] as $tmp) {

            $ext = $tmp->getClientOriginalExtension();
            $mime = $tmp->getMimeType();

            if ($mime != 'image/png' && $ext != 'png') {
                $controller->flash('danger',
                    "<b>png</b> file expected, <b>$ext</b> file given"
                );
                Router::redirect('/admin/uploads');
            }

            $filename = $upload->getClientOriginalName();
            $path = PICS_DIRECTORY . $filename;

            if (file_exists($path)) {
                // DUP
                // Router::addCookie('file_to_overwrite', $upload);
                //
                // $controller->addTwigVar('filename', $filename);
                // $controller->showAccountListPage();
            }

            $pic = self::uploadFile(PICS_DIRECTORY, $filename, $tmp);

            if (!$pic) {
                $controller->flash('danger', 'Upload failed');
            } else {
                $controller->flash('success', 'Upload successful');
            }

            Router::redirect('/admin/uploads');
        }
    }
}
