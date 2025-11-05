<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PostController extends Controller
{
    /**
     * Display a listing of the posts
     */
    public function index()
    {
        $post = Post::with(['author:id,name,email'])
            ->select(
                'id',
                'user_id',
                'title',
                'content',
                'is_draft',
                'published_at',
                'created_at'
            )
            ->where('is_draft', 0) // exclude draft posts
            ->paginate(20);

        return response()->json([
            'status' => 'Success',
            'message' => 'Success retrieve posts',
            'data' => $post,
        ]);
    }

    /**
     * Display a create post page
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created post.
     */
    public function store()
    {
        //
    }

    /**
     * Display a edit post page
     */
    public function edit()
    {
        //
    }

    /**
     * Display the specific post.
     */
    public function show(Post $post)
    {
        $post = $post->load(['author:id,name']);
        if ($post->is_draft == 1) {
            $data = [
                'status' => 'Error',
                'data' => null,
                'message' => 'Post not found',
            ];

            return response()->json($data, 404);
        }

        $data = [
            'status' => 'Success',
            'data' => $post,
            'message' => 'Success retrieve a post',
        ];

        return response()->json($data, 201);
    }

    /**
     * Update the specific post.
     */
    public function update()
    {
        //
    }

    /**
     * Remove / delete the specific post
     */
    public function destroy()
    {
        //
    }
}
