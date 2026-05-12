<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/admin/polls';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required'      => 'Naam is verplicht.',
            'email.required'     => 'E-mailadres is verplicht.',
            'email.email'        => 'Vul een geldig e-mailadres in.',
            'email.unique'       => 'Dit e-mailadres is al in gebruik.',
            'password.required'  => 'Wachtwoord is verplicht.',
            'password.min'       => 'Wachtwoord moet minimaal 8 tekens bevatten.',
            'password.confirmed' => 'Wachtwoorden komen niet overeen.',
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => false,
        ]);
    }
}
