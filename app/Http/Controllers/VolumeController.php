<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Novel;


class VolumeController extends Controller
{
    public function store(Request $request, Novel $novel)
    {
        $request->validate([
            'title' => 'required|string',
            'order' => 'nullable|integer'
        ]);

        $volume = $novel->volumes()->create([
            'title' => $request->title,
            'order' => $request->order ?? 1
        ]);

        return response()->json($volume, 201);
    }
}
