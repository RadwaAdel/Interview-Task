<?php

namespace App\Http\Controllers;


use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $posts = Auth::user()->posts()
            ->with('tags')
            ->orderBy('pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($posts);
    }

    public function store(StorePostRequest $request)
    {
        $coverImagePath = $request->file('cover_image')->store('cover_images', 'public');

        $post = Auth::user()->posts()->create([
            'title' => $request->title,
            'body' => $request->body,
            'cover_image' => $coverImagePath,
            'pinned' => (int) $request->pinned,
        ]);

        $tags = $this->parseTags($request->tags);
        $post->tags()->attach($tags);

        return response()->json($post->load('tags'), 201);
    }

    public function show(Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($post->load('tags'));
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($request->hasFile('cover_image')) {
            Storage::delete('public/' . $post->cover_image);
            $post->cover_image = $request->file('cover_image')->store('cover_images', 'public');
        }

        $post->update([
            'title' => $request->title,
            'body' => $request->body,
            'pinned' => (int) $request->pinned,
        ]);

        if ($request->has('tags')) {
            $tags = $this->parseTags($request->tags);
            $post->tags()->sync($tags);
        }

        return response()->json($post->load('tags'));
    }

    public function destroy(Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $post->delete();
        return response()->json(['message' => 'Post deleted successfully']);
    }

    public function trashed()
    {
        $posts = Auth::user()->posts()->onlyTrashed()->get();
        return response()->json($posts);
    }

    public function restore($id)
    {
        $post = Auth::user()->posts()->onlyTrashed()->where('id', $id)->firstOrFail();
        $post->restore();
        return response()->json(['message' => 'Post restored successfully']);
    }

    private function parseTags($tags)
    {
        if (is_string($tags)) {
            $tags = json_decode($tags, true);
        }
        return array_map('intval', (array) $tags);
    }
}
