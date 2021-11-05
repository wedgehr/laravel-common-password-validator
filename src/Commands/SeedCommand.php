<?php

namespace Wedge\Validators\CommonPassword\Commands;

use Illuminate\Console\Command;
use Wedge\Validators\CommonPassword\Facade;

class SeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'common-password:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed common passwords';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Facade::seedPasswords();
    }
}
