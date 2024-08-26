<?php

namespace RealtimeRegister\Services;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class MailService
{
    /**
     * Send email with PHPMailer to custom email address (WHMCS API functions
     * accept only userid as mail receiver).
     *
     * @throws Exception
     * @global $whmcs
     */
    public static function mail(string $email, string $subject, string $message): bool
    {
        global $whmcs;

        $mail = new PHPMailer();

        $mailConfig = json_decode(decrypt($whmcs->get_config('MailConfig'), $GLOBALS['cc_encryption_hash']), true);

        if (is_array($mailConfig)) {
            if ($mailConfig['module'] == 'SmtpMail' || $mailConfig['module'] == 'Mailgun') {
                $mail->isSMTP();
                $mail->SMTPAuth = true;
                $mail->Host = $mailConfig['configuration']['host'];
                $mail->Username = $mailConfig['configuration']['username'];
                $mail->Password = $mailConfig['configuration']['password'];

                if ($mailConfig['configuration']['secure'] !== '') {
                    $mail->SMTPSecure = $mailConfig['configuration']['secure'];
                }

                $mail->Port = $mailConfig['configuration']['port'];
            } elseif ($mailConfig['module'] == 'SendGrid') {
                $mail->isSMTP();
                $mail->Port = 587;
                $mail->SMTPAuth = true;
                $mail->Username = 'apikey';
                $mail->Password = $mailConfig['configuration']['key'];
                $mail->Host = "smtp.sendgrid.net";
                $mail->SMTPSecure = "tls";
            }
            $whmcs_mail_encoding = [
                0 => '8bit',
                1 => '7bit',
                2 => 'binary',
                3 => 'base64',
                4 => 'quoted-printable',
            ];

            $mail->Encoding = $whmcs_mail_encoding[$mailConfig['configuration']['encoding']];
            $mail->From = $whmcs->get_config('Email');
            $mail->FromName = 'Realtimeregister module';
            $mail->addAddress($email);
            $mail->isHTML();

            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->AltBody = strip_tags(preg_replace('/\<br(\s*)?\/?\>/i', "\n", $message));

            if (!$mail->send()) {
                logActivity(sprintf('RealTimeRegister error sending email to %s', $email));
                return false;
            } else {
                logActivity(sprintf('RealTimeRegister sent email `%s` to %s', $subject, $email));
                return true;
            }
        }
    }
}
