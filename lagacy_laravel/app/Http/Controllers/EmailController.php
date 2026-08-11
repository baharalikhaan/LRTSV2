<?php

namespace App\Http\Controllers;

use App\Notifications\EmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailer;
use App\Models\User;
use App\Models\Project;
use App\Notifications;
use App\Models\EmailTemplate;
use App\Models\EmailSMTP;
use App\Models\Alerts;
use App\Notifications\CompleteInfo;
use Illuminate\Support\Facades\DB;
use DataTables;
use App\Mail\welcomeEmail;
use App\Models\Cycle;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Carbon\Carbon;
use Psy\Readline\Hoa\Console;
use SendGrid\Mail\Attachment;
use Webklex\IMAP\Facades\Client;

class EmailController extends Controller
{
    public function fetchFailedEmails()
    {
        $client = Client::account('default');
        $inbox = $client->getFolder('INBOX');
        $messages = $inbox->messages()->unseen()->get();
        $count = 0;
        foreach ($messages as $message) {
            $subject = $message->getSubject();
            $sender = $message->getFrom()[0]->mail;
            $body = $message->getTextBody();
            if ($this->isUndeliverable($subject)) {
                $count++;
                $pattern = '/<mailto:(.*?)>/';
                $email = '';
                if (preg_match($pattern, $body, $matches)) {
                    $email = $matches[1];
                }
                DB::table('email_sending_status')->insert([
                    'title' =>  str_replace('Undeliverable: ', '', $subject),
                    'body' =>  '',
                    'email' => $email,
                    'category' =>  str_replace('Undeliverable: ', '', $subject),
                    'error_message' => $body,
                    'sending_status' => 'Failed'
                ]);

                $message->setFlag('Seen');
            } else {
            }
        }


        return redirect()->back()->with('failedEmails', '<b>' . $count . '</b> Failed Emails fetched');
    }

    // Function to check if a message is undeliverable
    private function isUndeliverable($subject)
    {
        if (stripos($subject, 'undeliverable') !== false) {
            return true;
        }

        return false;
    }

    public function sendEmailAdmin()
    {
        $emails = DB::table('users')
            ->select('email')
            ->get();

        return view('sendEmailAdmin', ['emails' => $emails]);
    }


    //helper function for send bulk general email
    public function sendMail($email, $cc, $subject, $body, $attachments)
    {
        $signature = DB::table('email_template')->find(3)->signature;
        $mail = new PHPMailer(true);
        $smtp = DB::table('email_settings')->find(1);


        $mail->isSMTP();
        $mail->Host = $smtp->host;
        $mail->SMTPAuth = true;
        $mail->Username =  $smtp->username;
        $mail->Password =  $smtp->password;
        $mail->SMTPSecure =  $smtp->smtp_secure;
        $mail->Port = $smtp->port;

        $mail->AddEmbeddedImage(public_path('images/signature/logo.png'), "logo", "logo.png");
        $mail->AddEmbeddedImage(public_path('images/signature/facebook.png'), "facebook", "facebook.png");
        $mail->AddEmbeddedImage(public_path('images/signature/twitter.png'), "twitter", "twitter.png");
        $mail->AddEmbeddedImage(public_path('images/signature/youtube.png'), "youtube", "youtube.png");
        $mail->AddEmbeddedImage(public_path('images/signature/instagram.png'), "instagram", "instagram.png");
        $mail->AddEmbeddedImage(public_path('images/signature/linkedin.png'), "linkedin", "linkedin.png");
        $mail->AddEmbeddedImage(public_path('images/signature/iso.png'), "iso", "iso.png");

        $mail->setFrom($smtp->setfrom_email, $smtp->setfrom_name);

        $mail->clearAddresses();

        $mail->addAddress($email);
        if ($cc <> '')
            $mail->addAddress($cc);
        //Attachments


        if ($attachments['name'][0] <> '') {
            if ($attachments) {
                foreach ($attachments['tmp_name'] as $index => $tmpName) {
                    $attachmentName = $attachments['name'][$index];
                    $mail->addAttachment($tmpName, $attachmentName);
                }
            }
         }
        //Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body . $signature;

        if (!$mail->send()) {

            $errorMessage = $mail->ErrorInfo;

            DB::table('email_sending_status')->insert([
                'title' =>  $subject,
                'body' =>  $body,
                'email' =>  $email,
                'category' => 'General Email by Admin',
                'error_message' => $errorMessage,
                'sending_status' => 'Success'
            ]);


            return

                '<span style="color: green;">' . $email . '</span> <i style="color:green"class="fas fa-check"></i><br>';
        } else {

            $errorMessage = $mail->ErrorInfo;
            DB::table('email_sending_status')->insert([
                'title' =>  $subject,
                'body' =>  $body,
                'email' =>  $email,
                'category' => 'General Email by Admin',
                'error_message' => $errorMessage,
                'sending_status' => 'Failed'
            ]);

            return  '<span style="color: red;">' . $email . '</span><i style="color:red" class="fas fa-times"></i><br>';
        }
    }


    //bulk general email send
    public function sendEmailAdminSave(Request $request)
    {
        $messages = [];
        foreach ($request->input('recipients') as $recipient) {
            try {


                if ($recipient == 'All') {
                    $emails = DB::table('users')->select('email')->get();
                    foreach ($emails as $email) {

                        array_push($messages,  $this->sendMail($email->email, $request->input('cc'),  $request->input('subject'), $request->input('body'),  isset($_FILES['attachment']) ? $_FILES['attachment'] : ''));
                    }
                    break;
                } else  if ($recipient == 'All LPIs') {
                    $emails = DB::table('users')->select('email')->where('type', '=', 'LPI')->get();
                    foreach ($emails as $email) {
                        array_push($messages, $this->sendMail($email->email, $request->input('cc'),  $request->input('subject'), $request->input('body'), isset($_FILES['attachment']) ? $_FILES['attachment'] : ''));
                    }
                } else if ($recipient == 'All Reviewers') {
                    $emails = DB::table('users')->select('email')->where('type', '=', 'Reviewer')->get();
                    foreach ($emails as $email) {
                        array_push($messages,  $this->sendMail($email->email, $request->input('cc'),  $request->input('subject'), $request->input('body'), isset($_FILES['attachment']) ? $_FILES['attachment'] : ''));
                    }
                } else
                    array_push($messages, $this->sendMail($recipient, $request->input('cc'),  $request->input('subject'), $request->input('body'), isset($_FILES['attachment']) ? $_FILES['attachment'] : ''));
            } catch (Exception $e) {
                continue;
            }
        }

        return redirect()->back()->with('successadminemails',  json_encode($messages));
    }



