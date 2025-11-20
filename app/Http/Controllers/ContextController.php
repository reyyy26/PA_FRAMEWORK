<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ContextController extends Controller
{
    public function switchBranch(Request $request)
    {
        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $user = $request->user();

        if ($user && !$user->branches()->whereKey($data['branch_id'])->exists()) {
            abort(403, 'User is not assigned to this branch');
        }

        Session::put('branch_id', $data['branch_id']);

        return response()->json([
            'branch' => Branch::find($data['branch_id']),
        ]);
    }
}
