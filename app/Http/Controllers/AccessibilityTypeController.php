<?php

namespace App\Http\Controllers;

use App\Models\AccessibilityType;

class AccessibilityTypeController extends Controller
{
    public function index()
    {
        $types = AccessibilityType::orderBy('type_name')->get();

        return view('accessibility.type.index', compact('types'));
    }
}
