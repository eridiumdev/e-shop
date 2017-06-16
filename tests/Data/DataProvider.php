<?php
namespace App\Test\Data;

class DataProvider
{
    public function getUserData()
    {
        return [
            'id'           => 37,
            'username'     => 'test_user123',
            'email'        => 'test@mail.com',
            'password'     => 'secret123',
            'type'         => 'user',
            'registeredAt' => '2017-04-10 10:30:00'
        ];
    }

    public function getShippingData()
    {
        return [
            'name'      => 'Test McTesterino',
            'phone'     => '123-123-123',
            'address'   => 'China, Shanghai Blightstone Rd. 300'
        ];
    }

    public function getOrdersData()
    {
        return [
            [
                'id'       => 14,
                'date'      => '2017-05-10 10:30:00',
                'status'    => 'finished'
            ],
            [
                'id'       => 15,
                'date'      => '2017-05-11 10:30:00',
                'status'    => 'paid'
            ]
        ];
    }

    public function getDeliveryData()
    {
        return [
            'id'            => 2,
            'name'          => 'Express Delivery',
            'description'   => 'Speedy delivery, under two days!',
            'price'         => 20
        ];
    }

    public function getPaymentData()
    {
        return [
            'id'            => 4,
            'name'          => 'Alipay',
            'description'   => 'Pay using your Alipay account'
        ];
    }

    public function getCategoriesData()
    {
        return [
            [
                'id'            => 1,
                'name'          => 'Mice',
                'description'   => 'Ergonomic mice'
            ],
            [
                'id'            => 2,
                'name'          => 'Keyboards',
                'description'   => 'Ergonomic keyboards'
            ]
        ];
    }

    public function getSpecsData()
    {
        return [
            [
                'id'            => 1,
                'name'          => 'Brand',
                'type'          => 'checkbox',
                'value'         => 'Microsoft',
                'isRequired'    => true
            ],
            [
                'id'            => 2,
                'name'          => 'Color',
                'type'          => 'checkbox',
                'value'         => 'White',
                'isRequired'    => false
            ]
        ];
    }

    public function getProductsData()
    {
        return [
            [
                'id'            => 1,
                'name'          => 'Full Size Ergonomic Backlit Hub Keyboard',
                'description'   => 'The PERI-312 is the ideal Ergonomic Backlit Keyboard for all scenarios. Its natural Ergonomic Full-Size design is comfortable and easy to use and it\'s split-key, 3-D design helps users maintain a natural posture.',
                'price'         => 280
            ],
            [
                'id'            => 2,
                'name'          => 'Evoluent VerticalMouse 3, Right Handed, Optical, USB',
                'description'   => 'The PERI-312 is the ideal Ergonomic Backlit Keyboard for all scenarios. Its natural Ergonomic Full-Size design is comfortable and easy to use and it\'s split-key, 3-D design helps users maintain a natural posture.',
                'price'         => 150
            ]
        ];
    }

    public function getOrderItemsData()
    {
        return [
            [
                'qty'           => 2,
                'id'            => 1,
                'name'          => 'Full Size Ergonomic Backlit Hub Keyboard',
                'description'   => 'The PERI-312 is the ideal Ergonomic Backlit Keyboard for all scenarios. Its natural Ergonomic Full-Size design is comfortable and easy to use and it\'s split-key, 3-D design helps users maintain a natural posture.',
                'price'         => 300
            ],
            [
                'qty'           => 1,
                'id'            => 2,
                'name'          => 'Evoluent VerticalMouse 3, Right Handed, Optical, USB',
                'description'   => 'The PERI-312 is the ideal Ergonomic Backlit Keyboard for all scenarios. Its natural Ergonomic Full-Size design is comfortable and easy to use and it\'s split-key, 3-D design helps users maintain a natural posture.',
                'price'         => 150
            ]
        ];
    }

    public function getPicsData()
    {
        return [
            [
                'name'  => 'Picture 1',
                'path'  => 'uploads/ms/pic1.png'
            ],
            [
                'name'  => 'Picture 2',
                'path'  => 'uploads/ms/pic2.png'
            ]
        ];
    }

    public function getDiscountData()
    {
        return [
            'amount'    => 0.8
        ];
    }

    public function getSectionData()
    {
        return [
            'id'            => 1,
            'name'          => 'On Sale',
            'description'   => 'Products that are currently on sale',
            'maxProducts'   => 10,
            'param'         => 'sale'
        ];
    }
}
