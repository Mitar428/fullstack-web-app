<?php 
    namespace Medical\Clinic\Core;

    use Monolog\Logger as MonologLogger;
    use Monolog\Handler\StreamHandler;

    class Logger {
        private static ?MonologLogger $instance = null;

        public static function getInstance(): MonologLogger {
            if(self::$instance === null) {
                self::$instance = new MonologLogger('medical_clinic');
                self::$instance->pushHandler(
                    new StreamHandler(LOG_FILE, MonologLogger::DEBUG)
                );
            }

            return self::$instance;
        }

        public static function info(string $message, array $context = []): void {
            self::getInstance()->info($message, $context);
        }

        public static function warning(string $message, array $context = []): void {
            self::getInstance()->warning($message, $context);
        }

        public static function error(string $message, array $context = []): void {
            self::getInstance()->error($message, $context);
        }

        public static function logAction(string $action, array $details = []): void {
            $userId = Session::get('user_id', 'guest');
            self::info("Akcija: {$action}", [
                'user_id' => $userId,
                'action' => $action,
                'details' => $details,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ]);
        }
    }
?>