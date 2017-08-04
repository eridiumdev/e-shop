<?php
namespace App\Controller;

use App\Model\Database\Creator;
use App\Model\Database\Deleter;
use App\Model\Database\Reader;
use App\Model\Database\Updater;

/**
 * Admin controller for managing sections
 */
class SectionManager extends AdminController
{
    public function showSectionsListPage()
    {
        try {
            $dbReader = new Reader();
            $sections = $dbReader->getAllSections();
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to get all sections', $e);
            $this->flash('danger', 'Database operation failed');
            $this->showDashboardPage();
        }

        $this->addTwigVar('sections', $sections);
        $this->setTemplate('admin/sections.twig');
        $this->render();
    }

    public function showViewSectionPage(int $sectId)
    {
        try {
            $dbReader = new Reader();
            $section = $dbReader->getSectionById($sectId);
        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to get section by id '$sectId'", $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect("/admin/sections");
        }

        if (!$section) {
            $this->flash('danger', "Section '$sectId' was not found");
            Router::redirect("/admin/sections");
        }

        $this->addTwigVar('section', $section);
        $this->setTemplate('admin/sections/view-section.twig');
        $this->render();
    }

    public function updateSection(array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Invalid URI address');
            Router::redirect("/admin/sections/view/" . $post['id']);
        }

        $sectId = $post['id'];
        $name = $post['name'];
        $uri = $post['uri'];
        $desc = $post['description'];
        $maxProducts = $post['maxProducts'];

        try {
            $dbReader = new Reader();
            $dbUpdater = new Updater();

            // need to get old data to resolve conflicting uniques and nulls
            $old = $dbReader->getSectionById($sectId);

            if (empty($old)) {
                $this->flash('danger', 'Some problem occurred, please try again');
                Router::redirect("/admin/sections/view/$sectId");
            }

            if ($old->getName() != $name) {
                // check for duplicate name
                $dup = $dbReader->getSectionByName($name);
                if (!empty($dup)) {
                    $this->flash('danger', "There is already a section named '$name'");
                    Router::redirect("/admin/sections/view/$sectId");
                }
            }

            if ($old->getUri() != $uri) {
                // check for duplicate URI
                $dup = $dbReader->getSectionByUri($uri);
                if (!empty($dup)) {
                    $this->flash('danger', "There is already a section with URI '$uri'");
                    Router::redirect("/admin/sections/view/$sectId");
                }
            }

            // replace values, overwriting old ones, regardless same or not
            $dbUpdater->updateSection($sectId, $name, $uri, $desc, $maxProducts);

            $this->flash('success', "'$name' was updated successfully");
            Router::redirect('/admin/sections');

        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to update section '$sectId'", $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect("/admin/sections/view/$sectId");
        }
    }
}
