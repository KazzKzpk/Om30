<?php

namespace App\Jobs;

use App\Http\Controllers\UserAddressController;
use App\Http\Controllers\UserController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\MessageBag;

class UserImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $currentLine    = 1;
    protected $columnMetadata = null;

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Get file path and column metadata from Redis
        $redis = Redis::client();
        $filePath = $redis->get('user.import.file');
        if (file_exists($filePath) === false) {
            $this->error('File ' . $filePath . ' not found.');
            return 1;
        }
        $this->columnMetadata = \unserialize($redis->get('user.import.metadata'));

        // Load file
        $this->alert("\r\n " . 'Import job started.' . "\r\n");
        $file = \fopen($filePath, 'rb');
        while (!feof($file)) {
            $row = \fgetcsv($file, 0, ';');
            $this->import($row);
            usleep(10000); // 10ms
            $this->currentLine++;
        }
        \fclose($file);

        $this->alert('Import job finished.');
        return 0;
    }

    protected function import($row)
    {
        $this->info('Import row line #' . $this->currentLine);

        // Extract data by column config options
        $data = [];
        $error = false;

        foreach ($this->columnMetadata as $key => $value) {
            $columnId = ($value - 1);
            if (isset($row[$columnId])) {
                $data[$key] = trim($row[$columnId]);
                if ($data[$key] === '') {
                    if ($key !== 'number_ex') {
                        $this->error('The field ' . $key . ' is empty.');
                        $error = true;
                    } else $this->warn('The field ' . $key . ' is empty.');
                }
            }
            elseif ($key !== 'number_ex') {
                $this->error('The field ' . $key . ' has not found.');
                $error = true;
            }
        }

        if ($error === true) {
            $this->warn('Skip to next line.');
            $this->info('');
            return;
        }

        $this->save($data);
    }

    protected function save($data)
    {
        $request = new Request();
        $request->replace($data);

        // Save User
        $userController = new UserController();
        $apiData = $userController->store($request);
        if (is_array($apiData) === false) {
            $apiData = $apiData->getOriginalContent();
            if (is_string($apiData))
                $apiData = (array)\json_decode($apiData);
        } else $apiData = $apiData['data']->toArray();

        if (isset($apiData['errors']) === true) {
            if ($apiData['errors'] instanceof MessageBag)
                $apiData['errors'] = $apiData['errors']->getMessages();
            foreach ($apiData['errors'] as $_ => $message) {
                if (is_array($message))
                    $message = $message[0];
                $this->error($message);
            }
            $this->warn('Skip to next line.');
            $this->info('');
            return;
        }

        $this->info('Name: ' . $apiData['name'] . ' | ' . 'CPF: ' . $apiData['cpf']);
        $this->success('User imported successfuly #' . $apiData['id']);

        // Save Address
        $data['user_id'] = $apiData['id'];
        $request->replace($data);
        $userAddressController = new UserAddressController();
        $apiData = $userAddressController->store($request);
        if (is_array($apiData) === false) {
            $apiData = $apiData->getOriginalContent();
            if (is_string($apiData))
                $apiData = (array)\json_decode($apiData);
        } else $apiData = $apiData['data']->toArray();

        if (isset($apiData['errors']) === true) {
            if ($apiData['errors'] instanceof MessageBag)
                $apiData['errors'] = $apiData['errors']->getMessages();
            foreach ($apiData['errors'] as $_ => $message) {
                if (is_array($message))
                    $message = $message[0];
                $this->error($message);
            }
            $this->warn('Skip to next line.');
            $this->info('');
            return;
        }

        $this->success('User Address imported successfuly #' . $apiData['id']);
        $this->info('');
    }

    protected function alert($message)
    { echo ("\033[01;37m ") . $message . "\r\n\033[0m"; }

    protected function error($message)
    { echo ("\033[01;31m ") . $message . "\r\n\033[0m"; }

    protected function info($message)
    { echo ("\033[01;36m ") . $message . "\r\n\033[0m"; }

    protected function success($message)
    { echo ("\033[01;32m ") . $message . "\r\n\033[0m"; }

    protected function warn($message)
    { echo ("\033[01;33m ") . $message . "\r\n\033[0m"; }
}
