<?php

namespace Medical\Clinic\Controllers;

use Medical\Clinic\Core\Controller;

Class HomeController extends Controller{
    Public function index(): void {
        $this->render('guest/home.html.twig');
    }
    

    public function about(): void{
    $this->render('guest/about.html.twig');
    }
    public function doctors(): void{
    $this->render('guest/doctors.html.twig');
    }

    public function contact(): void{
    $this->render('guest/contact.html.twig');
    }


}
   

?>