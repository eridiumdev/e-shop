<?php
namespace App\Controller;

use App\Model\Database\Creator;
use App\Model\Database\Deleter;
use App\Model\Database\Reader;
use App\Model\Database\Updater;

/**
 * Admin controller for managing orders
 */
class OrderManager extends AdminController
{
    public function showOrdersListPage()
    {
        try {
            $dbReader = new Reader();
            $orders = $dbReader->getAllOrders();
            krsort($orders);
            $statuses = $dbReader->getAllStatuses();
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to get all orders', $e);
            $this->flash('danger', 'Database operation failed');
            $this->showDashboardPage();
        }

        $this->addTwigVar('orders', $orders);
        $this->addTwigVar('statuses', $statuses);
        $this->setTemplate('admin/orders.twig');
        $this->render();
    }

    public function changeStatus(array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Some data is invalid');
            Router::redirect("/admin/orders");
        }

        $orderId = $post['orderId'];
        $statusId = $post['status'][$orderId];

        try {
            $dbUpdater = new Updater();
            $dbUpdater->updateOrderStatus($orderId, $statusId);

            $this->flash('success', "Order '$orderId' status was changed successfully");
            Router::redirect('/admin/orders');

        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to change order '$orderId' status", $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect("/admin/orders");
        }
    }
}
