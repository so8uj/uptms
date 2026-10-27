<?php

namespace App\Controllers;

use App\Models\Setting;
use App\Services\Validation;

/**
 * Called by api/settings.php (admin only). Works with data and returns arrays.
 */
class SettingController {

    // The settings of the page, in the order of the form.
    private const NAMES = [
        'institution_name', 'department_name', 'support_email',
        'team_min_size', 'team_max_size',
        'login_max_attempts', 'login_lock_minutes',
        'maintenance_mode', 'maintenance_message',
    ];

    public static function show(){
        $values = [];
        foreach (self::NAMES as $name) {
            $values[$name] = Setting::get($name);
        }

        // The department switch lives in .env (it changes the whole project), so the page only shows it.
        return ['settings' => $values, 'multi_department' => multi_department()];
    }

    public static function save($request){

        $validate = Validation::validate($request, [
            'institution_name'    => 'required|max:150',
            'department_name'     => 'required|max:150',
            'support_email'       => 'email|max:150',
            'team_min_size'       => 'required|integer|between:1,10',
            'team_max_size'       => 'required|integer|between:1,10',
            'login_max_attempts'  => 'required|integer|between:3,10',
            'login_lock_minutes'  => 'required|integer|between:1,60',
            'maintenance_message' => 'max:300',
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

        $values = [];
        foreach (self::NAMES as $name) {
            $values[$name] = trim((string) ($request[$name] ?? ''));
        }
        $values['maintenance_mode'] = !empty($request['maintenance_mode']) ? '1' : '0';

        Setting::saveMany($values);

        return ['success' => true, 'errors' => [], 'message' => 'Settings saved.'];
    }
}
