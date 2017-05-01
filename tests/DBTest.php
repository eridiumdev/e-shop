<?php
namespace VVC\Test;

require_once __DIR__ . '/../web/config.php';

use PHPUnit\DbUnit\TestCase;
use PHPUnit\DbUnit\DataSet\ReplacementDataSet;
use PHPUnit\DbUnit\DataSet\YamlDataSet;

class DBTest extends TestCase
{
    public $data;

    public function getConnection()
    {
        $dsn = "mysql:host=localhost;dbname=vvc_test";
        $username = "vvc_admin";
        $password = "123";

        $this->db = new \PDO($dsn, $username, $password);

        return $this->createDefaultDBConnection($this->db, $dsn);
    }

    public function getDataSet()
    {
        $ds = new YamlDataSet('./dataset.yml');
        $rds = new ReplacementDataSet($ds);
        $rds->addFullReplacement('###NULL###', null);

        $this->data = \Symfony\Component\Yaml\Yaml::parse(
            file_get_contents('./dataset.yml')
        );

        return $rds;
    }
}
