<?php

use App\Console\Commands\ProfileSyncCommand;
use App\Models\Team;
use Illuminate\Support\Facades\Schedule;

Team::all()
    ->each(function (Team $team) {
        Schedule::command(ProfileSyncCommand::class, [$team->getUsername(), $team->getSyncAt()])
            ->everyMinute()
            ->appendOutputTo(storage_path('logs/profile-sync.log'))
            ->withoutOverlapping();
    });
