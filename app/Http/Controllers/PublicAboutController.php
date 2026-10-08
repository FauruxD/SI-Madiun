<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class PublicAboutController extends Controller
{
    public function __invoke(): View
    {
        return view('public.about');
    }
}
