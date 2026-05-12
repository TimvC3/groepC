<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ZoningDesignation;
use Illuminate\View\View;

class ZoningDesignationController extends Controller
{
    public function index(): View
    {
        $functions = ZoningDesignation::orderBy('category')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.functions.index', compact('functions'));
    }
}