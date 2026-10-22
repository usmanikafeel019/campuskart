<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $r, Material $material)
    {
        abort_unless($r->user()->role === 'student' && $material->user_id !== $r->user()->id, 403);
        $d = $r->validate(['reason' => 'required|string|min:10|max:2000']);
        $material->reports()->firstOrCreate(['reporter_id' => $r->user()->id], $d);

        return back()->with('success', 'Report submitted for review.');
    }
}
