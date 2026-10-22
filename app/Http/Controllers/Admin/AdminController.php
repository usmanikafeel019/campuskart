<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Material;
use App\Models\Report;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', ['stats' => ['Students' => User::where('role', 'student')->count(), 'Listings' => Material::count(), 'Available' => Material::where('status', 'available')->count(), 'Sold' => Material::where('status', 'sold')->count(), 'Enquiries' => Enquiry::count(), 'Pending reports' => Report::where('status', 'pending')->count()]]);
    }

    public function users(Request $r)
    {
        return view('admin.users', ['users' => User::where('role', 'student')->where(fn ($q) => $q->where('name', 'like', '%'.$r->input('q').'%')->orWhere('email', 'like', '%'.$r->input('q').'%'))->latest()->paginate(20)->withQueryString()]);
    }

    public function toggle(User $user)
    {
        abort_unless($user->role === 'student', 403);
        $user->is_active = ! $user->is_active;
        $user->save();

        return back()->with('success', 'Account access updated. Suspended sellers are hidden.');
    }

    public function materials()
    {
        return view('admin.materials', ['materials' => Material::with('user')->latest()->paginate(20)]);
    }

    private function model($kind)
    {
        abort_unless(in_array($kind, ['categories', 'subjects']), 404);

        return $kind === 'categories' ? Category::class : Subject::class;
    }

    public function taxonomy(string $kind)
    {
        $model = $this->model($kind);

        return view('admin.taxonomy', ['items' => $model::orderBy('name')->paginate(20), 'kind' => $kind]);
    }

    public function save(Request $r, string $kind, ?int $id = null)
    {
        $model = $this->model($kind);
        $item = $id ? $model::findOrFail($id) : new $model;
        $d = $r->validate(['name' => ['required', 'string', 'max:100', Rule::unique($kind)->ignore($item->id)], 'description' => 'nullable|string|max:1000']);
        if ($kind === 'subjects') {
            unset($d['description']);
        }$item->fill($d)->save();

        return back()->with('success', 'Saved.');
    }

    public function remove(string $kind, int $id)
    {
        $model = $this->model($kind);
        $item = $model::findOrFail($id);
        if ($item->materials()->exists()) {
            return back()->withErrors(['name' => 'Cannot delete an option used by listings.']);
        }try {
            $item->delete();
        } catch (QueryException $e) {
            return back()->withErrors(['name' => 'Option is in use.']);
        }

return back()->with('success', 'Deleted.');
    }

    public function reports()
    {
        return view('admin.reports', ['reports' => Report::with(['material', 'reporter'])->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")->latest()->paginate(20)]);
    }

    public function report(Request $r, Report $report)
    {
        $report->update($r->validate(['status' => 'required|in:pending,reviewed,resolved']));

        return back()->with('success','Report updated.');
    }
}
