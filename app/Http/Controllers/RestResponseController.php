<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RestResponseController extends Controller
{
    public static function json($code, $message, $data = null)
    {
        if ($data != null) {
            return response()->json(['metaData' => ['code' => $code, 'message' => $message], 'data' => $data], 200);
        } else {
            return response()->json(['metaData' => ['code' => $code, 'message' => $message]], 200);
        }
    }
}
