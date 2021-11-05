<?php

namespace Wedge\Validators\CommonPassword;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class Service
{
    private array $seedQueue = [];
    private array $config = [];

    public function __construct()
    {
        $this->config = config('common-passwords');
    }

    public function seedPasswords(): void
    {
        foreach ($this->config['urls'] as $url) {
            $this->importPasswordDictionary($url);
        }
    }

    private function importPasswordDictionary(string $url)
    {
        $path = storage_path(pathinfo($url)['basename'] ?? 'common-password-dictionary.txt');

        File::copy($url, $path);

        $fh = @fopen($path, 'r');

        if (!$fh) {
            throw new \Exception('failed to open path: ' . $path);
        }

        while (($buffer = fgets($fh, 128)) !== false) {
            $this->importPassword($buffer);
        }

        if (!feof($fh)) {
            fclose($fh);
            throw new \Exception('failed to read from file at path: ' . $path);
        }

        fclose($fh);
    }

    private function importPassword(string $password): void
    {
        $password = trim($password);

        if (strlen($password) < $this->config['minlength']) {
            return;
        }

        $this->seedQueue[] = $password;

        if (count($this->seedQueue) >= 100) {
            $this->importPasswords();
        }
    }

    private function importPasswords(): void
    {
        $values = array_map(fn ($p) => [
            'id'       => hash('sha256', $p),
            'password' => $p
        ], $this->seedQueue);

        DB::table($this->config['table'])
            ->upsert($values, 'id');

        $this->seedQueue = [];
    }
}
