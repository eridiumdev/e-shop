<?php
namespace App\Controller;

/**
 * Template for all Manager classes
 * - admin controller classes to manage particular data,
 * e.g. Account manager, Order manager...
 */
class AdminController extends BaseController
{
    public function showDashboardPage()
    {
        $this->setTemplate('dashboard.twig');
        $this->render();
    }
}
