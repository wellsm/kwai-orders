<?php

use App\Console\Commands\ProfileSyncCommand;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

try {
    DB::connection()->getPdo();

    Team::all()
        ->each(function (Team $team) {
            Schedule::command(ProfileSyncCommand::class, [$team->getUsername(), $team->getSyncAt()])
                ->everyMinute()
                ->appendOutputTo(storage_path('logs/profile-sync.log'))
                ->withoutOverlapping();
        });
} catch (Exception) {}


