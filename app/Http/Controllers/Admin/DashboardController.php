<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $totals = [];

        foreach (WilayahController::LEVELS as $level => $cfg) {
            $totals[$level] = ['label' => $cfg['label'], 'total' => $cfg['model']::count()];
        }

        return view('dashboard', ['totals' => $totals]);
    }
}
