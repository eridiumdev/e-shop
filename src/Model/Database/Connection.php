<?php
namespace App\Model\Database;

/**
 * Singleton class for creating and holding MySQL connection
 * The purpose is to reuse existing connection
 * instead of creating new one for every query
 */
class Connection
{
    private static $username = "admin";
    private static $password = "123";
    private static $host = 'localhost';
    private static $dbname = 'eshop';

    // Holds connection that is shared between all child classes
    // Destroyed when execution of the script ends
    private static $instance;

    // Copy of a connection for every child
    // Destroyed when child is destroyed
    protected $db;

    /**
     * All child classes use this method for instantiation
     * Creates a new connection or reuses existing one
     */
    public function __construct()
    {
        if (!Connection::getInstance()) {
            Connection::createConnection();
        }
        $this->db = Connection::getInstance();
    }

    protected static function getInstance()
    {
        return self::$instance;
    }

    protected static function createConnection()
    {
        // $dsn = "mysql:host=localhost;dbname=eshop";
        // $username = 'admin';
        // $password = '123';

        self::$instance = new \PDO(
            "mysql:host=" . self::$host . ";dbname=" . self::$dbname, self::$username,
            self::$password
        );
        self::$instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function resetDatabase()
    {
        $this->db->beginTransaction();

        try {
            // Get all table names
            $sql = "SELECT table_name
                    FROM information_schema.tables
                    WHERE table_schema = 'eshop'";
            $stmt = $this->db->query($sql);

            $tables = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $tables[] = $row['table_name'];
            }

            // Delete each table contents
            foreach ($tables as $table) {
                $sql = "TRUNCATE $table";
                $stmt = $this->db->exec($sql);
            }

            // Get testing data from YML
            $dataSet = \Symfony\Component\Yaml\Yaml::parse(
                file_get_contents(ltrim(DB_DATA, '/'))
            );

            foreach ($dataSet as $data) {
                pn($data);
            }
            exit;

            // Repopulate tables

            // Commit transaction
            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to reset database", $e);
            $this->db->rollBack();
            return false;
        }
    }
}
