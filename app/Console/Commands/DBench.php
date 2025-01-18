<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\DBench\User;
use App\Models\DBench\Post;
use App\Models\DBench\Category;
use App\Models\DBench\Tag;

class DBench extends Command
{
    protected $signature = 'db:bench {--iterations=100 : Number of iterations for each benchmark}';
    protected $description = 'Run database benchmark with dbench tables: CRUD, joins, aggregations, relationships, pagination';

    public function handle()
    {
        return $this->runBenchmarks();
    }

    public function runBenchmarks()
    {
        $this->truncateTables();

        $iterations = (int) $this->option('iterations');
        $this->info("Running database benchmark with {$iterations} iterations...");

        $writeReadTime = $this->benchmarkWriteRead($iterations);
        $joinTime = $this->benchmarkJoins($iterations);
        $manyToManyTime = $this->benchmarkManyToMany($iterations);
        $relationshipTime = $this->benchmarkRelationships($iterations);
        $aggregationTime = $this->benchmarkAggregations($iterations);
        $paginationTime = $this->benchmarkPagination($iterations);

        $this->truncateTables();

        $this->info("\nBenchmark Results:");
        $this->info("Write/Read operations: {$writeReadTime} ms");
        $this->info("JOIN queries: {$joinTime} ms");
        $this->info("Many-to-Many Relationships: {$manyToManyTime} ms");
        $this->info("Eloquent Relationships: {$relationshipTime} ms");
        $this->info("Aggregations: {$aggregationTime} ms");
        $this->info("Pagination: {$paginationTime} ms");

        return Command::SUCCESS;
    }

    protected function benchmarkWriteRead($iterations)
    {
        $this->info('Benchmarking Write/Read operations...');
        $start = microtime(true);

        // Create users first
        $userIds = [];
        for ($i = 0; $i < $iterations; $i++) {
            $userIds[] = DB::table('dbench_users')->insertGetId([
                'name' => 'User ' . $i,
                'email' => 'user' . $i . '@example.com',
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create posts and categories
        $postIds = [];
        $categoryIds = [];
        for ($i = 0; $i < $iterations; $i++) {
            $postIds[] = DB::table('dbench_posts')->insertGetId([
                'user_id' => $userIds[array_rand($userIds)],
                'title' => 'Post Title ' . $i,
                'body' => 'This is the body of post ' . $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $categoryIds[] = DB::table('dbench_categories')->insertGetId([
                'name' => 'Category ' . $i,
                'description' => 'Description of Category ' . $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create tags (needed for Many-to-Many pivot with tags)
        for ($i = 0; $i < $iterations; $i++) {
            DB::table('dbench_tags')->insertGetId([
                'name' => 'Tag ' . $i,
                'description' => 'Description of Tag ' . $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Populate the pivot table with unique combinations of post_id and category_id
        $existingCombinations = [];
        for ($i = 0; $i < $iterations; $i++) {
            $postId = $postIds[array_rand($postIds)];
            $categoryId = $categoryIds[array_rand($categoryIds)];

            // Ensure unique combinations of post_id and category_id
            if (!isset($existingCombinations["$postId-$categoryId"])) {
                DB::table('dbench_post_category')->insert([
                    'post_id' => $postId,
                    'category_id' => $categoryId,
                ]);
                $existingCombinations["$postId-$categoryId"] = true;
            }
        }

        // Random read operation to test performance
        for ($i = 0; $i < $iterations; $i++) {
            DB::table('dbench_posts')
                ->join('dbench_users', 'dbench_posts.user_id', '=', 'dbench_users.id')
                ->where('dbench_posts.id', $postIds[array_rand($postIds)])
                ->first();
        }

        return round((microtime(true) - $start) * 1000, 2);
    }

    protected function benchmarkJoins($iterations)
    {
        $this->info('Benchmarking JOIN queries...');
        $start = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            DB::table('dbench_users')
                ->join('dbench_posts', 'dbench_users.id', '=', 'dbench_posts.user_id')
                ->select('dbench_users.name', 'dbench_posts.title')
                ->where('dbench_posts.id', rand(1, $iterations))
                ->first();
        }

        return round((microtime(true) - $start) * 1000, 2);
    }

    protected function benchmarkManyToMany($iterations)
    {
        $this->info('Benchmarking Many-to-Many Relationships...');
        $start = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $postId = rand(1, $iterations);
            $categoryId = rand(1, $iterations);

            // Avoid inserting duplicates
            $exists = DB::table('dbench_post_category')
                ->where('post_id', $postId)
                ->where('category_id', $categoryId)
                ->exists();

            if (!$exists) {
                DB::table('dbench_post_category')->insert([
                    'post_id' => $postId,
                    'category_id' => $categoryId,
                ]);
            }

            $postId2 = rand(1, $iterations);
            $tagId = rand(1, $iterations);

            // Same approach for post_tag pivot
            $existsTag = DB::table('dbench_post_tag')
                ->where('post_id', $postId2)
                ->where('tag_id', $tagId)
                ->exists();

            if (!$existsTag) {
                DB::table('dbench_post_tag')->insert([
                    'post_id' => $postId2,
                    'tag_id' => $tagId,
                ]);
            }
        }

        return round((microtime(true) - $start) * 1000, 2);
    }

    protected function benchmarkRelationships($iterations)
    {
        $this->info('Benchmarking Eloquent Relationships...');
        $start = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $user = User::with('posts')->find(rand(1, $iterations));
            if ($user) {
                $user->posts->count();
            }
        }

        return round((microtime(true) - $start) * 1000, 2);
    }

    protected function benchmarkAggregations($iterations)
    {
        $this->info('Benchmarking Aggregation queries...');
        $start = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            DB::table('dbench_posts')
                ->where('user_id', rand(1, $iterations))
                ->count();
        }

        return round((microtime(true) - $start) * 1000, 2);
    }

    protected function benchmarkPagination($iterations)
    {
        $this->info('Benchmarking Pagination...');
        $start = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            DB::table('dbench_users')
                ->paginate(10, ['*'], 'page', rand(1, $iterations / 10));
        }

        return round((microtime(true) - $start) * 1000, 2);
    }

    protected function truncateTables()
    {
        $this->info('Truncating tables...');

        // Check if the database connection is not SQLite
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        DB::table('dbench_users')->truncate();
        DB::table('dbench_posts')->truncate();
        DB::table('dbench_comments')->truncate();
        DB::table('dbench_categories')->truncate();
        DB::table('dbench_post_category')->truncate();
        DB::table('dbench_tags')->truncate();
        DB::table('dbench_post_tag')->truncate();
        DB::table('dbench_likes')->truncate();
        DB::table('dbench_media')->truncate();
        DB::table('dbench_audits')->truncate();

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $this->info('Tables truncated successfully.');
    }
}
