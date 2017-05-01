<?php
namespace VVC\Test;

require_once __DIR__ . '/../web/config.php';

use VVC\Model\Database\Creator;
use VVC\Model\Database\Deleter;
use VVC\Model\Database\Reader;
// use VVC\Model\Database\Updater;

use VVC\Model\Data\User;

class ReaderTest extends DBTest
{
    /**
     * @test
     * depends ReaderTest->findUserByUsername_Test
     */
    public function createUser_Test()
    {
        // Expect User
        $password = password_hash('123', PASSWORD_DEFAULT);

        $input[] = [
            'new_user',
            $password,
            2,
            date("Y-m-d H:i:s")
        ];
        $input[] = [
            'new_admin',
            $password,
            1,
            date("Y-m-d H:i:s")
        ];

        $expected[] = new User(
            -1,
            'new_user',
            $password,
            2,
            date("Y-m-d H:i:s")
        );
        $expected[] = new User(
            -1,
            'new_admin',
            $password,
            1,
            date("Y-m-d H:i:s")
        );

        $dbCreator = new Creator($this->db);

        for ($i = 0; $i < count($input); $i++) {
            $user = $dbCreator->createUser(
                $input[$i][0],
                $input[$i][1],
                $input[$i][2],
                $input[$i][3]
            );
            $expected[$i]->setId($user->getId());
            $this->assertEquals($expected[$i], $user);
        }
    }

}
