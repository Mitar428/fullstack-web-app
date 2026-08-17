<?php 

    namespace Medical\Clinic\Core;
    
    use PDO;
    use PDOException;

    class Database {
        private static? Database $instance = null;
        private PDO $connection;

        private function __construct() {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );

            try {
                $this->connection = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                die('Greska pri konekciji na bazu:' . $e->getMessage());
            }
        }

        public static function getInstance(): Database {
            if(self::$instance === null) {
                self::$instance = new Database();
            }

            return self::$instance;
        }

        public function getConnection(): PDO {
            return $this->connection;
        }
    }
?>