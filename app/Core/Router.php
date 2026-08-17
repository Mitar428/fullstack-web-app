<?php 

    namespace Medical\Clinic\Core;

    class Router {
      
        private array $routes = [];

        
        public function add(string $method, string $path, string $controller, string $action) : void {
            
            
            $this->routes[] = [
                'method' => strtoupper($method),
                'path' => $path, 
                'controller' => $controller, 
                'action' => $action, 
            ];
        }


        public function get(string $path, string $controller, string $action) : void {
            $this->add('GET', $path, $controller, $action);
        }

    
        public function post(string $path, string $controller, string $action) : void {
            $this->add('POST', $path, $controller, $action);
        }

     
        public function dispatch(string $url, string $method) : void {
          
            $url = parse_url($url, PHP_URL_PATH);

            $url = str_replace('/medical_clinic/public', '', $url);

          
            $url = rtrim($url, '/') ?: '/';

           
            foreach ($this->routes as $route) {
             
                $pattern = $this->convertToRegex($route['path']);

               
                if ($route['method'] === strtoupper($method) && preg_match($pattern, $url, $matches)) {
                  
                    array_shift($matches);

                   
                    $controllerClass = 'Medical\\Clinic\\Controllers\\' . $route['controller'];

              
                    if (!class_exists($controllerClass)) {
                        $this->notFound();
                        return;
                    }

                 
                    $controller = new $controllerClass;
                    $action = $route['action']; 

             
                    if (!method_exists($controller, $action)) {
                        $this->notFound(); 
                        return;
                    }

                    call_user_func_array([$controller, $action], $matches);
                    return;
                }
            }

            $this->notFound();
        }

        private function convertToRegex(string $path) : string {
         
            $pattern = preg_replace('/\{([a-zA-Z]+)\}/', '([^/]+)', $path);

            return '#^' . $pattern . '$#';
        }


        private function notFound() : void {
            http_response_code(404);
            echo '404';
        }
    }
?>