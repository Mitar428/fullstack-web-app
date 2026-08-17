<?php

namespace Medical\Clinic\Controllers;

use Medical\Clinic\Core\Controller;
use Medical\Clinic\Core\Session;
use Medical\Clinic\Core\Logger;
use Medical\Clinic\Models\Patient;
use Medical\Clinic\Models\Appointment;
use Medical\Clinic\Models\MedicalRecord;
use Medical\Clinic\Models\Message;
use Medical\Clinic\Models\Doctor;
use Medical\Clinic\Models\User;

class PatientController extends Controller
{
    private Patient $patientModel;
    private Appointment $appointmentModel;
    private MedicalRecord $recordModel;
    private Message $messageModel;
    private Doctor $doctorModel;
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireRole('user');
        $this->patientModel     = new Patient();
        $this->appointmentModel = new Appointment();
        $this->recordModel      = new MedicalRecord();
        $this->messageModel     = new Message();
        $this->doctorModel      = new Doctor();
        $this->userModel        = new User();
    }

    private function getPatientProfile(): ?array
    {
        $userId = (int) Session::get('user_id');
        return $this->patientModel->findByUserId($userId);
    }


    public function dashboard(): void
    {
        $pacijent = $this->getPatientProfile();
        $userId   = (int) Session::get('user_id');

        $predstojeciTermini = [];
        $neprocitanePoruke  = 0;

        if ($pacijent) {
            $sviTermini = $this->appointmentModel->findByPatient($pacijent['id']);
            $predstojeciTermini = array_filter(
                $sviTermini,
                fn($t) => in_array($t['status'], ['pending', 'confirmed']) && $t['scheduled_at'] >= date('Y-m-d H:i:s')
            );
            $predstojeciTermini = array_values(array_slice($predstojeciTermini, 0, 5));
        }

        $neprocitanePoruke = $this->messageModel->countUnread($userId);

        $this->render('patient/dashboard.html.twig', [
            'pacijent'           => $pacijent,
            'predstojeciTermini' => $predstojeciTermini,
            'neprocitanePoruke'  => $neprocitanePoruke,
        ]);
    }


    public function appointments(): void
    {
        $pacijent = $this->getPatientProfile();

        if (!$pacijent) {
            $this->render('patient/appointments.html.twig', ['termini' => [], 'nema_profila' => true]);
            return;
        }

        $termini = $this->appointmentModel->findByPatient($pacijent['id']);
        $this->render('patient/appointments.html.twig', ['termini' => $termini, 'nema_profila' => false]);
    }

 
    public function newAppointment(): void
    {
        $pacijent = $this->getPatientProfile();
        if (!$pacijent) {
            Session::setFlash('error', 'Morate imati kreiran pacijentski profil da biste zakazali termin. Kontaktirajte administraciju.');
            $this->redirect('/pacijent');
            return;
        }

        $lekari = $this->doctorModel->findAllForSelect();
        $this->render('patient/new_appointment.html.twig', ['lekari' => $lekari]);
    }

 
    public function createAppointment(): void
    {
        $pacijent = $this->getPatientProfile();
        if (!$pacijent) {
            $this->redirect('/pacijent');
            return;
        }

        $doctorId    = (int) ($_POST['doctor_id']    ?? 0);
        $scheduledAt = trim($_POST['scheduled_at']   ?? '');
        $reason      = trim($_POST['reason']         ?? '');
        $duration    = (int) ($_POST['duration_min'] ?? 30);

        if (!$doctorId || empty($scheduledAt)) {
            Session::setFlash('error', 'Izaberite lekara i datum termina.');
            $this->redirect('/pacijent/termini/novi');
            return;
        }

        
        if (strtotime($scheduledAt) <= time()) {
            Session::setFlash('error', 'Termin mora biti u budućnosti.');
            $this->redirect('/pacijent/termini/novi');
            return;
        }

        $this->appointmentModel->create([
            'patient_id'   => $pacijent['id'],
            'doctor_id'    => $doctorId,
            'scheduled_at' => $scheduledAt,
            'duration_min' => $duration,
            'status'       => 'pending',
            'reason'       => $reason,
        ]);

        Logger::logAction('patient.appointment.create', [
            'patient_id' => $pacijent['id'],
            'doctor_id'  => $doctorId,
            'scheduled_at' => $scheduledAt,
        ]);

        Session::setFlash('success', 'Termin je uspešno zakazan! Sačekajte potvrdu.');
        $this->redirect('/pacijent/termini');
    }

    public function records(): void
    {
        $pacijent = $this->getPatientProfile();

        if (!$pacijent) {
            $this->render('patient/records.html.twig', ['kartoni' => [], 'nema_profila' => true]);
            return;
        }

        $kartoni = $this->recordModel->findByPatient($pacijent['id']);
        $this->render('patient/records.html.twig', ['kartoni' => $kartoni, 'nema_profila' => false]);
    }

   
    public function messages(): void
    {
        $userId = (int) Session::get('user_id');
        $poruke = $this->messageModel->findForUser($userId);

        foreach ($poruke as $poruka) {
            if ((int)$poruka['receiver_id'] === $userId && !$poruka['is_read']) {
                $this->messageModel->markAsRead($poruka['id']);
            }
        }

        
        $menadzeri = $this->userModel->findBy(['role_id' => 2]);

        $this->render('patient/messages.html.twig', [
            'poruke'    => $poruke,
            'menadzeri' => $menadzeri,
        ]);
    }

  
    public function sendMessage(): void
    {
        $receiverId = (int) ($_POST['receiver_id'] ?? 0);
        $subject    = trim($_POST['subject']  ?? '(bez naslova)');
        $body       = trim($_POST['body']     ?? '');
        $senderId   = (int) Session::get('user_id');

        if (empty($body) || $receiverId <= 0) {
            Session::setFlash('error', 'Poruka ne može biti prazna.');
            $this->redirect('/pacijent/poruke');
            return;
        }

        $this->messageModel->create([
            'sender_id'   => $senderId,
            'receiver_id' => $receiverId,
            'subject'     => $subject,
            'body'        => $body,
        ]);

        Logger::logAction('patient.message.send', ['receiver_id' => $receiverId]);
        Session::setFlash('success', 'Poruka je uspešno poslata.');
        $this->redirect('/pacijent/poruke');
    }
}
