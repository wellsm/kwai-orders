<?php

namespace App\Console\Commands;

use App\Jobs\ProfileSync;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProfileSyncCommand extends Command
{
    protected $signature = 'app:profile:sync {profile} {time?}';

    protected $description = 'Sync Kwai Profile';

    private const OPTION_PROFILE = 'profile';
    private const OPTION_TIME    = 'time';

    

    public function handle()
    {
        $start   = microtime(true);
        $profile = $this->argument(self::OPTION_PROFILE);

        if (empty($profile)) {
            return 0;
        }

        $time   = $this->argument(self::OPTION_TIME) ?: null;
        $minute = now()->startOfMinute();

        if (
            isset($time)
            && Carbon::parse($time)->startOfMinute()->notEqualTo($minute)
        ) {
            return 0;
        }

        $team = Team::query()->where('username', $profile)->firstOrFail();
        $time = $time ?? $team->getSyncAt();

        if (
            empty($time)
            || Carbon::parse($time)->startOfMinute()->notEqualTo($minute)
            || $team->getSyncedAt()?->isToday()
        ) {
            return 0;
        }

        ProfileSync::dispatchSync($team);

        $this->info(date('Y-m-d H:i:s') . ' - Elapsed Time: ' . round(microtime(true) - $start, 2));
    }
}
