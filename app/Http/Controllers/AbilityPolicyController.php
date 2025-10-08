<?php

namespace App\Http\Controllers;

use App\Models\AbilityPolicy;
use Illuminate\Http\Request;

class AbilityPolicyController extends Controller
{
    public function index()
    {
        return AbilityPolicy::orderBy('key')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'key' => 'required|string|max:190|unique:ability_policies,key',
            'actions' => 'nullable|array',
            'actions.*' => 'string',
            'subjects' => 'nullable|array',
            'subjects.*' => 'string',
            'description' => 'nullable|string',
        ]);
        $policy = AbilityPolicy::create($data);
        return response()->json($policy, 201);
    }

    public function update(Request $request, int $id)
    {
        $policy = AbilityPolicy::findOrFail($id);
        $data = $request->validate([
            'key' => 'required|string|max:190|unique:ability_policies,key,' . $policy->id,
            'actions' => 'nullable|array',
            'actions.*' => 'string',
            'subjects' => 'nullable|array',
            'subjects.*' => 'string',
            'description' => 'nullable|string',
        ]);
        $policy->update($data);
        return $policy;
    }

    public function destroy(int $id)
    {
        AbilityPolicy::where('id', $id)->delete();
        return response()->json(['ok' => true]);
    }
}
