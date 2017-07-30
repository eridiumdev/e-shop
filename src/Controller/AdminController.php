<?php
namespace App\Controller;

use App\Model\Database\Connection;

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

    public function resetDatabase()
    {
        // $conn = new Connection();
        // $conn->resetDatabase();
    }
}
