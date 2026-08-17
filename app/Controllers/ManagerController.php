<?php

namespace Medical\Clinic\Controllers;

use Medical\Clinic\Core\Controller;
use Medical\Clinic\Core\Session;
use Medical\Clinic\Core\Logger;
use Medical\Clinic\Models\Appointment;
use Medical\Clinic\Models\Patient;
use Medical\Clinic\Models\Message;
use Medical\Clinic\Models\User;
use Medical\Clinic\Models\MedicalRecord;
use Medical\Clinic\Models\Doctor;
use Medical\Clinic\Models\Diagnosis;


class ManagerController extends Controller{
    private Appointment $appointmentModel;
    private patient $patientModel;
    private Message $messageModel;
    private user $userModel;
    private MedicalRecord $recordModel;
    private Doctor $doctorModel;
    private Diagnosis $diagnosisModel;

    public function __construct(){
        parent::__construct();
        $this-> requireRole('manager','admin');
        $this->appointmentModel = new Appointment();
        $this->patientModel = new patient();
        $this->messageModel = new message();
        $this->userModel    = new user();
        $this->recordModel  = new MedicalRecord();
        $this->doctorModel  = new Doctor();
        $this->diagnosisModel   = new Diagnosis();

    }
    public function dashboard(): void{
        $user_id=(int) Session::get('user_id');

        $stats=[
            'termini_danas' => $this->appointmentModel->countToday(),
            'termini_peding'=> $this->appointmentModel->countByStatus('status'),
            'termini_ukupno'=> $this->appointmentModel->count(),
            'pacijenti' => $this->patientModel->count(),
            'poruke_neprocitane'=> $this->messageModel->countUnread($user_id),
        ];
         $skorasnji = $this->appointmentModel->findAllFull('pending');
        $skorasnji = array_slice($skorasnji,0,5);

        $this->render('manager/dashboard.html.twig',[
            'stats'=>$stats,
             'skorasnji'=>$skorasnji,
        ]);

    }

    public function appointments(): void{
        $status=$_GET['status'] ?? '';
        $termini=$this->appointmentModel->findAllFull($status);

        $this->render('manager/appointments.html.twig',[
            'activestatus' => $status,
            'termini' => $termini

        ]);
    
        }
        public function updateAppointmentStatus(int $id):void{
            $status=$_POST['status'];
            $allowed = ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'];

            if(!in_array($status,$allowed)){
            Session::setFlash('error', 'Nevažeći status.');
            $this->redirect('/manager/termini');
            return;
            }
             $termin=$this->appointmentModel->findById($id);

            if (!$termin) { $this->notFound(); return; }

            $this->appointmentModel->updateStatus($id, $status);
            Logger::logAction('manager.appointment.status', ['id' => $id, 'status' => $status]);
            Session::setFlash('success', 'Status termina je ažuriran.');
            $this->redirect('/manager/termini');
        }

          public function patients(): void
    {
        $pacijenti = $this->patientModel->findAllWithDetails();
        $this->render('manager/patients.html.twig', ['pacijenti' => $pacijenti]);
    }


    public function messages(): void
    {
        $userId  = (int) Session::get('user_id');
        $poruke  = $this->messageModel->findForUser($userId);

        foreach ($poruke as $poruka) {
            if ($poruka['receiver_id'] === $userId && !$poruka['is_read']) {
                $this->messageModel->markAsRead($poruka['id']);
            }
        }

        $this->render('manager/messages.html.twig', ['poruke' => $poruke]);
    }

    
    public function reply(): void
    {
        $receiverId = (int) ($_POST['receiver_id'] ?? 0);
        $subject    = trim($_POST['subject']  ?? '(bez naslova)');
        $body       = trim($_POST['body']     ?? '');
        $senderId   = (int) Session::get('user_id');

        if (empty($body) || $receiverId <= 0) {
            Session::setFlash('error', 'Poruka ne može biti prazna.');
            $this->redirect('/manager/poruke');
            return;
        }

        $this->messageModel->create([
            'sender_id'   => $senderId,
            'receiver_id' => $receiverId,
            'subject'     => $subject,
            'body'        => $body,
        ]);

        Logger::logAction('manager.message.reply', ['receiver_id' => $receiverId]);
        Session::setFlash('success', 'Poruka je uspešno poslata.');
        $this->redirect('/manager/poruke');
    }

