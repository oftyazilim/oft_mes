<?php

namespace App\Http\Controllers;

use App\Models\UserGridLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserGridLayoutController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $pageKey = $request->query('page');
        $q = UserGridLayout::query()->where('user_id', $user->id);
        if ($pageKey) $q->where('page_key', $pageKey);
        return response()->json($q->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'page_key' => 'required|string|max:190',
            'name' => 'required|string|max:190',
            'include_filters' => 'boolean',
            'state' => 'required|array',
            'meta' => 'array',
        ]);
        $data['user_id'] = $user->id;
        $layout = UserGridLayout::updateOrCreate(
            [
                'user_id' => $user->id,
                'page_key' => $data['page_key'],
                'name' => $data['name'],
            ],
            [
                'include_filters' => $data['include_filters'] ?? true,
                'state' => $data['state'],
                'meta' => $data['meta'] ?? [],
            ]
        );
        return response()->json($layout, 201);
    }

    public function show($id)
    {
        $user = Auth::user();
        $layout = UserGridLayout::where('user_id', $user->id)->findOrFail($id);
        return response()->json($layout);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $layout = UserGridLayout::where('user_id', $user->id)->findOrFail($id);
        $layout->delete();
        return response()->json(['ok' => true]);
    }

    /**
     * Mark a layout as last used for its page (per user), clearing the flag on others.
     */
    public function markLastUsed(Request $request, $id)
    {
        $user = Auth::user();
        $layout = UserGridLayout::where('user_id', $user->id)->findOrFail($id);

        // Prefer provided page_key if any, fallback to layout's own
        $pageKey = $request->input('page_key', $layout->page_key);

        // Clear last_used for other layouts of the same page
        $others = UserGridLayout::where('user_id', $user->id)
            ->where('page_key', $pageKey)
            ->where('id', '!=', $layout->id)
            ->get();

        foreach ($others as $o) {
            $meta = is_array($o->meta) ? $o->meta : [];
            $meta['last_used'] = false;
            $o->meta = $meta;
            $o->save();
        }

        // Set last_used on the chosen layout
        $meta = is_array($layout->meta) ? $layout->meta : [];
        $meta['last_used'] = true;
        $meta['last_used_at'] = now()->toISOString();
        $layout->meta = $meta;
        $layout->save();

        return response()->json(['ok' => true, 'layout' => $layout]);
    }
}
