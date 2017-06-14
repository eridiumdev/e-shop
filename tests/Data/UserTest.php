<?php
namespace App\Test\Data;

use App\Model\Data\User;
use App\Model\Data\ShippingDetails;


class DataProvider
{
    public function getUser()
    {
        $item = new User(
            37,                     // id
            'test_user123',         // username
            'test@mail.com',        // email
            'secret123',            // password
            'user',                 // type
            '2017-04-10 10:30:00'   // registeredAt
        );

        $item->setShipping($this->getShipping());
        $item->setOrders($this->getOrders());

        return $item;
    }

    public function getShipping()
    {
        $item = new ShippingDetails(
            'Test McTesterino',                     // name
            '1234-1234-123',                        // phone
            'China, Shanghai, Blightstone road 300' // address
        );

        return $item;
    }

    public function getOrders()
    {
        $item = [];

        return $item;
    }
}
