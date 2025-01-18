<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Create dbench_users table first
        Schema::create('dbench_users', function (Blueprint $table) {
            $table->id(); // This will be unsigned and primary key
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps(0);
        });

        // Create dbench_posts table second
        Schema::create('dbench_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('dbench_users')->onDelete('cascade');
            $table->string('title');
            $table->text('body')->nullable();
            $table->timestamps(0);
            $table->index('user_id');
        });

        // Create dbench_categories table
        Schema::create('dbench_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps(0);
        });

        // Create dbench_tags table
        Schema::create('dbench_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description');
            $table->timestamps(0);
        });

        // Create dbench_comments table (after dbench_posts table)
        Schema::create('dbench_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('dbench_posts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('dbench_users')->onDelete('cascade');
            $table->text('comment');
            $table->timestamps(0);
            $table->index('post_id');
            $table->index('user_id');
        });

        // Create dbench_likes table (after dbench_posts table)
        Schema::create('dbench_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('dbench_posts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('dbench_users')->onDelete('cascade');
            $table->timestamps(0);
            $table->index('post_id');
            $table->index('user_id');
        });

        // Create dbench_media table (after dbench_posts table)
        Schema::create('dbench_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('dbench_posts')->onDelete('cascade');
            $table->string('file_path');
            $table->enum('media_type', ['image', 'video', 'document']);
            $table->timestamps(0);
            $table->index('post_id');
        });

        // Create dbench_post_category table (after dbench_posts and dbench_categories)
        Schema::create('dbench_post_category', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained('dbench_posts')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('dbench_categories')->onDelete('cascade');
            $table->primary(['post_id', 'category_id']);
            $table->index('category_id');
        });

        // Create dbench_post_tag table (after dbench_posts and dbench_tags)
        Schema::create('dbench_post_tag', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained('dbench_posts')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('dbench_tags')->onDelete('cascade');
            $table->primary(['post_id', 'tag_id']);
            $table->index('tag_id');
        });

        // Create dbench_audits table (after dbench_users)
        Schema::create('dbench_audits', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');
            $table->enum('operation', ['INSERT', 'UPDATE', 'DELETE']);
            $table->foreignId('user_id')->nullable()->constrained('dbench_users')->onDelete('set null');
            $table->timestamp('operation_time')->useCurrent();
            $table->text('details')->nullable();
            $table->timestamps(0);
            $table->index('user_id');
        });
    }

    public function down()
    {
        // Drop tables in reverse order of creation to avoid foreign key constraint issues
        Schema::dropIfExists('dbench_post_tag');
        Schema::dropIfExists('dbench_post_category');
        Schema::dropIfExists('dbench_media');
        Schema::dropIfExists('dbench_likes');
        Schema::dropIfExists('dbench_comments');
        Schema::dropIfExists('dbench_audits');
        Schema::dropIfExists('dbench_tags');
        Schema::dropIfExists('dbench_categories');
        Schema::dropIfExists('dbench_posts');
        Schema::dropIfExists('dbench_users');
    }
};
