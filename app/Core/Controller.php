<?php 

    namespace Medical\Clinic\Core;

    use Twig\Environment;
    use Twig\Loader\FilesystemLoader;

    class Controller {
   
        protected Environment $twig;

        public function __construct() {
           
            $loader = new FilesystemLoader(TWIG_VIEWS);

     
            $this->twig = new Environment($loader, [
                'cache' => TWIG_CACHE, 
                'debug' => true, 
                'auto_reload' => true, 
            ]);

        
            $this->twig->addGlobal('app_name', APP_NAME);
            $this->twig->addGlobal('app_url', APP_URL);
            $this->twig->addGlobal('session', $_SESSION);

            $this->twig->addGlobal('flash', [
                'success' => Session::getFlash('success'),
                'error' => Session::getFlash('error'),
                'warning' => Session::getFlash('warning'),
                'info' => Session::getFlash('info'),
            ]);
        }

       
        protected function render(string $template, array $data = []): void {
            echo $this->twig->render($template, $data);
        }

        protected function redirect(string $url): void {
            header('Location: ' . APP_URL . $url);  
            exit; 
        }

 
        protected function forbidden(): void {
            http_response_code(403);
            $this->twig->render('errors/403.html.twig'); 
            exit;
        }

      
        protected function notFound(): void {
            http_response_code(404);
            $this->twig->render('errors/404.html.twig'); 
            exit;
        }

        protected function requireAuth(): void {
            if (!Session::has('user_id')) {
                Session::setFlash('error', 'Morate biti prijavljeni.');
                $this->redirect('/login');
            }
        }

       
        protected function requireRole(string ...$roles): void {
            $this->requireAuth(); 
            $userRole = Session::get('role');

            if(!in_array($userRole, $roles)) {
                $this->forbidden(); 
            }
        }

    }
?>