<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TabSessionController extends Controller
{
    /**
     * Registra una pestaña activa para esta sesión.
     */
    public function register(Request $request)
    {
        $tabId = $request->input('tab_id');
        if (empty($tabId)) {
            return response()->json(['ok' => false], 400);
        }
        $tabs = session('active_tabs', []);
        if (!in_array($tabId, $tabs, true)) {
            $tabs[] = $tabId;
            session(['active_tabs' => $tabs]);
        }
        return response()->json(['ok' => true]);
    }

    /**
     * Desregistra la pestaña. Si era la última, cierra la sesión.
     */
    public function unregister(Request $request)
    {
        $tabId = $request->input('tab_id');
        if (empty($tabId)) {
            return response()->noContent();
        }
        $tabs = session('active_tabs', []);
        $tabs = array_values(array_filter($tabs, fn($id) => $id !== $tabId));
        session(['active_tabs' => $tabs]);

        if (count($tabs) === 0) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->noContent();
    }
}
