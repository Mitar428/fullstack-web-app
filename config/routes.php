<?php
    use Medical\Clinic\Core\Router;

    $router = new Router();




   

    $router->get('/', 'HomeController', 'index');
    $router->get('/o-nama', 'HomeController', 'about');
    $router->get('/lekari', 'HomeController', 'doctors');
    $router->get('/kontakt', 'HomeController', 'contact');




  

    $router->get('/login', 'AuthController', 'showLogin');
    $router->post('/login', 'AuthController', 'login');
    $router->get('/logout', 'AuthController', 'logout');
    $router->get('/register', 'AuthController', 'showRegister');
    $router->post('/register', 'AuthController', 'register');





    $router->get('/admin', 'AdminController', 'dashboard');
    $router->get('/admin/korisnici', 'AdminController', 'users');
    $router->get('/admin/korisnici/novi',         'AdminController', 'userCreateForm');
    $router->post('/admin/korisnici/novi',        'AdminController', 'userStore');
    $router->get('/admin/korisnici/{id}/izmeni',  'AdminController', 'userEditForm');
    $router->post('/admin/korisnici/{id}/izmeni', 'AdminController', 'userUpdate');
    $router->post('/admin/korisnici/{id}/obrisi', 'AdminController', 'userDelete');
    $router->get('/admin/lekari', 'AdminController', 'doctors');
    $router->get('/admin/pacijenti', 'AdminController', 'patients');
    $router->get('/admin/termini', 'AdminController', 'appointments');
    $router->get('/admin/dijagnoze', 'AdminController', 'diagnoses');
    $router->get('/admin/izvestaji', 'AdminController', 'reports');
    $router->get('/admin/log', 'AdminController', 'activityLog');






    $router->get('/manager', 'ManagerController', 'dashboard');
    $router->get('/manager/termini', 'ManagerController', 'appointments');
    $router->post('/manager/termini/{id}/status', 'ManagerController', 'updateAppointmentStatus');
    $router->get('/manager/pacijenti', 'ManagerController', 'patients');
    $router->get('/manager/pacijenti/{id}', 'ManagerController', 'patientDetail');
    $router->get('/manager/pacijenti/{id}/karton/novi', 'ManagerController', 'addRecordForm');
    $router->post('/manager/pacijenti/{id}/karton/novi', 'ManagerController', 'addRecord');
    $router->get('/manager/poruke', 'ManagerController', 'messages');
    $router->post('/manager/poruke/reply', 'ManagerController', 'reply');





    $router->get('/pacijent', 'PatientController', 'dashboard');
    $router->get('/pacijent/termini', 'PatientController', 'appointments');
    $router->get('/pacijent/termini/novi', 'PatientController', 'newAppointment');
    $router->post('/pacijent/termini/novi', 'PatientController', 'createAppointment');
    $router->get('/pacijent/kartoni', 'PatientController', 'records');
    $router->get('/pacijent/poruke', 'PatientController', 'messages');
    $router->post('/pacijent/poruke/novi', 'PatientController', 'sendMessage');

    return $router;
?>
