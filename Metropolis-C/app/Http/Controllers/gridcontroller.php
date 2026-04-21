<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class gridcontroller extends Controller
{
    public function grid()
    {
        $grid = DB::table('grid')->get();
    return view('grid.grid', ['grid'=>$grid]);
    }
}