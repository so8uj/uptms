<?php

namespace App\Controllers;

use App\Models\Setting;
use App\Services\Crypt;
use App\Services\Mailer;
use App\Services\Validation;

/**
 * Called by api/settings.php (admin only). Works with data and returns arrays.
 */
class SettingController {

    // The settings of the page, in the order of the form.
    private const NAMES = [
        'institution_name', 'department_name', 'support_email',
        'team_min_size', 'team_max_size', 'panel_signatures_needed',
        'login_max_attempts', 'login_lock_minutes',
        'maintenance_mode', 'maintenance_message',
        'mail_enabled', 'mail_host', 'mail_port', 'mail_encryption', 'mail_username', 'mail_from_email', 'mail_from_name',
    ];

    // What a setting is when it was never saved (the mail box starts switched off).
    private const DEFAULTS = ['mail_enabled' => '0', 'mail_port' => '587', 'mail_encryption' => 'tls'];

    public static function show(){
        $values = [];
        foreach (self::NAMES as $name) {
            $values[$name] = Setting::get($name, self::DEFAULTS[$name] ?? '');
        }

        // The SMTP password is never sent back to the page: it only says whether one is saved.
        // The department switch lives in .env (it changes the whole project), so the page only shows it.
        return ['settings' => $values, 'multi_department' => multi_department(), 'mail_password_set' => Setting::get('mail_password') !== ''];
    }

    public static function save($request){

        $validate = Validation::validate($request, [
            'institution_name'    => 'required|max:150',
            'department_name'     => 'required|max:150',
            'support_email'       => 'email|max:150',
            'team_min_size'       => 'required|integer|between:1,10',
            'team_max_size'       => 'required|integer|between:1,10',
            'panel_signatures_needed' => 'required|integer|between:1,3',
            'login_max_attempts'  => 'required|integer|between:3,10',
            'login_lock_minutes'  => 'required|integer|between:1,60',
            'maintenance_message' => 'max:300',
            'mail_host'           => 'max:150',
            'mail_port'           => 'integer|between:1,65535',
            'mail_encryption'     => 'in:tls,ssl,none',
            'mail_username'       => 'max:150',
            'mail_from_email'     => 'email|max:150',
            'mail_from_name'      => 'max:100',
            'mail_password'       => 'max:200',
        ]);

        if (!$validate['success']) {
            $validate['message'] = 'Please fix the errors below.';
            return $validate;
        }

        if ((int) $request['team_min_size'] > (int) $request['team_max_size']) {
            return [
                'success' => false,
                'errors'  => ['team_max_size' => ['The largest group size cannot be smaller than the smallest.']],
                'message' => 'Please fix the errors below.',
            ];
        }

        // Switching the email on needs the server and the sender.
        $mailOn = !empty($request['mail_enabled']);
        $mailErrors = [];
        if ($mailOn) {
            foreach (['mail_host' => 'The mail server (host)', 'mail_port' => 'The port', 'mail_from_email' => 'The sender email'] as $field => $label) {
                if (trim((string) ($request[$field] ?? '')) === '') {
                    $mailErrors[$field] = [$label . ' is needed to switch the email on.'];
                }
            }
        }
        if ($mailErrors) {
            return ['success' => false, 'errors' => $mailErrors, 'message' => 'Please fix the errors below.'];
        }

        $values = [];
        foreach (self::NAMES as $name) {
            $values[$name] = trim((string) ($request[$name] ?? ''));
        }
        $values['maintenance_mode'] = !empty($request['maintenance_mode']) ? '1' : '0';
        $values['mail_enabled']     = $mailOn ? '1' : '0';

        // The SMTP password: empty means "keep the saved one".
        $newPassword = (string) ($request['mail_password'] ?? '');
        if ($newPassword !== '') {
            $values['mail_password'] = Crypt::encrypt($newPassword);
        }

        Setting::saveMany($values);

        return ['success' => true, 'errors' => [], 'message' => 'Settings saved.'];
    }

    // The "Send test email" button: sends one mail with the SAVED mail settings (even when sending is switched off).
    public static function testMail($request, $actor){

        $to = trim((string) ($request['to'] ?? ''));
        if ($to === '') {
            $to = (string) ($actor['email'] ?? '');
        }
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'errors' => ['mail_test_to' => ['Please write a valid email address.']], 'message' => 'Please write a valid email address.'];
        }

        $site = Setting::get('institution_name', 'UPTMS');
        $mail = Mailer::send(
            $to, '', 'Test email from ' . $site,
            '<p>This is a test email from the Project &amp; Thesis Management System. Your mail setup works.</p>',
            "This is a test email from the Project & Thesis Management System. Your mail setup works.\n",
            true
        );

        return $mail['success']
            ? ['success' => true, 'errors' => [], 'message' => 'The test email was sent to ' . $to . '.']
            : ['success' => false, 'errors' => [], 'message' => 'The test email could not be sent: ' . $mail['error']];
    }
}
