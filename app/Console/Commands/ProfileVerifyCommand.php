<?php

namespace App\Console\Commands;

use App\Jobs\ProfileVerify;
use App\Models\Team;
use Illuminate\Console\Command;

class ProfileVerifyCommand extends Command
{
    protected $signature = 'app:profile:verify {profile}';

    protected $description = 'Verify Products on Kwai Profile';

    private const OPTION_PROFILE = 'profile';
    
    public function handle()
    {
        $start   = microtime(true);
        $profile = $this->argument(self::OPTION_PROFILE);

        if (empty($profile)) {
            return 0;
        }

        $team = Team::query()->where('username', $profile)->firstOrFail();

        if (empty($team->getSyncedAt())) {
            return 0;
        }

        ProfileVerify::dispatchSync($team);

        $this->info(date('Y-m-d H:i:s') . ' - Elapsed Time: ' . round(microtime(true) - $start, 2));
    }
}
