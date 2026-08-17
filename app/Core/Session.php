<?php 
    namespace Medical\Clinic\Core;

    class Session {

        public static function start(): void {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();  
                }
            }

        public static function set(string $key, mixed $value) : void {
            $_SESSION[$key] = $value;
        }

        
      
        public static function get(string $key, mixed $default = null): mixed {
            return $_SESSION[$key] ?? $default;
        }

        public static function has(string $key) : bool {
            return isset($_SESSION[$key]);
        }


        public static function remove(string $key): void {
            unset($_SESSION[$key]);
        }

        public static function destroy(): void {
            session_destroy();
            $_SESSION = [];
        }

        public static function setFlash(string $key, string $message) : void {
            $_SESSION['flash'][$key] = $message;
        }

        public static function getFlash(string $key) : ?string {
            $message = $_SESSION['flash'][$key] ?? null;
            unset($_SESSION['flash'][$key]);
            return $message;
        }
    }
?>