    public function completeInfo()
    {
        $user = DB::table('users')
            ->select('name', 'email')
            ->where('id', '=', '2306')
            ->get();
        // dd($user);
        $context = [
            'greeting' => 'Hi ' . $user . 'name' . ',',
            'body' => 'Your project has been added to Reearch Tracking System (RTS) by Qatar University. Kinldy login to RTS and update the project details with in 7 working days. ',
            'thanks' => 'Thank you this is from Office of Research and Graduate Studies',
            'actionText' => 'Update Project Information',
            'actionURL' => url('/'),
        ];

        new CompleteInfo($context);
    }

    public function ajaxListemailSetting()
    {
        $type = auth()->user()->type;
        $user = auth()->user()->id;
        if ($type == User::TYPE_ADMIN) {
            $data = DB::table('email_template')
                ->select('*')
                ->get();
        }

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $url = route('emailEdit', ['id' => $row->id]);
                $Btn = '<a href="' . $url . '" class="btn btn-teal btn-sm">Edit</a>';

                return   $Btn;
            })


            ->rawColumns(['action'])
            ->make(true);
    }

    public function ProgressReportReminder()
    {

        //Bi-Monthly reminder
        $projects = DB::table('projects')
            ->join('cycle', 'cycle.id', '=', 'projects.cycle')
            ->join('users', 'users.id', '=', 'projects.user_id')

            ->where('progress_bi_monthly_reminder', '=', '0')
            ->whereDate('cycle.prog_rpt_deadline', '=', Carbon::now()->addDays(15))
            ->selectRaw('cycle.prog_rpt_deadline,cycle.id,cycle.cycle_title,users.name,users.email,projects.user_id')
            ->get();

        $email = DB::table('email_template')
            ->select('*')
            ->where('id', '=', 9)
            ->first();

        $mail = new PHPMailer(true);

        $smtp = DB::table('email_settings')->find(1);

        foreach ($projects as $ct) {
            try {


                $data = [
                    'title' => 'Progress Report Upload Reminder',
                    'description' => 'Progress Report uploading deadline is approaching in 15 days, kindly upload the progress report',
                    'link' => '<a href="' . route('project', ['c_id' => $ct->id]) . '"> This Link</a>',
                    'user_id' => $ct->user_id, // Replace $userId with the actual user ID
                    'date' => now(), // Current date and time
                    'isentertained' => false // Or true depending on your requirement
                ];

                Alerts::create($data);

                $mail->isSMTP();
                $mail->Host = $smtp->host;
                $mail->SMTPAuth = true;
                $mail->Username =  $smtp->username;
                $mail->Password =  $smtp->password;
                $mail->SMTPSecure =  $smtp->smtp_secure;
                $mail->Port = $smtp->port;


                $mail->AddEmbeddedImage(public_path('images/signature/logo.png'), "logo", "logo.png");
                $mail->AddEmbeddedImage(public_path('images/signature/facebook.png'), "facebook", "facebook.png");
                $mail->AddEmbeddedImage(public_path('images/signature/twitter.png'), "twitter", "twitter.png");
                $mail->AddEmbeddedImage(public_path('images/signature/youtube.png'), "youtube", "youtube.png");
                $mail->AddEmbeddedImage(public_path('images/signature/instagram.png'), "instagram", "instagram.png");
                $mail->AddEmbeddedImage(public_path('images/signature/linkedin.png'), "linkedin", "linkedin.png");
                $mail->AddEmbeddedImage(public_path('images/signature/iso.png'), "iso", "iso.png");


                $mail->setFrom($smtp->setfrom_email, $smtp->setfrom_name);

                $mail->clearAddresses();

                $mail->addAddress($ct->email);

                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

                $body =  str_replace("*name*",  $ct->name, $email->contents);
                $body =  str_replace("*deadline*",  $ct->prog_rpt_deadline, $body);
                $body =  str_replace("*cycle*", $ct->cycle_title, $body);

                $mail->isHTML(true);
                $mail->Subject = $email->subject;
                $mail->Body = $body . $email->signature;
                $mail->send();
                if ($mail->send()) {
                    DB::table('email_sending_status')->insert([
                        'title' => $email->subject,
                        'body' =>  $body,
                        'email' => $ct->email,
                        'category' => 'Progress Report Upload Reminder',
                        'error_message' => '',
                        'sending_status' => 'Success'
                    ]);

                    //update the record to avoid recursive email sendng
                    $record = Cycle::findOrFail($ct->id);
                    $record->progress_bi_monthly_reminder = 1;
                    $record->save();
                } else {
                    $errorMessage = $mail->ErrorInfo;
                    DB::table('email_sending_status')->insert([
                        'title' => $email->subject,
                        'body' =>  $body,
                        'email' =>  $ct->email,
                        'category' => 'Progress Report Upload Reminder (Bi-Monthly)',
                        'error_message' => $errorMessage,
                        'sending_status' => 'Failed'
                    ]);
                }
            } catch (Exception $e) {


                $errorMessage = $mail->ErrorInfo;
                DB::table('email_sending_status')->insert([
                    'title' => $email->subject,
                    'body' =>  $body,
                    'email' => $ct->email,
                    'category' => 'Progress Report Upload Reminder (Bi-Monthly)',
                    'error_message' => $errorMessage,
                    'sending_status' => 'Failed'
                ]);

                continue;
            }
        }



        //Monthly reminder
        $projects = DB::table('projects')
            ->join('cycle', 'cycle.id', '=', 'projects.cycle')
            ->join('users', 'users.id', '=', 'projects.user_id')


            ->where('progress_monthly_reminder', '=', '0')
            ->whereDate('cycle.prog_rpt_deadline', '=', Carbon::now()->addDays(31))
            ->selectRaw('cycle.prog_rpt_deadline,cycle.id,cycle.cycle_title,users.name,users.email,projects.user_id')
            ->get();

        $email = DB::table('email_template')
            ->select('*')
            ->where('id', '=', 9)
            ->first();
        $mail = new PHPMailer(true);
        $smtp = DB::table('email_settings')->find(1);

        foreach ($projects as $ct) {


            try {

                $data = [
                    'title' => 'Progress Report Upload Reminder',
                    'description' => 'Progress Report uploading deadline is approaching in 1 month, kindly upload the progress report',
                    'link' => '<a href="' . route('project', ['c_id' => $ct->id]) . '"> This Link</a>',
                    'user_id' => $ct->user_id, // Replace $userId with the actual user ID
                    'date' => now(), // Current date and time
                    'isentertained' => false // Or true depending on your requirement
                ];

                Alerts::create($data);

                $mail->isSMTP();
                $mail->Host = $smtp->host;
                $mail->SMTPAuth = true;
                $mail->Username =  $smtp->username;
                $mail->Password =  $smtp->password;
                $mail->SMTPSecure =  $smtp->smtp_secure;
                $mail->Port = $smtp->port;

                $mail->AddEmbeddedImage(public_path('images/signature/logo.png'), "logo", "logo.png");
                $mail->AddEmbeddedImage(public_path('images/signature/facebook.png'), "facebook", "facebook.png");
                $mail->AddEmbeddedImage(public_path('images/signature/twitter.png'), "twitter", "twitter.png");
                $mail->AddEmbeddedImage(public_path('images/signature/youtube.png'), "youtube", "youtube.png");
                $mail->AddEmbeddedImage(public_path('images/signature/instagram.png'), "instagram", "instagram.png");
                $mail->AddEmbeddedImage(public_path('images/signature/linkedin.png'), "linkedin", "linkedin.png");
                $mail->AddEmbeddedImage(public_path('images/signature/iso.png'), "iso", "iso.png");


                $mail->setFrom($smtp->setfrom_email, $smtp->setfrom_name);


                $mail->clearAddresses();

                $mail->addAddress($ct->email);

                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

                $body =  str_replace("*name*",  $ct->name, $email->contents);
                $body =  str_replace("*deadline*",  $ct->prog_rpt_deadline, $body);
                $body =  str_replace("*cycle*", $ct->cycle_title, $body);

                $mail->isHTML(true);
                $mail->Subject = $email->subject;
                $mail->Body = $body . $email->signature;

                if ($mail->send()) {
                    DB::table('email_sending_status')->insert([
                        'title' => $email->subject,
                        'body' =>  $body,
                        'email' => $ct->email,
                        'category' => 'Progress Report Upload Reminder (Monthly)',
                        'error_message' => '',
                        'sending_status' => 'Success'
                    ]);

                    //update the record to avoid recursive email sendng
                    $record = Cycle::findOrFail($ct->id);
                    $record->progress_monthly_reminder = 1;
                    $record->save();
                } else {

                    $errorMessage = $mail->ErrorInfo;
                    DB::table('email_sending_status')->insert([
                        'title' => $email->subject,
                        'body' =>  $body,
                        'email' =>  $ct->email,
                        'category' => 'Progress Report Upload Reminder (Monthly)',
                        'error_message' => $errorMessage,
                        'sending_status' => 'Failed'
                    ]);
                }
            } catch (Exception $e) {

                DB::table('email_sending_status')->insert([
                    'title' => $email->subject,
                    'body' =>  $body,
                    'email' => $ct->email,
                    'category' => 'Progress Report Upload Reminder (Monthly)',
                    'error_message' => '',
                    'sending_status' => 'Failed'
                ]);
                continue;
            }
        }
    }

    public function FinalReportReminder()
    {

        //Bi-Monthly reminder
        $projects = DB::table('projects')
            ->join('cycle', 'cycle.id', '=', 'projects.cycle')
            ->join('users', 'users.id', '=', 'projects.user_id')

            ->where('final_bi_monthly_reminder', '=', '0')
            ->whereDate('cycle.final_rpt_deadline', '=', Carbon::now()->addDays(15))
            ->selectRaw('cycle.final_rpt_deadline,cycle.id,cycle.cycle_title,users.name,users.email,users.id as user_id')
            ->get();



        $email = DB::table('email_template')
            ->select('*')
            ->where('id', '=', 8)
            ->first();

        $mail = new PHPMailer(true);

        $smtp = DB::table('email_settings')->find(1);
        foreach ($projects as $ct) {
            try {


                $data = [
                    'title' => 'Final Report Upload Reminder',
                    'description' => 'Final Report uploading deadline is approaching in 15 days, kindly upload the progress',
                    'link' => '<a href="' . route('project', ['c_id' => $ct->id]) . '"> This Link</a>',
                    'user_id' => $ct->user_id, // Replace $userId with the actual user ID
                    'date' => now(), // Current date and time
                    'isentertained' => false // Or true depending on your requirement
                ];

                Alerts::create($data);


                $mail->isSMTP();
                $mail->Host = $smtp->host;
                $mail->SMTPAuth = true;
                $mail->Username =  $smtp->username;
                $mail->Password =  $smtp->password;
                $mail->SMTPSecure =  $smtp->smtp_secure;
                $mail->Port = $smtp->port;


                $mail->AddEmbeddedImage(public_path('images/signature/logo.png'), "logo", "logo.png");
                $mail->AddEmbeddedImage(public_path('images/signature/facebook.png'), "facebook", "facebook.png");
                $mail->AddEmbeddedImage(public_path('images/signature/twitter.png'), "twitter", "twitter.png");
                $mail->AddEmbeddedImage(public_path('images/signature/youtube.png'), "youtube", "youtube.png");
                $mail->AddEmbeddedImage(public_path('images/signature/instagram.png'), "instagram", "instagram.png");
                $mail->AddEmbeddedImage(public_path('images/signature/linkedin.png'), "linkedin", "linkedin.png");
                $mail->AddEmbeddedImage(public_path('images/signature/iso.png'), "iso", "iso.png");


                $mail->setFrom($smtp->setfrom_email, $smtp->setfrom_name);

                $mail->clearAddresses();
                $mail->addAddress($ct->email);
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

                $body =  str_replace("*name*",  $ct->name, $email->contents);
                $body =  str_replace("*deadline*",  $ct->final_rpt_deadline, $body);
                $body =  str_replace("*cycle*", $ct->cycle_title, $body);



                $mail->isHTML(true);
                $mail->Subject = $email->subject;
                $mail->Body = $body . $email->signature;

                if ($mail->send()) {
                    DB::table('email_sending_status')->insert([
                        'title' => $email->subject,
                        'body' =>  $body,
                        'email' => $ct->email,
                        'category' => 'Progress Report Upload Reminder (Bi-Monthly)',
                        'error_message' => '',
                        'sending_status' => 'Success'
                    ]);


                    $record = Cycle::findOrFail($ct->id);
                    $record->final_bi_monthly_reminder = 1;
                    $record->save();
                } else {
                    $errorMessage = $mail->ErrorInfo;
                    DB::table('email_sending_status')->insert([
                        'title' => $email->subject,
                        'body' =>  $body,
                        'email' =>  $ct->email,
                        'category' => 'Final Report Upload Reminder (B-Monthly)',
                        'error_message' => $errorMessage,
                        'sending_status' => 'Failed'
                    ]);
                }
            } catch (Exception $e) {


                $errorMessage = $mail->ErrorInfo;
                DB::table('email_sending_status')->insert([
                    'title' => $email->subject,
                    'body' =>  $body,
                    'email' => $ct->email,
                    'category' => 'Final Report Upload Reminder (Bi-Monthly)',
                    'error_message' => $errorMessage,
                    'sending_status' => 'Failed'
                ]);


                continue;
            }
        }



        //Monthly reminder
        $projects = DB::table('projects')
            ->join('cycle', 'cycle.id', '=', 'projects.cycle')
            ->join('users', 'users.id', '=', 'projects.user_id')

            ->where('final_monthly_reminder', '=', '0')
            ->whereDate('cycle.final_rpt_deadline', '=', Carbon::now()->addDays(31))
            ->selectRaw('cycle.final_rpt_deadline,cycle.id,cycle.cycle_title,users.name,users.email, users.id as user_id')
            ->get();

        $email = DB::table('email_template')
            ->select('*')
            ->where('id', '=', 8)
            ->first();

        $mail = new PHPMailer(true);

        $smtp = DB::table('email_settings')->find(1);
        foreach ($projects as $ct) {
            try {



                $data = [
                    'title' => 'Final Report Upload Reminder',
                    'description' => 'Final Report uploading deadline is approaching in 1 Month, kindly upload the progress',
                    'link' => '<a href="' . route('project', ['c_id' => $ct->id]) . '"> This Link</a>',
                    'user_id' => $ct->user_id, // Replace $userId with the actual user ID
                    'date' => now(), // Current date and time
                    'isentertained' => false // Or true depending on your requirement
                ];

                Alerts::create($data);

                $mail->isSMTP();
                $mail->Host = $smtp->host;
                $mail->SMTPAuth = true;
                $mail->Username =  $smtp->username;
                $mail->Password =  $smtp->password;
                $mail->SMTPSecure =  $smtp->smtp_secure;
                $mail->Port = $smtp->port;


                $mail->AddEmbeddedImage(public_path('images/signature/logo.png'), "logo", "logo.png");
                $mail->AddEmbeddedImage(public_path('images/signature/facebook.png'), "facebook", "facebook.png");
                $mail->AddEmbeddedImage(public_path('images/signature/twitter.png'), "twitter", "twitter.png");
                $mail->AddEmbeddedImage(public_path('images/signature/youtube.png'), "youtube", "youtube.png");
                $mail->AddEmbeddedImage(public_path('images/signature/instagram.png'), "instagram", "instagram.png");
                $mail->AddEmbeddedImage(public_path('images/signature/linkedin.png'), "linkedin", "linkedin.png");
                $mail->AddEmbeddedImage(public_path('images/signature/iso.png'), "iso", "iso.png");


                $mail->setFrom($smtp->setfrom_email, $smtp->setfrom_name);

                $mail->clearAddresses();
                $mail->addAddress($ct->email);
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

                $body =  str_replace("*name*",  $ct->name, $email->contents);
                $body =  str_replace("*deadline*",  $ct->final_rpt_deadline, $body);
                $body =  str_replace("*cycle*", $ct->cycle_title, $body);

                $mail->isHTML(true);
                $mail->Subject = $email->subject;
                $mail->Body = $body . $email->signature;

                if ($mail->send()) {
                    DB::table('email_sending_status')->insert([
                        'title' => $email->subject,
                        'body' =>  $body,
                        'email' =>  $ct->email,
                        'category' => 'Progress Report Upload Reminder (Monthly)',
                        'error_message' => '',
                        'sending_status' => 'Success'
                    ]);

                    $record = Cycle::findOrFail($ct->id);
                    $record->final_monthly_reminder = 1;
                    $record->save();
                } else {
                    $errorMessage = $mail->ErrorInfo;
                    DB::table('email_sending_status')->insert([
                        'title' => $email->subject,
                        'body' =>  $body,
                        'email' =>  $ct->email,
                        'category' => 'Final Report Upload Reminder (Monthly)',
                        'error_message' => $errorMessage,
                        'sending_status' => 'Failed'
                    ]);
                }
            } catch (Exception $e) {
                $errorMessage = $mail->ErrorInfo;
                DB::table('email_sending_status')->insert([
                    'title' => $email->subject,
                    'body' =>  $body,
                    'email' => $ct->email,
                    'category' => 'Final Report Upload Reminder (B-Monthly)',
                    'error_message' => $errorMessage,
                    'sending_status' => 'Failed'
                ]);
                continue;
            }
        }
    }

    public function ReviewersAssignedNotify() {}

    public function GradesUploadedNotify() {}

    public function testingEmail()
    {

        $mail = new PHPMailer(true);
        $smtp = DB::table('email_settings')->find(1);

        try {
            $mail->isSMTP();
            $mail->Host = $smtp->host;
            $mail->SMTPAuth = true;
            $mail->Username =  $smtp->username;
            $mail->Password =  $smtp->password;
            $mail->SMTPSecure =  $smtp->smtp_secure;
            $mail->Port = $smtp->port;

            $mail->setFrom($smtp->setfrom_email, $smtp->setfrom_name);
            //   $mail->setFrom('researchtracksystem@gmail.com', 'RTS-Office of Research and Graduate Studies');
            //  $mail->addAddress('imhira4@gmail.com', 'Recipient1');
            $mail->addAddress('baharalikhaan@gmail.com', 'Recipient1');
            //Attachments

            //Content
            $mail->isHTML(true);
            $mail->Subject = 'Mail Subject Here!';
            $mail->Body = 'Mail body content goes here';

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }
    }

    public function announcement()
    {
        $email = DB::table('email_template')
            ->select('*')
            ->where('id', '=', 3)
            ->first();

        $smtp = DB::table('email_settings')->find(1);
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $smtp->host;
            $mail->SMTPAuth = true;
            $mail->Username =  $smtp->username;
            $mail->Password =  $smtp->password;
            $mail->SMTPSecure =  $smtp->smtp_secure;
            $mail->Port = $smtp->port;

            $mail->AddEmbeddedImage(public_path('images/signature/logo.png'), "logo", "logo.png");
            $mail->AddEmbeddedImage(public_path('images/signature/facebook.png'), "facebook", "facebook.png");
            $mail->AddEmbeddedImage(public_path('images/signature/twitter.png'), "twitter", "twitter.png");
            $mail->AddEmbeddedImage(public_path('images/signature/youtube.png'), "youtube", "youtube.png");
            $mail->AddEmbeddedImage(public_path('images/signature/instagram.png'), "instagram", "instagram.png");
            $mail->AddEmbeddedImage(public_path('images/signature/linkedin.png'), "linkedin", "linkedin.png");
            $mail->AddEmbeddedImage(public_path('images/signature/iso.png'), "iso", "iso.png");

            //   $mail->setFrom('rts@qu.edu.qa', 'RTS-Office of Research and Graduate Studies');
            $mail->setFrom($smtp->setfrom_email, $smtp->setfrom_name);
            $mail->addAddress('imhira4@gmail.com', 'Recipient1');
            $mail->addAddress('baharalikhaan@gmail.com', 'Recipient2');
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            //Attachments

            //Content
            $mail->isHTML(true);
            $mail->Subject = $email->subject;
            $mail->Body = $email->contents .  $email->signature;

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }
    }

    public function user_added($user)
    {

        $record = $user;
        $email = DB::table('email_template')
            ->select('*')
            ->where('id', '=', 5)
            ->first();

        $smtp = DB::table('email_settings')->find(1);
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $smtp->host;
            $mail->SMTPAuth = true;
            $mail->Username =  $smtp->username;
            $mail->Password =  $smtp->password;
            $mail->SMTPSecure =  $smtp->smtp_secure;
            $mail->Port = $smtp->port;

            $mail->AddEmbeddedImage(public_path('images/signature/logo.png'), "logo", "logo.png");
            $mail->AddEmbeddedImage(public_path('images/signature/facebook.png'), "facebook", "facebook.png");
            $mail->AddEmbeddedImage(public_path('images/signature/twitter.png'), "twitter", "twitter.png");
            $mail->AddEmbeddedImage(public_path('images/signature/youtube.png'), "youtube", "youtube.png");
            $mail->AddEmbeddedImage(public_path('images/signature/instagram.png'), "instagram", "instagram.png");
            $mail->AddEmbeddedImage(public_path('images/signature/linkedin.png'), "linkedin", "linkedin.png");
            $mail->AddEmbeddedImage(public_path('images/signature/iso.png'), "iso", "iso.png");


            $body =  str_replace("*name*",  $user->name, $email->contents);


            // $mail->setFrom('rts@qu.edu.qa', 'RTS-Office of Research and Graduate Studies');
            $mail->setFrom($smtp->setfrom_email, $smtp->setfrom_name);
            $mail->addAddress($record->email);
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $mail->isHTML(true);
            $mail->Subject = $email->subject;
            $mail->Body = $body  . $email->signature;
            //     $mail->send();
        } catch (Exception $e) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }
    }


    public function sendBudgetReminder($project_id)
    {
        $project = DB::table('project_api')->join('projects', 'projects.old_project_id', '=', 'project_api.project_name')
            ->join('users', 'users.id', '=', 'projects.user_id')
            ->selectRaw('project_api.*,users.email,users.name')
            ->where('project_api.project_name', '=', $project_id)
            ->first();

        $email = DB::table('email_template')
            ->select('*')
            ->where('id', '=', 3)
            ->first();


        if ($project) {


            $smtp = DB::table('email_settings')->find(1);
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = $smtp->host;
                $mail->SMTPAuth = true;
                $mail->Username =  $smtp->username;
                $mail->Password =  $smtp->password;
                $mail->SMTPSecure =  $smtp->smtp_secure;
                $mail->Port = $smtp->port;


                $mail->AddEmbeddedImage(public_path('images/signature/logo.png'), "logo", "logo.png");
                $mail->AddEmbeddedImage(public_path('images/signature/facebook.png'), "facebook", "facebook.png");
                $mail->AddEmbeddedImage(public_path('images/signature/twitter.png'), "twitter", "twitter.png");
                $mail->AddEmbeddedImage(public_path('images/signature/youtube.png'), "youtube", "youtube.png");
                $mail->AddEmbeddedImage(public_path('images/signature/instagram.png'), "instagram", "instagram.png");
                $mail->AddEmbeddedImage(public_path('images/signature/linkedin.png'), "linkedin", "linkedin.png");
                $mail->AddEmbeddedImage(public_path('images/signature/iso.png'), "iso", "iso.png");

                // $mail->setFrom('rts@qu.edu.qa', 'RTS-Office of Research and Graduate Studies');
                $mail->setFrom($smtp->setfrom_email, $smtp->setfrom_name);
                $mail->addAddress($project->email);
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

                $body =  str_replace("*name*",  $project->name, $email->contents);
                $body =  str_replace("*project_name*",  $project->project_name, $body);
                $body =  str_replace("*balance*", $project->available_balance, $body);

                $mail->isHTML(true);
                $mail->Subject = $email->subject;
                $mail->Body = $body . $email->signature;

                if ($mail->send()) {
                    DB::table('email_sending_status')->insert([
                        'title' => $email->subject,
                        'body' =>  $body,
                        'email' =>  $project->email,
                        'category' => 'Budget Reminder',
                        'error_message' => '',
                        'sending_status' => 'Success'
                    ]);
                } else {
                    $errorMessage = $mail->ErrorInfo;
                    DB::table('email_sending_status')->insert([
                        'title' => $email->subject,
                        'body' =>  $body,
                        'email' =>  $project->email,
                        'category' => 'Budget Reminder',
                        'error_message' => $errorMessage,
                        'sending_status' => 'Failed'
                    ]);
                }

                return redirect()->back()->with('budgetemail', 'Email sent successfully');
            } catch (Exception $e) {

                $errorMessage = $mail->ErrorInfo;
                DB::table('email_sending_status')->insert([
                    'title' => $email->subject,
                    'body' =>  $body,
                    'email' => $project->email,
                    'category' => 'Budget Reminder',
                    'error_message' => $errorMessage,
                    'sending_status' => 'Failed'
                ]);

                return redirect()->back()->with('budgetemail', 'Email sending error');
            }
        } else {
            return redirect()->back()->with('budgetemail', 'Project not registered in RTS. Kindly send the email manually.');
        }
    }

    public function registerProjectReminder()
    {
        $conf_tool = DB::table('from_conf_tool')
            ->Leftjoin('users', 'users.email', '=', 'from_conf_tool.email')->where('added', '=', '0')
            ->selectRaw('from_conf_tool.*,users.name')->get();

        $email = DB::table('email_template')
            ->select('*')
            ->where('id', '=', 4)
            ->first();

        $mail = new PHPMailer(true);

        $smtp = DB::table('email_settings')->find(1);
        foreach ($conf_tool as $ct) {
            try {

                $mail->isSMTP();
                $mail->Host = $smtp->host;
                $mail->SMTPAuth = true;
                $mail->Username =  $smtp->username;
                $mail->Password =  $smtp->password;
                $mail->SMTPSecure =  $smtp->smtp_secure;
                $mail->Port = $smtp->port;



                $mail->AddEmbeddedImage(public_path('images/signature/logo.png'), "logo", "logo.png");
                $mail->AddEmbeddedImage(public_path('images/signature/facebook.png'), "facebook", "facebook.png");
                $mail->AddEmbeddedImage(public_path('images/signature/twitter.png'), "twitter", "twitter.png");
                $mail->AddEmbeddedImage(public_path('images/signature/youtube.png'), "youtube", "youtube.png");
                $mail->AddEmbeddedImage(public_path('images/signature/instagram.png'), "instagram", "instagram.png");
                $mail->AddEmbeddedImage(public_path('images/signature/linkedin.png'), "linkedin", "linkedin.png");
                $mail->AddEmbeddedImage(public_path('images/signature/iso.png'), "iso", "iso.png");

                $mail->clearAttachments();
                $aatchment = storage_path('app\downloads\add_project_tutorial.pdf');
                $mail->addAttachment($aatchment);

                // $mail->setFrom('rts@qu.edu.qa', 'RTS-Office of Research and Graduate Studies');
                $mail->setFrom($smtp->setfrom_email, $smtp->setfrom_name);

                $mail->clearAddresses();
                $mail->addAddress($ct->email);
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

                $body =  str_replace("*name*",  $ct->name, $email->contents);
                $body =  str_replace("*old_project_id*", $ct->old_project_id, $body);
                $body =  str_replace("*project_title*", $ct->title, $body);

                $body =  str_replace("*link*", '<a href="' . route('newProject', ['p_id' => $ct->id]) . '"> This Link</a>', $body);

                $mail->isHTML(true);
                $mail->Subject = $email->subject;
                $mail->Body = $body . $email->signature;

                if ($mail->send()) {
                    DB::table('email_sending_status')->insert([
                        'title' => $email->subject,
                        'body' =>  $body,
                        'email' =>  $ct->email,
                        'category' => 'Register Project Reminder',
                        'error_message' => '',
                        'sending_status' => 'Success'
                    ]);
                } else {
                    $errorMessage = $mail->ErrorInfo;
                    DB::table('email_sending_status')->insert([
                        'title' => $email->subject,
                        'body' =>  $body,
                        'email' => $ct->email,
                        'category' => 'Register Project Reminder',
                        'error_message' => $errorMessage,
                        'sending_status' => 'Failed'
                    ]);
                }
            } catch (Exception $e) {

                $errorMessage = $mail->ErrorInfo;
                DB::table('email_sending_status')->insert([
                    'title' => $email->subject,
                    'body' =>  $body,
                    'email' => $ct->email,
                    'category' => 'Register Project Reminder',
                    'error_message' => $errorMessage,
                    'sending_status' => 'Failed'
                ]);
                continue;
            }
        }

        return redirect()->back()->with('lpiemail', 'Emails sent successfully');
    }


    public function reviewerAssigned($project_reviewer)
    {
        $user = User::find($project_reviewer->user_id);


        $data = [
            'title' => 'Assigned projct for review',
            'description' => 'You have been assigned a project for review, Click on the link to accept the agreement.',
            'link' => '<a href="' . route('acceptProposal', ['r_id' => $project_reviewer->id]) . '"> This Link</a>',
            'user_id' => $user->id, // Replace $userId with the actual user ID
            'date' => now(), // Current date and time
            'isentertained' => false // Or true depending on your requirement
        ];

        Alerts::create($data);

        $project = DB::table('cycle')->join('projects', 'cycle.id', '=', 'projects.cycle')
            ->selectRaw('cycle.cycle_title,projects.old_project_id,cycle.prog_rpt_deadline')
            ->where('projects.id', '=', $project_reviewer->project_id)
            ->first();

        $email = DB::table('email_template')
            ->select('*')
            ->where('id', '=', 2)
            ->first();

        $smtp = DB::table('email_settings')->find(1);
        $mail = new PHPMailer(true);
        try {


            $mail->isSMTP();
            $mail->Host = $smtp->host;
            $mail->SMTPAuth = true;
            $mail->Username =  $smtp->username;
            $mail->Password =  $smtp->password;
            $mail->SMTPSecure =  $smtp->smtp_secure;
            $mail->Port = $smtp->port;

            $mail->AddEmbeddedImage(public_path('images/signature/logo.png'), "logo", "logo.png");
            $mail->AddEmbeddedImage(public_path('images/signature/facebook.png'), "facebook", "facebook.png");
            $mail->AddEmbeddedImage(public_path('images/signature/twitter.png'), "twitter", "twitter.png");
            $mail->AddEmbeddedImage(public_path('images/signature/youtube.png'), "youtube", "youtube.png");
            $mail->AddEmbeddedImage(public_path('images/signature/instagram.png'), "instagram", "instagram.png");
            $mail->AddEmbeddedImage(public_path('images/signature/linkedin.png'), "linkedin", "linkedin.png");
            $mail->AddEmbeddedImage(public_path('images/signature/iso.png'), "iso", "iso.png");

            $mail->addCustomHeader('Content-Disposition', 'inline; filename="logo.png"');

            // $mail->setFrom('rts@qu.edu.qa', 'RTS-Office of Research and Graduate Studies');
            $mail->setFrom($smtp->setfrom_email, $smtp->setfrom_name);
            $mail->addAddress($user->email);


            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );


            $imageUrl = asset('images/signature/logo.png');
       //     $aatchment =  storage_path('app\downloads\Peer Reviewer Agreement 2023.docx');
            //  $aatchment2 =  storage_path('app\downloads\add_agreement_tutorial.pdf');
            $date = Carbon::parse($project_reviewer->created_at)->addWeeks(2);
            $dateString = $date->toDateString();

            $grant = '';
            $grant =  explode("-", $project->old_project_id);
            if (isset($grant[0])) {

                $result = DB::table('grant_title')->select('*')->where('code', '=', $grant[0])->first();
                if ($result) {
                    $grant = $result->title;
                }
            }

            $body =  str_replace("*name*",  $user->name, $email->contents);
            $body =  str_replace("*grant_title*", $grant, $body);
            $body =  str_replace("*old_project_id*", $project->old_project_id, $body);
            $body =  str_replace("*duedate*", $dateString, $body);
            $body =  str_replace("*reply*", '<a href="' . route('acceptProposal', ['r_id' => $project_reviewer->id]) . '"> This Link</a>', $body);

            $mail->isHTML(true);
            $mail->Subject = $email->subject;
            $mail->Body = $body. $email->signature;
            $mail->clearAttachments();
       //     $mail->addAttachment($aatchment);    // Add attachments
            //      $mail->addAttachment($aatchment2);    // Add attachments
            if ($mail->send()) {
                DB::table('email_sending_status')->insert([
                    'title' => $email->subject,
                    'body' =>  $body,
                    'email' => $user->email,
                    'category' => 'Reviewer Assigned',
                    'error_message' => '',
                    'sending_status' => 'Success'
                ]);
            } else {
                $errorMessage = $mail->ErrorInfo;
                DB::table('email_sending_status')->insert([
                    'title' => $email->subject,
                    'body' =>  $body,
                    'email' =>  $user->email,
                    'category' => 'Reviewer Assigned',
                    'error_message' => $errorMessage,
                    'sending_status' => 'Failed'
                ]);
            }
        } catch (Exception $e) {
            $errorMessage = $mail->ErrorInfo;
            DB::table('email_sending_status')->insert([
                'title' => $email->subject,
                'body' =>  $body,
                'email' =>  $user->email,
                'category' => 'Reviewer Assigned',
                'error_message' => $errorMessage,
                'sending_status' => 'Failed'
            ]);
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }
    }

    public function project_added($user)
    {

        $record = User::find($user);

        $data = [
            'title' => 'Project Registered',
            'description' => 'Your project has been registered in RTS. Kindly add the progress',
            'link' => '<a href="' . route('cycles') . '"> This Link</a>',
            'user_id' => $record->id,
            'date' => now(),
            'isentertained' => false
        ];

        Alerts::create($data);


        $email = DB::table('email_content')
            ->select('*')
            ->where('id', '=', 2)
            ->first();
        $smtp = DB::table('email_settings')->find(1);
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $smtp->host;
            $mail->SMTPAuth = true;
            $mail->Username =  $smtp->username;
            $mail->Password =  $smtp->password;
            $mail->SMTPSecure =  $smtp->smtp_secure;
            $mail->Port = $smtp->port;
            $mail->clearAttachments();
            $aatchment = storage_path('app\downloads\rts_progress_upload_tutorial.pdf');
            $mail->addAttachment($aatchment);
            $mail->setFrom($smtp->setfrom_email, $smtp->setfrom_name);
            $mail->addAddress($record->email);
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $mail->isHTML(true);
            $mail->Subject = $email->subject;
            $mail->Body = $email->title . " " . $record->name . ", <br>" . $email->contenta . "\n" . $email->contentb;
            if ($mail->send()) {
                DB::table('email_sending_status')->insert([
                    'title' => $email->subject,
                    'body' =>   $mail->Body,
                    'email' => $record->email,
                    'category' => 'Project Added',
                    'error_message' => '',
                    'sending_status' => 'Success'
                ]);
            } else {
                $errorMessage = $mail->ErrorInfo;
                DB::table('email_sending_status')->insert([
                    'title' => $email->subject,
                    'body' =>   $mail->Body,
                    'email' => $record->email,
                    'category' => 'Project Added',
                    'error_message' => $errorMessage,
                    'sending_status' => 'Success'
                ]);
            }
        } catch (Exception $e) {
            $errorMessage = $mail->ErrorInfo;
            DB::table('email_sending_status')->insert([
                'title' => $email->subject,
                'body' =>   $mail->Body,
                'email' => $record->email,
                'category' => 'Project Added',
                'error_message' => $errorMessage,
                'sending_status' => 'Success'
            ]);
        }
    }

    public function emailSetting()
    {
        $emails = DB::table('email_template')
            ->select('*')
            ->get();
        $emails = json_decode($emails, true);
        return view('emailSetting', ['emails' => $emails]);
    }

    public function emailEdit($id)
    {
        $email = DB::table('email_template')->find($id);
        return view('emailEdit', ['email' => $email]);
    }

    public function emailUpdate(Request $request, $id)
    {
        $request->validate([

            'subject' => 'required',
            'contents' => 'required',
            'signature' => 'required',


        ]);
        $email = EmailTemplate::find($id);
        $email->contents = $request->input('contents');
        $email->subject = $request->input('subject');
        $email->signature = $request->input('signature');

        $email->update();

        return redirect()->back()->with('emailtemplatesuccess', ' Email Template updated successfully');
    }

    public function emailNew(Request $request)
    {
        $request->validate([

            'subject' => 'required',
            'contents' => 'required',
            'signature' => 'required',


        ]);

        EmailTemplate::create([
            "contents" =>  $request->input('contents'),
            "subject" =>  $request->input('subject'),
            "signature" =>  $request->input('signature')
        ]);



        return redirect()->back()->with('emailtemplatesuccess', ' Email Template updated successfully');
    }

    public function smtpSettings()
    {

        $settings = DB::table('email_settings')->find(1);

        return view('smtpSettings', ['settings' => $settings]);
    }

    public function savesmtpSettings(Request $request)
    {
        $request->validate([

            'host' => 'required',
            'smtp_auth' => 'required',
            'smtp_secure' => 'required',
            'username' => 'required',
            'password' => 'required',
            'setfrom_name' => 'required',
            'setfrom_email' => 'required',
            'port' => 'required',


        ]);
        $email = EmailSMTP::find(1);
        $email->host = $request->input('host');
        $email->smtp_auth = $request->input('smtp_auth');
        $email->smtp_secure = $request->input('smtp_secure');

        $email->port = $request->input('port');
        $email->username = $request->input('username');
        $email->password = $request->input('password');

        $email->setfrom_name = $request->input('setfrom_name');
        $email->setfrom_email = $request->input('setfrom_email');


        $email->update();

        return redirect()->back()->with('successsmtp', ' SMTP settings updated successfully');
    }

    public function ajaxEmailSendingStatus()
    {
        $data = DB::table('email_sending_status')->orderBy('id', 'desc')
            ->where('processed', '=', '0')
            ->get();
        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $url = route('MarkEmailProcessed', ['id' => $row->id]);
                $Btn = '<a href="' . $url . '" class="btn btn-teal btn-sm">Mark as processed</a>';

                return   $Btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function MarkEmailProcessed($id)
    {
        DB::table('email_sending_status')
            ->where('id', $id)
            ->update(['processed' => 1]);
        return redirect()->back()->with('successmarkprocess', 'Email with id: <b>' . $id . '</b> Marked as processed');
    }

    public function SummaryEmail(Request $request)
    {

        $type = $request->input('submit_id');

        $email = DB::table('email_template')
            ->select('*')
            ->where('id', '=', $type)
            ->first();

        $summary = DB::table('admin_progress')
            ->select('*')
            ->where('id', '=', $request->input('cycle'))
            ->get();

        $arr = [];

        foreach ($summary as $item) {
            if (($type == 10 && $item->registration == 'No')
                or ($type == 11 && ($item->outcomes == 0 or $item->students == 0 or $item->contribution == 0))
                or ($type == 12 && $item->progress_report == 'No')
                or ($type == 13 && $item->final_report == 'No')
                or ($type == 14 && $item->readiness_report == 'No')
                or ($type == 15 && $item->progress_grading == 0)
                or ($type == 16 && $item->final_grading == 0)
            ) {

                array_push($arr, $item->old_project_id);

                if ($type < 15 and $item->project_id<>null) //its LPI
                {
                    $lpi = DB::table('projects')
                        ->join('users', 'users.id', '=', 'projects.user_id')
                        ->select('users.email', 'users.name')
                        ->where('projects.id', '=', $item->project_id)
                        ->first();


                    $body =  str_replace("*name*",  $lpi->name, $email->contents);
                    $body =  str_replace("*old_project_id*",  $item->old_project_id, $body);


                    $smtp = DB::table('email_settings')->find(1);
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = $smtp->host;
                        $mail->SMTPAuth = true;
                        $mail->Username =  $smtp->username;
                        $mail->Password =  $smtp->password;
                        $mail->SMTPSecure =  $smtp->smtp_secure;
                        $mail->Port = $smtp->port;

                        $mail->AddEmbeddedImage(public_path('images/signature/logo.png'), "logo", "logo.png");
                        $mail->AddEmbeddedImage(public_path('images/signature/facebook.png'), "facebook", "facebook.png");
                        $mail->AddEmbeddedImage(public_path('images/signature/twitter.png'), "twitter", "twitter.png");
                        $mail->AddEmbeddedImage(public_path('images/signature/youtube.png'), "youtube", "youtube.png");
                        $mail->AddEmbeddedImage(public_path('images/signature/instagram.png'), "instagram", "instagram.png");
                        $mail->AddEmbeddedImage(public_path('images/signature/linkedin.png'), "linkedin", "linkedin.png");
                        $mail->AddEmbeddedImage(public_path('images/signature/iso.png'), "iso", "iso.png");


                        $mail->setFrom($smtp->setfrom_email, $smtp->setfrom_name);
                        $mail->clearAddresses();
                        $mail->addAddress($lpi->email);
                        $mail->SMTPOptions = array(
                            'ssl' => array(
                                'verify_peer' => false,
                                'verify_peer_name' => false,
                                'allow_self_signed' => true
                            )
                        );

                        $mail->isHTML(true);
                        $mail->Subject = $email->subject;
                        $mail->Body = $body  . $email->signature;
                        //  $mail->send();

                        // dd($mail);
                    } catch (Exception $e) {
                        echo 'Message could not be sent.';
                        echo 'Mailer Error: ' . $mail->ErrorInfo;
                    }
                } else {
                    $reviewers = DB::table('projects_reviewers')
                        ->leftJoin('users', 'users.id', '=', 'projects_reviewers.user_id')
                        ->select('users.email', 'users.name')
                        ->where('projects_reviewers.project_id', '=', $item->project_id)
                        ->get();

                    foreach ($reviewers as $reviewer) {

                        $body =  str_replace("*name*",  $reviewer->name, $email->contents);
                        $body =  str_replace("*old_project_id*",  $item->old_project_id, $body);


                        $smtp = DB::table('email_settings')->find(1);
                        $mail = new PHPMailer(true);
                        try {
                            $mail->isSMTP();
                            $mail->Host = $smtp->host;
                            $mail->SMTPAuth = true;
                            $mail->Username =  $smtp->username;
                            $mail->Password =  $smtp->password;
                            $mail->SMTPSecure =  $smtp->smtp_secure;
                            $mail->Port = $smtp->port;

                            $mail->AddEmbeddedImage(public_path('images/signature/logo.png'), "logo", "logo.png");
                            $mail->AddEmbeddedImage(public_path('images/signature/facebook.png'), "facebook", "facebook.png");
                            $mail->AddEmbeddedImage(public_path('images/signature/twitter.png'), "twitter", "twitter.png");
                            $mail->AddEmbeddedImage(public_path('images/signature/youtube.png'), "youtube", "youtube.png");
                            $mail->AddEmbeddedImage(public_path('images/signature/instagram.png'), "instagram", "instagram.png");
                            $mail->AddEmbeddedImage(public_path('images/signature/linkedin.png'), "linkedin", "linkedin.png");
                            $mail->AddEmbeddedImage(public_path('images/signature/iso.png'), "iso", "iso.png");


                            $mail->setFrom($smtp->setfrom_email, $smtp->setfrom_name);
                            $mail->clearAddresses();
                            $mail->addAddress($reviewer->email);
                            $mail->SMTPOptions = array(
                                'ssl' => array(
                                    'verify_peer' => false,
                                    'verify_peer_name' => false,
                                    'allow_self_signed' => true
                                )
                            );

                            $mail->isHTML(true);
                            $mail->Subject = $email->subject;
                            $mail->Body = $body  . $email->signature;
                            //$mail->send();

                            //     dd($mail);
                        } catch (Exception $e) {
                            echo 'Message could not be sent.';
                            echo 'Mailer Error: ' . $mail->ErrorInfo;
                        }
                    }
                }
            }
        }

          dd($arr);
    }
}
