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
        $posts = Post::where('published_at', '<=', now()->toDateTimeString())
            ->where('is_draft', 1)
            ->select('id')
            ->limit(500) // limit 500 to avoid overload
            ->get();
        if ($posts->isEmpty()) {
            return;
        }
        try {
            $updated = Post::whereIn('id', $posts->pluck('id'))
                ->update([
                    'is_draft' => 0,
                ]);
            Log::info('Successfully updating is_draft on table posts', [
                'count' => $updated,
                'post_ids' => $posts,
            ]);

        } catch (\Throwable $e) {
            Log::warning('Fail updating is_draft on table posts', [
                'when' => now(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
