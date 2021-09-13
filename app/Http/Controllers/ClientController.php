<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }


    public function list()
    {
        return view('clients.list');
    }

    public function creditcards()
    {
        return view('clients.creditcards');
    }

    public function import()
    {
        return view('clients.import');
    }

    public function failed_import()
    {
        return view('clients.failed_import');
    }
}
