<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Console\Command;

class SyncProfileNames extends Command
{
    protected $signature = 'profiles:sync {--dry-run : Tampilkan perubahan tanpa menyimpan}';

    protected $description = 'Sinkronkan name & phone dari tabel users ke record student/teacher yang terkait (perbaiki data lama yang tidak sinkron).';

    public function handle(): int
    {
        $dry = $this->option('dry-run');
        $updated = 0;

        foreach ([Student::class, Teacher::class] as $model) {
            $label = class_basename($model);

            $model::with('user')->chunkById(200, function ($records) use (&$updated, $dry, $label) {
                foreach ($records as $record) {
                    $user = $record->user;
                    if (! $user) {
                        continue;
                    }

                    $changes = [];
                    if ($user->name && $record->name !== $user->name) {
                        $changes['name'] = $user->name;
                    }
                    // Only sync phone when users has a value — never wipe an existing
                    // profile phone with an empty users.phone.
                    if (! empty($user->phone) && $record->phone !== $user->phone) {
                        $changes['phone'] = $user->phone;
                    }
                    // Photo: users.avatar -> student/teacher.photo (only when set).
                    if (! empty($user->avatar) && $record->photo !== $user->avatar) {
                        $changes['photo'] = $user->avatar;
                    }

                    if (! $changes) {
                        continue;
                    }

                    $this->line(sprintf(
                        '%s #%d: %s',
                        $label,
                        $record->id,
                        collect($changes)->map(fn ($v, $k) => "$k: \"{$record->$k}\" → \"$v\"")->implode(', ')
                    ));

                    if (! $dry) {
                        $record->update($changes);
                    }
                    $updated++;
                }
            });
        }

        $this->newLine();
        $this->info($dry
            ? "$updated record akan disinkronkan (dry-run, belum disimpan). Jalankan tanpa --dry-run untuk menerapkan."
            : "$updated record berhasil disinkronkan.");

        return self::SUCCESS;
    }
}
