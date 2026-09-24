<?php

namespace App\Modules\Home\Http\Controllers;

use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return redirect()->route('consent.index');
    }
}
