<?php
namespace App\Controller;

use App\Model\Database\Creator;
use App\Model\Database\Deleter;
use App\Model\Database\Reader;
use App\Model\Database\Updater;

/**
 * Admin controller for managing categories
 */
class CategoryManager extends AdminController
{
    public function showCategoriesListPage()
    {
        try {
            $dbReader = new Reader();
            $categories = $dbReader->getAllCategories();
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to get all categories', $e);
            $this->flash('danger', 'Database operation failed');
            $this->showDashboardPage();
        }

        $this->addTwigVar('categories', $categories);
        $this->setTemplate('admin/categories.twig');
        $this->render();
    }

    public function showAddCategoryPage()
    {
        $this->setTemplate('admin/categories/add-category.twig');
        $this->render();
    }

    public function addCategory(array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Invalid URI address');
            Router::redirect("/admin/categories/add");
        }

        $name = $post['name'];
        $desc = $post['description'];
        $uri = $post['uri'];

        try {
            $dbCreator = new Creator();
            $dbReader = new Reader();   // to check name and uri duplicates

            $dup = $dbReader->getCategoryByName($name);
            if (!empty($dup)) {
                $this->flash(
                    'danger',
                    "There is already a category named '$name'"
                );
                Router::redirect("/admin/categories/add");
            }

            $dup = $dbReader->getCategoryByUri($uri);
            if (!empty($dup)) {
                $this->flash(
                    'danger',
                    "There is already a category with URI '$uri'"
                );
                Router::redirect("/admin/categories/add");
            }

            $new = $dbCreator->createCategory($name, $desc, $uri);

            if (!$new) {
                $this->flash('danger', "Failed to create new category '$name'");
                Router::redirect('/admin/categories/add');
            }

            $this->flash('success', "'$name' was created successfully");
            Router::redirect('/admin/categories');

        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to create category '$name'", $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect("/admin/categories/add");
        }
    }

    public function showViewCategoryPage(int $catId)
    {
        try {
            $dbReader = new Reader();
            $category = $dbReader->getCategoryById($catId);
        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to get category by id '$catId'", $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect("/admin/categories");
        }

        if (!$category) {
            $this->flash('danger', "Category '$catId' was not found");
            Router::redirect("/admin/categories");
        }

        $this->addTwigVar('category', $category);
        $this->setTemplate('admin/categories/view-category.twig');
        $this->render();
    }

    public function updateCategory(int $catId, array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Invalid URI address');
            Router::redirect("/admin/categories/view/$catId");
        }

        $name = $post['name'];
        $desc = $post['description'];
        $uri = $post['uri'];

        try {
            $dbReader = new Reader();
            $dbUpdater = new Updater();

            // need to get old data to resolve conflicting uniques and nulls
            $old = $dbReader->getCategoryById($catId);

            if (empty($old)) {
                $this->flash('danger', 'Some problem occurred, please try again');
                Router::redirect("/admin/categories/view/$catId");
            }

            if ($old->getName() != $name) {
                // check for duplicate name
                $dup = $dbReader->getCategoryByName($name);
                if (!empty($dup)) {
                    $this->flash('danger', "There is already a category named '$name'");
                    Router::redirect("/admin/categories/view/$catId");
                }
            }

            if ($old->getUri() != $uri) {
                // check for duplicate URI
                $dup = $dbReader->getCategoryByUri($uri);
                if (!empty($dup)) {
                    $this->flash('danger', "There is already a category with URI '$uri'");
                    Router::redirect("/admin/categories/view/$catId");
                }
            }

            // replace values, overwriting old ones, regardless same or not
            $dbUpdater->updateCategory($catId, $name, $desc, $uri);

            $this->flash('success', "'$name' was updated successfully");
            Router::redirect('/admin/categories');

        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to update category '$catId'", $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect("/admin/categories/view/$catId");
        }
    }

    public function deleteCategory(int $catId)
    {
        try {
            $dbDeleter = new Deleter();
            $deleted = $dbDeleter->deleteCategory($catId);
            if (!$deleted) {
                $this->flash('danger', "Could not delete category '$catId'");
                Router::redirect('/admin/categories');
            }

            $name = $deleted->getName();

            $this->flash('success', "Category '$name' was deleted successfully");
            Router::redirect('/admin/categories');

        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to delete category '$catId'", $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect('/admin/categories');
        }
    }
}
