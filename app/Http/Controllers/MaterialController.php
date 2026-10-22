<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaterialRequest;
use App\Models\Category;
use App\Models\Material;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    public function index(Request $r)
    {
        $r->validate(['min' => 'nullable|numeric|min:0', 'max' => 'nullable|numeric|min:0|gte:min', 'semester' => 'nullable|integer|between:1,8', 'q' => 'nullable|string|max:150']);
        $q = Material::with(['user', 'category', 'subject'])->where('status', 'available')->whereHas('user', fn ($q) => $q->where('is_active', true));
        if ($r->filled('q')) {
            $term = '%'.$r->input('q').'%';
            $q->where(fn ($b) => $b->where('title', 'like', $term)->orWhere('description', 'like', $term)->orWhereHas('subject', fn ($s) => $s->where('name', 'like', $term)));
        }
        foreach (['category_id', 'subject_id', 'semester', 'condition'] as $key) {
            if ($r->filled($key)) {
                $q->where($key, $r->input($key));
            }
        }
        if ($r->filled('min')) {
            $q->where('price', '>=', $r->input('min'));
        }if ($r->filled('max')) {
            $q->where('price', '<=', $r->input('max'));
        }
        [$col,$dir] = match ($r->input('sort')) {
            'oldest' => ['created_at', 'asc'],'price_asc' => ['price', 'asc'],'price_desc' => ['price', 'desc'],default => ['created_at', 'desc']
        };

        return view('materials.index', ['materials' => $q->orderBy($col, $dir)->orderBy('id', 'desc')->paginate(12)->withQueryString(), 'categories' => Category::orderBy('name')->get(), 'subjects' => Subject::orderBy('name')->get()]);
    }

    public function show(Material $material)
    {
        abort_unless($material->user->is_active || auth()->id() === $material->user_id || auth()->user()?->role === 'admin', 404);

        return view('materials.show', compact('material'));
    }

    public function create()
    {
        Gate::authorize('create', Material::class);

        return $this->form(new Material);
    }

    public function edit(Material $material)
    {
        Gate::authorize('update', $material);

        return $this->form($material);
    }

    private function form($material)
    {
        return view('materials.form', ['material' => $material, 'categories' => Category::all(), 'subjects' => Subject::all()]);
    }

    public function store(MaterialRequest $r)
    {
        Gate::authorize('create', Material::class);
        $d = $r->safe()->except(['image', 'permission']);
        if ($r->hasFile('image')) {
            $d['image_path'] = $r->file('image')->store('materials', 'public');
        }try {
            $m = $r->user()->materials()->create($d);
        } catch (\Throwable $e) {
            if (isset($d['image_path'])) {
                Storage::disk('public')->delete($d['image_path']);
            }throw $e;
        }

return redirect()->route('materials.show', $m)->with('success', 'Listing published.');
    }

    public function update(MaterialRequest $r, Material $material)
    {
        Gate::authorize('update', $material);
        $d = $r->safe()->except(['image', 'permission']);
        $old = $material->image_path;
        if ($r->hasFile('image')) {
            $d['image_path'] = $r->file('image')->store('materials', 'public');
        }try {
            $material->update($d);
        } catch (\Throwable $e) {
            if (isset($d['image_path'])) {
                Storage::disk('public')->delete($d['image_path']);
            }throw $e;
        }if (isset($d['image_path']) && $old) {
            Storage::disk('public')->delete($old);
        }

return redirect()->route('materials.show', $material)->with('success', 'Listing updated.');
    }

    public function destroy(Material $material)
    {
        Gate::authorize('delete', $material);
        $path = $material->image_path;
        $material->delete();
        if ($path) {
            Storage::disk('public')->delete($path);
        }

return redirect(auth()->user()->role === 'admin' ? '/admin/materials' : '/my-listings')->with('success', 'Listing removed.');
    }

    public function sold(Material $material)
    {
        Gate::authorize('update', $material);
        DB::transaction(function () use ($material) {
            $m = Material::lockForUpdate()->findOrFail($material->id);
            $m->update(['status' => 'sold']);
            $m->enquiries()->update(['status' => 'closed']);
        });

        return back()->with('success', 'Marked as sold. Enquiries closed.');
    }

    public function mine(Request $r)
    {
        return view('materials.mine',['materials' => $r->user()->materials()->latest()->paginate(12)]);
    }
}
