<?php
namespace Medical\Clinic\Controllers;

use Medical\Clinic\Core\Controller;
use Medical\Clinic\Core\Session;
use Medical\Clinic\Core\Logger;
use Medical\Clinic\Models\User;
use Medical\Clinic\Models\Doctor;
use Medical\Clinic\Models\Patient;
use Medical\Clinic\Models\Appointment;
use Medical\Clinic\Models\Diagnosis;
use Medical\Clinic\Models\ActivityLog;


class AdminController extends Controller
{
    private User $userModel;
    private Doctor $doctorModel;
    private Patient $patientModel;
    private Appointment $appointmentModel;
    private diagnosis $diagnosisModel;
    private activityLog $activityLogModel;


    public function __construct()
    {
        parent::__construct();

        $this->requireRole('admin');
        $this->userModel=new User();
        $this->doctorModel=new Doctor();
        $this->patientModel=new Patient();
        $this->appointmentModel=new Appointment();
        $this->diagnosisModel=new Diagnosis();
        $this->activityLogModel=new ActivityLog();
    }

    public function dashboard(): void
    {
        $stats = [
            'korisnici' => $this->userModel->count(),
        ];

        $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
        ]);
    }

    public function users(): void
    {
        $korisnici = $this->userModel->findAllWithRoles();

        $this->render('admin/users.html.twig', [
            'korisnici' => $korisnici,
        ]);
    }
      public function userCreateForm(): void
    {
        $this->render('admin/user_form.html.twig', [
            'user' => null, 
        ]);
    }

    public function userStore(): void
    {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = trim($_POST['password'] ?? '');
        $roleId    = (int)($_POST['role_id'] ?? 3);

        if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
            Session::setFlash('error', 'Sva polja su obavezna.');
            $this->redirect('/admin/korisnici/novi');
            return;
        }

        if ($this->userModel->findByEmail($email)) {
            Session::setFlash('error', 'Korisnik sa tim emailom već postoji.');
            $this->redirect('/admin/korisnici/novi');
            return;
        }

        $newId = $this->userModel->createUser([
            'role_id'     => $roleId,
            'first_name'  => $firstName,
            'last_name'   => $lastName,
            'email'       => $email,
            'password'    => $password,
        ]);

        Logger::logAction('admin.user.create', [
            'new_user_id' => $newId,
            'email'       => $email
        ]);

        Session::setFlash('success', 'Korisnik je uspešno kreiran.');
        $this->redirect('/admin/korisnici');
    }

    public function userEditForm(int $id): void
    {
        $user = $this->userModel->findById($id);

        if (!$user) {
            $this->notFound();
            return;
        }

        $this->render('admin/user_form.html.twig', [
            'user' => $user,
        ]);
    }

    public function userUpdate(int $id): void
    {
        $user = $this->userModel->findById($id);

        if (!$user) {
            $this->notFound();
            return;
        }

        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name'  => trim($_POST['last_name'] ?? ''),
            'email'      => trim($_POST['email'] ?? ''),
            'role_id'    => (int)($_POST['role_id'] ?? $user['role_id']),
        ];

        if (!empty($_POST['password'])) {
            $data['password_hash'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
        }

        $this->userModel->update($id, $data);

        Logger::logAction('admin.user.update', [
            'user_id' => $id
        ]);

        Session::setFlash('success', 'Korisnik je uspešno izmenjen.');
        $this->redirect('/admin/korisnici');
    }

    public function userDelete(int $id): void
    {
        $user = $this->userModel->findById($id);

        if (!$user) {
            $this->notFound();
            return;
        }

        if ($id === (int) Session::get('user_id')) {
            Session::setFlash('error', 'Ne možete obrisati sami sebe.');
            $this->redirect('/admin/korisnici');
            return;
        }

        $this->userModel->delete($id);

         Logger::logAction('admin.users.delete', [
            'user_id' => $id,
            'email'   => $user['email']
        ]);

        Session::setFlash('success', 'Korisnik je uspešno obrisan.');
        $this->redirect('/admin/korisnici');

        
    }

    public function doctors():void{
        $lekari= $this->doctorModel->findAllWithDetails();
        $this->render('admin/patients.html.twig', ['pacijenti' => $lekari]);
    }
    public function appointments():void
    {
        $status=$_GET['status'] ?? '';
        $termini= $this->appointmentModel->findAllFull($status);
        $this->render('admin/appointments.html.twig',[

        'termini'=>$termini,
        'activeStatus'   => $status,
        ]);
    
    }

    public function diagnoses(): void
{
    $dijagnoze = $this->diagnosisModel->findAll('icd_code');

    $this->render('admin/diagnoses.html.twig', [
        'dijagnoze' => $dijagnoze,
    ]);


   
    }
    public function reports(): void
    {
        $stats = [
            'ukupno_termina'    => $this->appointmentModel->count(),
            'zavrseni'          => $this->appointmentModel->countByStatus('completed'),
            'otkazani'          => $this->appointmentModel->countByStatus('cancelled'),
            'pending'           => $this->appointmentModel->countByStatus('pending'),
            'confirmed'         => $this->appointmentModel->countByStatus('confirmed'),
            'no_show'           => $this->appointmentModel->countByStatus('no_show'),
            'ukupno_pacijenata' => $this->patientModel->count(),
            'ukupno_lekara'     => $this->doctorModel->count(),
        ];

        $this->render('admin/reports.html.twig', ['stats' => $stats]);
    }
        public function activityLog(): void
    {
        $logovi = $this->activityLogModel->findAllWithUsers(200);
        $this->render('admin/log.html.twig', ['logovi' => $logovi]);
    }

    public function patients(){
        $pacijenti=$this->patientModel->findAllWithDetails();

        $this->render('admin/patients.html.twig',[

         'pacijenti' => $pacijenti
        ]);

    }
    }
    
    