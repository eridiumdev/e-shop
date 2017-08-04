<?php
namespace App\Controller;

use App\Model\Database\Creator;
use App\Model\Database\Deleter;
use App\Model\Database\Reader;
use App\Model\Database\Updater;

/**
 * Admin controller for managing orders
 */
class SpecManager extends AdminController
{
    public function showSpecsListPage()
    {
        try {
            $dbReader = new Reader();
            $specs = $dbReader->getAllSpecs();
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to get all specs', $e);
            $this->flash('danger', 'Database operation failed');
            $this->showDashboardPage();
        }

        $this->addTwigVar('specs', $specs);
        $this->setTemplate('admin/specs.twig');
        $this->render();
    }

    public function showAddSpecPage()
    {
        $this->setTemplate('admin/specs/add-spec.twig');
        $this->render();
    }

    public function addSpec(array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Invalid input data, try again');
            Router::redirect("/admin/specs/add");
        }

        $name = $post['name'];
        $type = $post['type'];
        $isRequired = $post['isRequired'];
        $isFilter = $post['isFilter'];
        $categories = $post['categories'];

        try {
            $dbCreator = new Creator();

            $new = $dbCreator->createSpec($name, $type, $isRequired, $isFilter);

            if (!$new) {
                $this->flash('danger', "Failed to create new spec '$name'");
                Router::redirect('/admin/specs/add');
            }

            foreach ($categories as $catId) {
                if (!$dbCreator->addCategoryToSpec($new->getId(), $catId)) {
                    $this->flash(
                        'warning',
                        "Failed to add category '$catId' to the spec"
                    );
                }
            }

            $this->flash('success', "'$name' was created successfully");
            Router::redirect('/admin/specs');

        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to create spec '$name'", $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect("/admin/specs/add");
        }
    }

    public function showViewSpecPage(int $specId)
    {
        try {
            $dbReader = new Reader();
            $spec = $dbReader->getFullSpecById($specId);
        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to get spec by id '$specId'", $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect("/admin/specs");
        }

        if (!$spec) {
            $this->flash('danger', "Spec '$specId' was not found");
            Router::redirect("/admin/specs");
        }

        $this->addTwigVar('spec', $spec);
        $this->setTemplate('admin/specs/view-spec.twig');
        $this->render();
    }

    public function updateSpec(int $specId, array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Invalid input data, try again');
            Router::redirect("/admin/specs/view/$specId");
        }

        $specId = $post['id'];
        $name = $post['name'];
        $type = $post['type'];
        $isRequired = $post['isRequired'];
        $isFilter = $post['isFilter'];
        $categories = $post['categories'];

        try {
            $dbUpdater = new Updater();
            $dbCreator = new Creator();     // for replacing
            $dbDeleter = new Deleter();     // categories

            // replace values, overwriting old ones, regardless same or not
            $dbUpdater->updateSpec($specId, $name, $type, $isRequired, $isFilter);

            if ($dbDeleter->deleteSpecCategories($specId)) {
                foreach ($categories as $catId) {
                    if (!$dbCreator->addCategoryToSpec($specId, $catId)) {
                        $this->flash(
                            'warning',
                            "Failed to add category '$catId' to the spec"
                        );
                    }
                }
            } else {
                $this->flash('warning', "Failed to update spec categories");
            }

            $this->flash('success', "'$name' was updated successfully");
            Router::redirect('/admin/specs');

        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to update spec '$specId'", $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect("/admin/specs/view/$specId");
        }
    }

    public function deleteSpec(int $specId)
    {
        try {
            $dbDeleter = new Deleter();
            $deleted = $dbDeleter->deleteSpec($specId);
            if (!$deleted) {
                $this->flash('danger', "Could not delete spec '$specId'");
                Router::redirect('/admin/specs');
            }

            $name = $deleted->getName();

            $this->flash('success', "Spec '$name' was deleted successfully");
            Router::redirect('/admin/specs');

        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to delete spec '$specId'", $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect('/admin/specs');
        }
    }
}
