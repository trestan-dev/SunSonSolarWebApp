<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        if (!session()->get('user_id')) {
            return redirect()->to('login');
        }

        return view('home', [
            'name' => session()->get('user_name') ?? 'User',
        ]);
    }
}
