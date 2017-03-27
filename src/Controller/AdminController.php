<?php
namespace App\Controller;

/**
 * Template for all Manager classes
 * - admin controller classes to manage particular data,
 * e.g. Account manager, Drug manager...
 */
class AdminController extends BaseController
{
    public function showDashboardPage()
    {
        $this->setTemplate('admin.twig');
        $this->render();
    }
}
