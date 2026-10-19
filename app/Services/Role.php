<?php

namespace App\Services;

class Role {

    public static function get_roles(){
        $roles = [
            'admin' => 'Admin',
            'teacher' => 'Teacher',
            'student' => 'Student'
        ];
        return $roles;
    }

}