    public function patientDetail(int $id): void
    {
        $pacijent = $this->patientModel->findByIdFull($id);
        if (!$pacijent) { $this->notFound(); return; }

        $termini = $this->appointmentModel->findByPatient($id);
        $kartoni = $this->recordModel->findByPatient($id);

        $this->render('manager/patient_detail.html.twig', [
            'pacijent' => $pacijent,
            'termini'  => $termini,
            'kartoni'  => $kartoni,
        ]);
    }

    
    public function addRecordForm(int $patientId): void
    {
        $pacijent = $this->patientModel->findByIdFull($patientId);
        if (!$pacijent) { $this->notFound(); return; }

        $myDoctor = $this->doctorModel->findByUserId((int) Session::get('user_id'));

        $lekari    = $myDoctor ? [] : $this->doctorModel->findAllForSelect();
        $termini   = $myDoctor
            ? $this->appointmentModel->findByPatientAndDoctor($patientId, $myDoctor['id'])
            : $this->appointmentModel->findByPatient($patientId);
        $dijagnoze = $this->diagnosisModel->findAll('icd_code');

        $this->render('manager/record_form.html.twig', [
            'pacijent'  => $pacijent,
            'myDoctor'  => $myDoctor,
            'lekari'    => $lekari,
            'termini'   => $termini,
            'dijagnoze' => $dijagnoze,
        ]);
    }

    // POST /manager/pacijenti/{id}/karton/novi
    public function addRecord(int $patientId): void
    {
        $pacijent = $this->patientModel->findByIdFull($patientId);
        if (!$pacijent) { $this->notFound(); return; }

        // ako je lekar, ignorišemo POST doctor_id i koristimo sopstveni
        $myDoctor = $this->doctorModel->findByUserId((int) Session::get('user_id'));
        $doctorId = $myDoctor
            ? $myDoctor['id']
            : (int) ($_POST['doctor_id'] ?? 0);

        $appointmentId = (int) ($_POST['appointment_id'] ?? 0) ?: null;
        $symptoms      = trim($_POST['symptoms']         ?? '');
        $treatment     = trim($_POST['treatment']        ?? '') ?: null;
        $prescription  = trim($_POST['prescription']     ?? '') ?: null;
        $followUpDate  = trim($_POST['follow_up_date']   ?? '') ?: null;
        $isConfidential = (int) ($_POST['is_confidential'] ?? 0);
        $diagnosisIds  = $_POST['diagnosis_ids'] ?? [];

        if (empty($symptoms) || !$doctorId) {
            Session::setFlash('error', 'Simptomi i lekar su obavezni.');
            $this->redirect("/manager/pacijenti/{$patientId}/karton/novi");
            return;
        }

        $data = [
            'patient_id'      => $patientId,
            'doctor_id'       => $doctorId,
            'appointment_id'  => $appointmentId,
            'symptoms'        => $symptoms,
            'treatment'       => $treatment,
            'prescription'    => $prescription,
            'follow_up_date'  => $followUpDate,
            'is_confidential' => $isConfidential,
        ];

        $recordId = $this->recordModel->createWithDiagnoses($data, $diagnosisIds);

        Logger::logAction('manager.record.create', [
            'record_id'  => $recordId,
            'patient_id' => $patientId,
            'doctor_id'  => $doctorId,
        ]);

        Session::setFlash('success', 'Medicinski karton je uspešno sačuvan.');
        $this->redirect("/manager/pacijenti/{$patientId}");
    }
}

?>