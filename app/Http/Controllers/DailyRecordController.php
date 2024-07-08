<?php

namespace App\Http\Controllers;

use App\Models\DailyRecord;
use Illuminate\Http\Request;

class DailyRecordController extends Controller
{
    public function index()
    {
        $records = DailyRecord::all();
        return view('daily_records.index', ['records' => $records]);
    }
}
