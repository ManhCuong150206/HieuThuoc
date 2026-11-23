<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HealthArticleSeeder extends Seeder
{
    public function run(): void
    {
        $authorId = Account::where('role', 'ADMIN')->where('is_active', true)->orderBy('id')->value('id');

        if ($authorId === null) {
            throw new \RuntimeException('Cần một tài khoản quản trị đang hoạt động để đăng bài sức khỏe.');
        }

        DB::transaction(function () use ($authorId): void {
            foreach (config('health_articles') as $coverUrl => $article) {
                Post::firstOrCreate(['cover_url' => $coverUrl], [
                    'title' => $article['title'],
                    'content' => $article['content'],
                    'status' => 'PUBLISHED',
                    'published_at' => today(),
                    'author_account_id' => $authorId,
                ]);
            }
        });
    }
}
