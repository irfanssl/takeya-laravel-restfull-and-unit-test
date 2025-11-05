<?php

namespace App\Service\Post;

use App\Models\Post;
use Illuminate\Support\Facades\Log;

class PublishingPost
{
    public function __invoke()
    {
        $this->publish();
    }

    private function publish()
    {
        $posts = Post::where('created_at', '<=', now()->toDateTimeString())
            ->where('is_draft', 0)
            ->where('published_at', null)
            ->select('id')
            ->limit(500) // limit 500 to avoid overload
            ->get();
        if ($posts->isEmpty()) {
            return;
        }
        try {
            $updated = Post::whereIn('id', $posts->pluck('id'))
                ->update([
                    'published_at' => now()->toDateTimeString(),
                ]);
            Log::info('Successfully updating published_at on table posts', [
                'count' => $updated,
                'post_ids' => $posts,
            ]);

        } catch (\Throwable $e) {
            Log::warning('Fail updating published_at on table posts', [
                'when' => now(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
