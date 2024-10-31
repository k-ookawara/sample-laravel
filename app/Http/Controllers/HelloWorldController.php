<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelloWorldController extends Controller
{
    public function index(Request $request) {
        $params = [
            'id' => 'Hello World',
            'name' => $request->input('name'),
        ];
        return response()->json($params, 200);
    }
}
