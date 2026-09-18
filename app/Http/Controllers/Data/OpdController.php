<?php

namespace App\Http\Controllers\Data;

use App\Http\Controllers\Controller;
use App\Models\SysdbInstansi;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OpdController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $opd = SysdbInstansi::query()
            ->whereNotNull('ins_insnam')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('ins_inscod', 'like', "%{$q}%")
                        ->orWhere('ins_insnam', 'like', "%{$q}%");
                });
            })
            ->orderBy('ins_insnam')
            ->paginate(25)
            ->withQueryString();

        return view('data.opd', ['opd' => $opd, 'q' => $q]);
    }
}
