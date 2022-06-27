<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        return Inertia::render('App', [
            'totalStreamsByGame' => collect(DB::select(DB::raw('SELECT game, count(1) as streams from streams where top_thousand = 1 group by game')))->sortByDesc('streams'),
            'topGamesByViewer'   => collect(DB::select(DB::raw('SELECT game, sum(viewer_count) as viewers from streams where top_thousand = 1 group by game')))->sortByDesc('viewers'),
            'medianViewers'      => DB::select(DB::raw('SELECT max(viewer_count) as median FROM (SELECT viewer_count FROM streams where top_thousand = 1 ORDER BY viewer_count limit 500) x;'))[0]->median,
            'top100Streams'      => Stream::take(100)->where('top_thousand', 1)->orderBy('viewer_count', 'DESC')->get(),
            'allStreams'      => Stream::where('top_thousand', 1)->orderBy('viewer_count', 'DESC')->get(),
        ]);
    }
}
