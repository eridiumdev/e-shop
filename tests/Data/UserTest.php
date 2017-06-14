<?php
namespace App\Test\Data;

use App\Model\Data\User;

class UserTest extends DataTest
{
    /**
     * Data provider for User tests
     */
    public function userData()
    {

        $input = [
            'id'           => 37,
            'username'     => 'test_user123',
            'email'        => 'test@mail.com',
            'password'     => 'secret123',
            'type'         => 'user',
            'registeredAt' => '2017-04-10 10:30:00'
        ];
        $expected = [
            'id'           => 37,
            'username'     => 'test_user123',
            'email'        => 'test@mail.com',
            'password'     => 'secret123',
            'type'         => 'user',
            'registeredAt' => '2017-04-10 10:30:00'
        ];
        return [[$input, $expected]];
    }

    /**
     * @test
     * @dataProvider userData
     */
    public function getterTest($input, $expected)
    {
        $user = new User(
            $input['id'],
            $input['username'],
            $input['email'],
            $input['password'],
            $input['type'],
            $input['registeredAt']
        );

        $this->runGetter($user, $input, $expected);
    }

    /**
     * @test
     * @dataProvider userData
     */
    public function setterTest($input, $expected)
    {
        $user = new User();
        $this->runSetter($user, $input, $expected);
    }
}
