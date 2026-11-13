<?php

namespace App\Services;

use App\Models\Setting;
use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Sends email through the SMTP server that the admin set up on the Settings page.
 * Sending is OFF until the admin switches "Send emails" on. A mail error never stops the action that asked for it:
 * every method answers ['success' => bool, 'error' => text, 'skipped' => bool].
 */
class Mailer
{
    // Is sending switched on and the server filled in?
    public static function enabled(): bool
    {
        return Setting::bool('mail_enabled') && self::configured();
    }

    public static function configured(): bool
    {
        return Setting::get('mail_host') !== '' && Setting::get('mail_from_email') !== '';
    }

    /**
     * $force = send even when the switch is off (the "Send test email" button).
     */
    public static function send(string $to, string $toName, string $subject, string $html, string $text, bool $force = false): array
    {
        if (!$force && !self::enabled()) {
            return ['success' => false, 'skipped' => true, 'error' => 'Email sending is switched off.'];
        }
        if (!self::configured()) {
            return ['success' => false, 'skipped' => false, 'error' => 'The mail server is not set up yet.'];
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host    = Setting::get('mail_host');
            $mail->Port    = Setting::int('mail_port', 587);
            $mail->Timeout = 10;
            $mail->CharSet = 'UTF-8';

            $encryption = Setting::get('mail_encryption', 'tls');
            if ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure  = '';
                $mail->SMTPAutoTLS = false;
            }

            $username = Setting::get('mail_username');
            if ($username !== '') {
                $mail->SMTPAuth = true;
                $mail->Username = $username;
                $mail->Password = Crypt::decrypt(Setting::get('mail_password'));
            }

            $mail->setFrom(Setting::get('mail_from_email'), Setting::get('mail_from_name') ?: Setting::get('institution_name', 'UPTMS'));
            $mail->addAddress($to, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = $text;
            $mail->send();

            return ['success' => true, 'skipped' => false, 'error' => ''];
        } catch (MailException $e) {
            return ['success' => false, 'skipped' => false, 'error' => $mail->ErrorInfo ?: $e->getMessage()];
        } catch (\Throwable $e) {
            return ['success' => false, 'skipped' => false, 'error' => $e->getMessage()];
        }
    }

    // The login details of a new account: the address of the site, the email and the default password.
    public static function sendCredentials(string $name, string $email, string $role, string $password): array
    {
        $site  = Setting::get('institution_name', 'UPTMS');
        $link  = url('index.php');
        $label = ['admin' => 'an administrator', 'teacher' => 'a teacher', 'student' => 'a student'][$role] ?? $role;

        $e = fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        $text = "Hello {$name},\n\n"
            . "An account was created for you as {$label} in the Project & Thesis Management System of {$site}.\n\n"
            . "Login page: {$link}\nEmail: {$email}\nPassword: {$password}\n\n"
            . "This is a first password. You must choose a new one when you log in for the first time.\n";

        $html = '<p>Hello ' . $e($name) . ',</p>'
            . '<p>An account was created for you as <strong>' . $e($label) . '</strong> in the Project &amp; Thesis Management System of ' . $e($site) . '.</p>'
            . '<table cellpadding="6" style="border-collapse:collapse">'
            . '<tr><td><strong>Login page</strong></td><td><a href="' . $e($link) . '">' . $e($link) . '</a></td></tr>'
            . '<tr><td><strong>Email</strong></td><td>' . $e($email) . '</td></tr>'
            . '<tr><td><strong>Password</strong></td><td>' . $e($password) . '</td></tr></table>'
            . '<p>This is a first password. <strong>You must choose a new one when you log in for the first time.</strong></p>';

        return self::send($email, $name, 'Your account for ' . $site, $html, $text);
    }
}
