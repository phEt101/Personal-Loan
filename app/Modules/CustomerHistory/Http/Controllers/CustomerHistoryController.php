<?php

namespace App\Modules\CustomerHistory\Http\Controllers;

use Illuminate\Routing\Controller;

class CustomerHistoryController extends Controller
{
    public function index()
    {
        return view('customerhistory::index');
    }
}