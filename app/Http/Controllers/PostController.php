<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\StorePostRequest;
use App\Models\Post;
use Illuminate\Support\Facades\Gate;

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
        return response()->json([
            'status' => 'Success',
            'message' => 'No content',
            'data' => null,
        ], 200);
    }

    /**
     * Store a newly created post.
     */
    public function store(StorePostRequest $request)
    {
        try {
            $post = $request->user()->posts()->create($request->validated());
            $data = [
                'status' => 'Success',
                'data' => $post,
                'message' => 'Success creating a post',
            ];

            return response()->json($data, 201);
        } catch (\Throwable $e) {
            $data = [
                'status' => 'Error',
                'data' => null,
                'message' => 'Fail creating a post. '.$e->getMessage(),
            ];

            return response()->json($data, 500);
        }
    }

    /**
     * Display a edit post page
     */
    public function edit()
    {
        return response()->json([
            'status' => 'Success',
            'message' => 'No content',
            'data' => null,
        ], 200);
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

        return response()->json($data, 200);
    }

    /**
     * Update the specific post.
     */
    public function update(StorePostRequest $postRequest, Post $post)
    {
        if (! Gate::allows('update', $post)) {
            $data = [
                'status' => 'Error',
                'data' => null,
                'message' => "You can't update it, are not the author of this post",
            ];

            return response()->json($data, 403);
        }

        try {
            $post->update($postRequest->validated());
            $data = [
                'status' => 'Success',
                'data' => $post,
                'message' => 'Success updating a post',
            ];

            return response()->json($data, 201);
        } catch (\Throwable $e) {
            $data = [
                'status' => 'Error',
                'data' => null,
                'message' => 'Fail updating a post. '.$e->getMessage(),
            ];

            return response()->json($data, 500);
        }
    }

    /**
     * Remove / delete the specific post
     */
    public function destroy(Post $post)
    {
        if (! Gate::allows('delete', $post)) {
            $data = [
                'status' => 'Error',
                'data' => null,
                'message' => "You can't delete it, are not the author of this post",
            ];

            return response()->json($data, 403);
        }

        try {
            $post->delete();
            $data = [
                'status' => 'Success',
                'data' => $post,
                'message' => 'Success deleting a post',
            ];

            return response()->json($data, 201);
        } catch (\Throwable $e) {
            $data = [
                'status' => 'Error',
                'data' => null,
                'message' => 'Fail deleting a post. '.$e->getMessage(),
            ];

            return response()->json($data, 500);
        }
    }
}
