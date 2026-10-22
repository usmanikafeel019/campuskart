<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnquiryController extends Controller
{
    public function store(Request $r, Material $material)
    {
        abort_unless($r->user()->role === 'student', 403);
        $d = $r->validate(['message' => 'required|string|min:5|max:2000']);
        DB::transaction(function () use ($material, $r, $d) {
            $m = Material::lockForUpdate()->findOrFail($material->id);
            abort_if($m->user_id === $r->user()->id, 403, 'You cannot enquire on your own listing.');
            abort_unless($m->status === 'available' && $m->user->is_active, 422, 'This listing is unavailable.');
            $m->enquiries()->create($d + ['buyer_id' => $r->user()->id]);
        });

        return back()->with('success', 'Enquiry sent.');
    }

    public function index(Request $r)
    {
        $received = $r->routeIs('enquiries.received');
        $q = Enquiry::with(['material.user', 'buyer']);
        $received ? $q->whereHas('material', fn ($q) => $q->where('user_id', $r->user()->id)) : $q->where('buyer_id', $r->user()->id);

        return view('enquiries.index', ['enquiries' => $q->latest()->paginate(15), 'received' => $received]);
    }

    public function update(Request $r, Enquiry $enquiry)
    {
        abort_unless($enquiry->material->user_id === $r->user()->id, 403);
        $d = $r->validate(['status' => 'required|in:responded,closed', 'response' => 'nullable|string|max:2000|required_if:status,responded']);
        $enquiry->update($d);

        return back()->with('success','Enquiry updated.');
    }
}
