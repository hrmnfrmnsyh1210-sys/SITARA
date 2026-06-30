<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::where('school_id', auth()->user()->school_id)
            ->latest()->paginate(10);

        return view('admin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('admin.announcements.form', ['announcement' => new Announcement]);
    }

    public function store(Request $request)
    {
        Announcement::create($this->validateData($request) + [
            'school_id' => auth()->user()->school_id,
            'user_id' => auth()->id(),
            'published_at' => now(),
        ]);

        return redirect()->route('admin.announcements.index')->with('success', 'Pengumuman dipublikasikan.');
    }

    public function edit(Announcement $announcement)
    {
        abort_unless($announcement->school_id === auth()->user()->school_id, 403);

        return view('admin.announcements.form', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        abort_unless($announcement->school_id === auth()->user()->school_id, 403);
        $announcement->update($this->validateData($request));

        return redirect()->route('admin.announcements.index')->with('success', 'Pengumuman diperbarui.');
    }

    public function destroy(Announcement $announcement)
    {
        abort_unless($announcement->school_id === auth()->user()->school_id, 403);
        $announcement->delete();

        return back()->with('success', 'Pengumuman dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'target' => ['required', 'in:all,teachers,students'],
            'is_published' => ['nullable'],
        ]) + ['is_published' => $request->boolean('is_published')];
    }
}
