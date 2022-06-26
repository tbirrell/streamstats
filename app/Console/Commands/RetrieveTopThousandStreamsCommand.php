<?php

namespace App\Console\Commands;

use App\Models\Stream;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RetrieveTopThousandStreamsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitch:get-top-thousand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get top 1000 live streams';

    private $appAccessToken = null;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::table('streams')->update(['top_thousand' => 0]);

        $streams = collect();
        $after = null;
        $count = 0;
        while ($count < 1000) {
            $quantity = ($count > 900) ? 1000 - $count : 100;
            $response = $this->fetch($quantity, $after);
            $streams = $streams->merge($response->data)->unique('id');
            $after = $response->pagination->cursor;
            $count = $streams->count();
        }

        $streams = $streams->shuffle();

        $streams->each(function ($item, $key) {
            Stream::updateOrCreate([
                'stream_id'    => $item->id
            ], [
                'channel_name' => $item->user_name,
                'stream_title' => $item->title,
                'game'         => $item->game_name,
                'viewer_count' => $item->viewer_count,
                'start_time'   => Carbon::parse($item->started_at),
                'top_thousand' => 1
            ]);
        });
    }

    private function fetch($first, $after = null)
    {
        $query = [
            'first' => $first
        ];

        if ($after) {
            $query['after'] = $after;
        }

        return Http::helix()->withToken($this->appAccessToken ?? $this->appAccessToken())->get('/streams', $query)->object();
    }

    private function appAccessToken(): string
    {
        $response = Http::asForm()->post('https://id.twitch.tv/oauth2/token', [
            'client_id'     => env('TWITCH_CLIENT_ID'),
            'client_secret' => env('TWITCH_CLIENT_SECRET'),
            'grant_type'    => 'client_credentials'
        ]);

        $this->appAccessToken = $response->object()->access_token;

        return $this->appAccessToken;
    }
}
