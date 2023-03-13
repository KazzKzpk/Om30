<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Jobs\UserImport as UserImportJob;

class UserImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:user-import {file} {--now="false"} {--name="0"} {--name_mother="1"} {--birth="2"} {--cpf="3"} {--cns="4"} {--cep="5"} {--number="6"} {--number_ex="7"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Users CSV';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Verify file
        $filePath = $this->argument('file');
        if (file_exists($filePath) === false) {
            $this->error('File ' . $filePath . ' not found.');
            return 1;
        }

        // Send file path & column metadata to Redis
        $redis = Redis::client();
        $redis->set('user.import.file', $filePath);
        $redis->set('user.import.metadata', \serialize($this->updateColumnMetadata()));

        // Start Job Now
        if ($this->options()['now'] === 'true')
            return (new UserImportJob())->handle();

        // Start Job queue
        UserImportJob::dispatch();
        $this->info('Import users from ' . $filePath . ' job started.');
        return 0;
    }

    protected function updateColumnMetadata()
    {
        // Get options and remove laravel default
        $options = $this->options();
        unset($options['now']);
        unset($options['help']);
        unset($options['quiet']);
        unset($options['verbose']);
        unset($options['version']);
        unset($options['ansi']);
        unset($options['no-interaction']);
        unset($options['env']);
        return $options;
    }
}
