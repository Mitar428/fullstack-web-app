<?php

namespace Medical\Clinic\Controllers;

use Medical\Clinic\Core\Controller;
use Medical\Clinic\Core\Session;
use Medical\Clinic\Core\Logger;
use Medical\Clinic\Models\User;

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct(); 
        $this->userModel = new User();
    }

    public function showLogin(): void
    {
        if (Session::has('user_id')) {
            $this->redirectToDashboard(Session::get('role'));
        }

        $this->render('auth/login.html.twig');
    }

   
    public function login(): void
    {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // osnovna validacija
        if (empty($email) || empty($password)) {
            Session::setFlash('error', 'Email i lozinka su obavezni.');
            $this->redirect('/login');
            return;
        }

  
        $user = $this->userModel->authenticate($email, $password);

        if (!$user) {
     
            Logger::warning('Neuspeli login pokušaj', ['email' => $email]);
            Session::setFlash('error', 'Pogrešan email ili lozinka.');
            $this->redirect('/login');
            return;
        }

        Session::set('user_id',    $user['id']);
        Session::set('user_name',  $user['first_name'] . ' ' . $user['last_name']);
        Session::set('user_email', $user['email']);
        Session::set('role',       $user['role_slug']);

     
        Logger::logAction('user.login', ['email' => $email]);

        Session::setFlash('success', 'Dobrodošli, ' . $user['first_name'] . '!');

  
        $this->redirectToDashboard($user['role_slug']);
    }

   
    public function logout(): void
    {
        Logger::logAction('user.logout');
        Session::destroy();
        Session::start();
        Session::setFlash('success', 'Uspešno ste se odjavili.');
        $this->redirect('/login');
    }

   
    public function showRegister(): void
    {
        if (Session::has('user_id')) {
            $this->redirectToDashboard(Session::get('role'));
        }

        $this->render('auth/register.html.twig');
    }

    public function register(): void
    {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name']  ?? '');
        $email     = trim($_POST['email']      ?? '');
        $password  = $_POST['password']        ?? '';
        $confirm   = $_POST['confirm_password'] ?? '';


        if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
            Session::setFlash('error', 'Sva polja su obavezna.');
            $this->redirect('/register');
            return;
        }

        if ($password !== $confirm) {
            Session::setFlash('error', 'Lozinke se ne poklapaju.');
            $this->redirect('/register');
            return;
        }

        if (strlen($password) < 8) {
            Session::setFlash('error', 'Lozinka mora imati najmanje 8 karaktera.');
            $this->redirect('/register');
            return;
        }

        if ($this->userModel->findByEmail($email)) {
            Session::setFlash('error', 'Korisnik sa tim emailom već postoji.');
            $this->redirect('/register');
            return;
        }


        $this->userModel->createUser([
            'role_id'    => 3,
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
            'password'   => $password,
        ]);

        Logger::logAction('user.register', ['email' => $email]);
        Session::setFlash('success', 'Registracija uspešna! Možete se prijaviti.');
        $this->redirect('/login');
    }

    private function redirectToDashboard(string $role): void
    {
        match($role) {
            'admin'   => $this->redirect('/admin'),
            'manager' => $this->redirect('/manager'),
            default   => $this->redirect('/pacijent'),
        };
    }
}