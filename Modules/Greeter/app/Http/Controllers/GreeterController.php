<?php

namespace Modules\Greeter\Http\Controllers;

use Illuminate\Routing\Controller;

class GreeterController extends Controller
{
    public function index()
    {
        return view('greeter::index');
    }
}
