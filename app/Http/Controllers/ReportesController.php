<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportesController extends Controller
{
    /**
     * Centro de reportes (PDF y Excel) para administración y coordinación (no instructores).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user || $user->isInstructor()) {
            abort(403);
        }

        if (! ($user->isAdmin() || $user->isCoordinatorL() || $user->isCoordinatorOnly())) {
            abort(403);
        }

        return view('reportes.index');
    }
}
