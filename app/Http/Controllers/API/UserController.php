<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //

    public function index(Request $request)
    {
        $query = User::with(['details', 'location']);

        if ($request->has('gender')) {
            $query->whereHas('details', fn($q) => $q->where('gender', $request->gender));
        }

        if ($request->has('city')) {
            $query->whereHas('location', fn($q) => $q->where('city', $request->city));
        }

        if ($request->has('country')) {
            $query->whereHas('location', fn($q) => $q->where('country', $request->country));
        }

        $limit = $request->input('limit', 10);
        $users = $query->limit($limit)->get();

        if ($request->has('fields')) {
            $fields = explode(',', $request->fields);
            $users = $users->map(fn($user) => collect($user)->only($fields));
        }

        return response()->json($users);
    }
}
