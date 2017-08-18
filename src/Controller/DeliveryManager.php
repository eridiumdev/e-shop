<?php
namespace App\Controller;

use App\Model\Database\Creator;
use App\Model\Database\Deleter;
use App\Model\Database\Reader;
use App\Model\Database\Updater;

/**
 * Admin controller for managing orders
 */
class DeliveryManager extends AdminController
{
    public function showDeliveriesListPage()
    {
        try {
            $dbReader = new Reader();
            $deliveries = $dbReader->getAllDeliveries();
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to get all deliveries', $e);
            $this->flash('danger', 'Database operation failed');
            $this->showDashboardPage();
        }

        $this->addTwigVar('deliveries', $deliveries);
        $this->setTemplate('admin/deliveries.twig');
        $this->render();
    }
}
