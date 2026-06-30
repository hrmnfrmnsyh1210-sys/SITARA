<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = Room::where('school_id', auth()->user()->school_id)
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%$s%"))
            ->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('admin.rooms.form', ['room' => new Room]);
    }

    public function store(Request $request)
    {
        Room::create($this->validateData($request) + ['school_id' => auth()->user()->school_id]);

        return redirect()->route('admin.rooms.index')->with('success', 'Ruangan ditambahkan.');
    }

    public function edit(Room $room)
    {
        abort_unless($room->school_id === auth()->user()->school_id, 403);

        return view('admin.rooms.form', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        abort_unless($room->school_id === auth()->user()->school_id, 403);
        $room->update($this->validateData($request));

        return redirect()->route('admin.rooms.index')->with('success', 'Ruangan diperbarui.');
    }

    public function destroy(Room $room)
    {
        abort_unless($room->school_id === auth()->user()->school_id, 403);
        $room->delete();

        return back()->with('success', 'Ruangan dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
