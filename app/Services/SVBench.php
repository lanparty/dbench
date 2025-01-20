<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;

class SVBench
{
    protected $logCallback;

    public function __construct(callable $logCallback)
    {
        $this->logCallback = $logCallback;
    }

    protected function log($message)
    {
        call_user_func($this->logCallback, $message);
    }

    public function benchmarkEncryption($iterations)
    {
        $this->log('Benchmarking Encryption/Decryption...');
        $start = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $faker = \Faker\Factory::create();
            $data = [
                'name' => $faker->name,
                'email' => $faker->email,
                'roles' => $faker->randomElements(['admin', 'user', 'editor'], 2),
                'permissions' => $faker->randomElements(['create', 'read', 'update', 'delete'], 3),
                'profile' => [
                    'address' => $faker->address,
                    'phone' => $faker->phoneNumber,
                    'dob' => $faker->date('Y-m-d', '2000-01-01')
                ],
                'random_string' => $faker->sentence,
            ];

            $encrypted = Crypt::encrypt($data);
            $decrypted = Crypt::decrypt($encrypted);

            // Verify the decrypted data matches the original data
            if ($decrypted !== $data) {
                $this->log('Decrypted data does not match original data!');
                return;
            }
        }

        return round((microtime(true) - $start) * 1000, 2);
    }

    public function benchmarkJson($iterations)
    {
        $this->log('Benchmarking JSON operations...');
        $iterations = $iterations * 10; // Increase iterations for JSON operations
        $start = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $faker = \Faker\Factory::create();
            $data = [
                'name' => $faker->name,
                'email' => $faker->email,
                'roles' => $faker->randomElements(['admin', 'user', 'editor'], 2),
                'permissions' => $faker->randomElements(['create', 'read', 'update', 'delete'], 3),
                'profile' => [
                    'address' => $faker->address,
                    'phone' => $faker->phoneNumber,
                    'dob' => $faker->date('Y-m-d', '2000-01-01')
                ],
                'random_string' => $faker->sentence,
            ];

            $json = json_encode($data);
            $decoded = json_decode($json, true);
        }

        return round((microtime(true) - $start) * 1000, 2);
    }

    public function benchmarkCache($iterations)
    {
        $this->log('Benchmarking Cache operations...');
        $start = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $key = 'benchmark_cache_key_' . $i;
            $value = 'This is a sample value for cache benchmark ' . $i;

            // Set cache
            Cache::put($key, $value, 60);

            // Get cache
            $cachedValue = Cache::get($key);

            // Delete cache
            Cache::forget($key);
        }

        return round((microtime(true) - $start) * 1000, 2);
    }
}
