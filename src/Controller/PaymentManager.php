<?php
namespace App\Controller;

use App\Model\Database\Creator;
use App\Model\Database\Deleter;
use App\Model\Database\Reader;
use App\Model\Database\Updater;

/**
 * Admin controller for managing orders
 */
class PaymentManager extends AdminController
{
    public function showPaymentsListPage()
    {
        try {
            $dbReader = new Reader();
            $payments = $dbReader->getAllPayments();
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to get all payments', $e);
            $this->flash('danger', 'Database operation failed');
            $this->showDashboardPage();
        }

        $this->addTwigVar('payments', $payments);
        $this->setTemplate('admin/payments.twig');
        $this->render();
    }
}
