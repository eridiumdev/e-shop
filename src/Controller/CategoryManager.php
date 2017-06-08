<?php
namespace App\Controller;

use App\Model\Database\Creator;
use App\Model\Database\Deleter;
use App\Model\Database\Reader;
use App\Model\Database\Updater;

/**
 * Admin controller for managing orders
 */
class CategoryManager extends AdminController
{
    public function showCatsListPage()
    {
        $this->setTemplate('admin/categories.twig');
        $this->render();
    }

    public function showAddAccountPage(
        string $email = null, string $type = null
    ) {
        $this->setTemplate('admin/inc/add-account.twig');
        $this->addTwigVar('email', $email);
        $this->addTwigVar('type', $type);
        $this->render();
    }
}