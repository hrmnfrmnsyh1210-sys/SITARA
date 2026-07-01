<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $schools = School::query()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('npsn', 'like', "%$s%"))
            ->when($request->status !== null && $request->status !== '', fn ($q) => $q->where('is_active', $request->status))
            ->withCount(['teachers', 'students'])
            ->orderBy($request->sort ?? 'name', $request->dir ?? 'asc')
            ->paginate(10)->withQueryString();

        return view('superadmin.schools.index', compact('schools'));
    }

    public function create()
    {
        return view('superadmin.schools.form', ['school' => new School]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['logo'] = $this->handleLogo($request);
        School::create($data);

        return redirect()->route('superadmin.schools.index')->with('success', 'Sekolah berhasil ditambahkan.');
    }

    public function edit(School $school)
    {
        return view('superadmin.schools.form', compact('school'));
    }

    public function update(Request $request, School $school)
    {
        $data = $this->validateData($request);
        if ($logo = $this->handleLogo($request, $school)) {
            $data['logo'] = $logo;
        }

        $wasActive = $school->is_active;
        $school->update($data);

        // Nonaktif -> bekukan sisa langganan; Aktif kembali -> lanjutkan sisa hari.
        if ($wasActive && ! $school->is_active) {
            $school->freezeSubscription();
        } elseif (! $wasActive && $school->is_active) {
            $school->thawSubscription();
        }

        return redirect()->route('superadmin.schools.index')->with('success', 'Sekolah berhasil diperbarui.');
    }

    public function destroy(School $school)
    {
        $school->delete();

        return back()->with('success', 'Sekolah berhasil dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'npsn' => ['nullable', 'string', 'max:20'],
            'level' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email'],
            'website' => ['nullable', 'string', 'max:255'],
            'principal_name' => ['nullable', 'string', 'max:255'],
            'primary_color' => ['nullable', 'string', 'max:9'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }

    private function handleLogo(Request $request, ?School $school = null): ?string
    {
        $request->validate(['logo' => ['nullable', 'image', 'max:2048']]);
        if ($request->hasFile('logo')) {
            if ($school?->logo) {
                Storage::delete($school->logo);
            }
            return $request->file('logo')->store('logos');
        }
        return null;
    }
}
