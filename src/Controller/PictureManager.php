<?php
namespace App\Controller;

use App\Model\Database\Reader;
use App\Model\Database\Updater;
use App\Model\Database\Creator;
use App\Model\Database\Deleter;

class PictureManager extends AdminController
{
    public function showPicturesPage(string $picsNum = null)
    {
        // How many pictures to display
        if ($picsNum === 'low') {
            $picsPerRow = 4;
            $picsPerPage = $picsPerRow * 2;
        } else {
            $picsNum = 'high';
            $picsPerRow = 6;
            $picsPerPage = $picsPerRow * 2;
        }

        // List of pictures from uploads folder
        $picFiles = Uploader::getFiles(PIC_DIRECTORY, ['png', 'jpg', 'gif']);

        $pics = [];
        for ($i = 0; $i < count($picFiles); $i++) {
            $pics[($i/$picsPerPage) + 1][] = $picFiles[$i];
        }

        $this->addTwigVar('pics', $pics);
        $this->addTwigVar('picsNum', $picsNum);
        $this->addTwigVar('picsPerRow', $picsPerRow);

        $this->setTemplate('admin/pictures.twig');
        $this->render();
    }

    public function deletePictures(string $dir, array $pics, string $redirect)
    {
        $good = [];
        $bad = [];

        foreach ($pics as $key => $pic) {
            if (empty($pic)) {
                unset($pics[$key]);
            }
        }

        foreach ($pics as $pic) {
            try {
                $dbDeleter = new Deleter();
                $deletedFromDb = $dbDeleter->deletePicture($dir . $pic);

                if (!$deletedFromDb) {
                    $bad['db'][] = $pic;
                    continue;
                }

                $deletedFromFs = Uploader::deleteFile($dir . $pic);
                if (!$deletedFromFs) {
                    $bad['fs'][] = $pic;
                    continue;
                }

                $good[] = $pic;

            } catch (\Exception $e) {
                Logger::log('db', 'error',
                    "Failed to delete picture $pic", $e
                );
                $bad['db'][] = $pic;
            }
        }

        $this->prepareGoodBatchResults($good, $pics, ['name']);
        $this->prepareBadBatchResults($bad, $pics, ['name']);

        return Router::redirect($redirect);
    }

    public function addPicturesToIllness(array $post)
    {
        if (empty($post['illnessId']) ||
            empty($post['stepNum'])
        ) {
            $this->flash('fail', 'Some details are missing');
            return $this->showUploadsPage();
        }

        foreach ($post['pics'] as $key => $pic) {
            if (empty($pic)) {
                unset($post['pics'][$key]);
            }
        }

        if (empty($post['pics'])) {
            $this->flash('fail', 'No pictures selected');
            return $this->showUploadsPage();
        }

        $illnessId = $post['illnessId'];
        $stepNum = $post['stepNum'];
        $pics = $post['pics'];

        $good = [];
        $bad = [];

        foreach ($pics as $pic) {
            try {
                $dbReader = new Reader();
                $duplicate = $dbReader->getStepPictures($illnessId, $stepNum);
                if (in_array(PIC_DIRECTORY . $pic, $duplicate)) {
                    $bad['duplicate'][] = $pic;
                    continue;
                }

                $dbCreator = new Creator();
                $dbCreator->addPictureToStep(
                    $illnessId, $stepNum, PIC_DIRECTORY . $pic
                );

                $good[] = $pic;

            } catch (\Exception $e) {
                Logger::log('db', 'error', "Failed to add pic $pic to illness $illnessId, step $stepNum", $e);
                $bad['db'][] = $pic;
                continue;
            }
        }

        $this->prepareGoodBatchResults($good, $pics, ['name']);
        $this->prepareBadBatchResults($bad, $pics, ['name']);

        return Router::redirect('/admin/uploads');
    }

}
