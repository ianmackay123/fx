<?php

namespace App\Http\Controllers;

use App\Fx;
use Illuminate\Http\Request;

class FxController extends Controller
{

    public function showAllFxs()
    {
        return response()->json(Fx::all());
    }

    public function showOneFx($id)
    {
        return response()->json(Fx::find($id));
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'location' => 'required|alpha'
        ]);
        $fx = Fx::create($request->all());

        return response()->json($fx, 201);
    }

    public function update($id, Request $request)
    {
        $fx = Fx::findOrFail($id);
        $fx->update($request->all());

        return response()->json($fx, 200);
    }

    public function delete($id)
    {
        Fx::findOrFail($id)->delete();
        return response('Deleted Successfully', 200);
    }
}
