<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $this->tags();
        return Inertia::render('App', [
            'totalStreamsByGame' => collect(DB::select(DB::raw('SELECT game, count(1) as streams from streams where top_thousand = 1 group by game')))->sortByDesc('streams'),
            'topGamesByViewer'   => collect(DB::select(DB::raw('SELECT game, sum(viewer_count) as viewers from streams where top_thousand = 1 group by game')))->sortByDesc('viewers'),
            'medianViewers'      => DB::select(
                DB::raw('SELECT max(viewer_count) as median FROM (SELECT viewer_count FROM streams where top_thousand = 1 ORDER BY viewer_count limit 500) x;')
            )[0]->median,
            'top100Streams'      => Stream::take(100)->where('top_thousand', 1)->orderBy('viewer_count', 'DESC')->get(),
            'allStreams'         => Stream::where('top_thousand', 1)->orderBy('viewer_count', 'DESC')->get(),
            'userStreams'        => $this->userStreams(),
            'tags'               => $this->tags(),
        ]);
    }

    protected function userStreams()
    {
        //todo needs to be able to handle more than the default 100
        return Http::helix()->withToken(auth()->user()->token)->get('/streams/followed', ['user_id' => auth()->user()->twitch_id])->object();
    }

    protected function tags()
    {
        $s = Stream::select('tags')->where('top_thousand', 1)->get();

        $tags = collect();
        $s->each(function ($stream) use (&$tags) {
            $tags = $tags->merge($stream->tags);
        });

        $tagsRes = collect();
        $tags->chunk(100)->each(function ($chunk) use (&$tagsRes) {
            $res = Http::helix()->withToken(auth()->user()->token)->get('/tags/streams' . '?tag_id=' . $chunk->implode('&tag_id='))->json();
            $tagsRes = $tagsRes->merge(
                collect($res['data'])->mapWithKeys(function ($tag) {
                    return [$tag['tag_id'] => $tag['localization_names']['en-us']];
                })
            );
        });

        return $tagsRes;
    }
}
