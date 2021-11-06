<?php

namespace Wedge\Validators\CommonPassword;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CommonPassword
{
    private array $seedQueue = [];
    private array $config = [];
    private int $currentCount = 0;

    public function __construct()
    {
        $this->config = config('common-passwords');
    }

    /**
     * Check if a provided password is too common
     */
    public function isCommonPassword(string $password): bool
    {
        if (strlen($password) < $this->config['minlength']) {
            return true;
        }

        return $this->getQuery()
            ->where('id', hash('sha256', strtolower($password)))
            ->count() > 0;
    }

    public function seedPasswords(): void
    {
        $this->currentCount = $this->getQuery()->count();

        if ($this->atLimit()) {
            return;
        }

        foreach ($this->config['urls'] as $url) {
            $this->importPasswordDictionary($url);
        }
    }

    private function importPasswordDictionary(string $url): void
    {
        $path = storage_path(pathinfo($url)['basename'] ?? 'common-password-dictionary.txt');

        File::copy($url, $path);

        if (!($fh = @fopen($path, 'r'))) {
            throw new \Exception('failed to open path: ' . $path);
        }

        while (($buffer = fgets($fh, 128)) !== false) {
            if (!$this->importPassword($buffer)) {
                break;
            }
        }

        fclose($fh);
        unlink($path);
    }

    /**
     * import a password
     *
     * @return bool are we at the limit
     */
    private function importPassword(string $password): bool
    {
        $password = strtolower(trim($password));

        if (strlen($password) < $this->config['minlength']) {
            return true;
        }

        $this->seedQueue[] = $password;
        $this->currentCount++;

        if (count($this->seedQueue) >= 100 || $this->atLimit()) {
            $this->importPasswords();
        }

        return !$this->atLimit();
    }

    private function importPasswords(): void
    {
        $values = array_map(fn ($p) => [
            'id'       => hash('sha256', $p),
            'password' => $p
        ], array_unique($this->seedQueue));

        DB::table($this->config['table'])
            ->upsert($values, 'id');

        $this->seedQueue = [];
    }

    private function getQuery()
    {
        return DB::table($this->config['table']);
    }

    private function atLimit(): bool
    {
        if ($this->config['limit'] === 0) {
            return false;
        }

        return $this->currentCount >= $this->config['limit'];
    }
}
