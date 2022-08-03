<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

// used carbon as a date library
use Carbon\Carbon;

class PlayerController extends Controller
{
    /**
     * Get all players profile
     *
     * @param  string $team
     * @return \Illuminate\Response\JSON
     */
    public function all($team = null)
    {
        $team = $team ?? 'allblacks';

        $storedPlayers = $this->allPlayers($team);
        $players = collect();

        // get players stats
        $stats = collect();
        if($team == 'nba'){
            $stats = $this->getStats($team);
        }

        foreach ($storedPlayers as $key => $player) {
            $player = collect($player);

            // getting the next and prev player
            $nextPlayer = collect($storedPlayers->get($key+1));
            if($nextPlayer->isEmpty()){
                $nextPlayer = collect($storedPlayers->first());
            }

            $prevPlayer = collect($storedPlayers->get($key-1));
            if($prevPlayer->isEmpty()){
                $prevPlayer = collect($storedPlayers->last());
            }

            if($player->has('name')){
                // split first & last name
                $names = collect(preg_split('/\s+/', $player->get('name')));
                $player->put('last_name', $names->pop());
                $player->put('first_name', $names->join(' '));
            }else{
                // combine first & last name
                $names = collect([$player->get('first_name'), $player->get('last_name')]);
                $player->put('name', $names->implode(' '));

                $names = collect([$nextPlayer->get('first_name'), $nextPlayer->get('last_name')]);
                $nextPlayer->put('name', $names->implode(' '));

                $names = collect([$prevPlayer->get('first_name'), $prevPlayer->get('last_name')]);
                $prevPlayer->put('name', $names->implode(' '));
            }

            // getting the height of the player
            if(!$player->has('height')){
                $height = collect([$player->get('feet'), $player->get('inches')]);
                $player->put('height', $height->implode('\'') . '"');
            }

            // computing the age of player
            if(!$player->has('age')){
                $age = Carbon::parse($player->get('birthday'))->age;
                $player->put('age', $age);
            }

            // determine the image filename from the name
            $player->put('image', $this->image($player->get('name')));

            // stats to feature
            $player->put('featured', $this->feature($player, $team, $stats));

            // attaching next and prev players
            $player->put('next_player', collect([
                'id' => $nextPlayer->get('id'),
                'name' => $nextPlayer->get('name')
            ]));
            $player->put('prev_player', collect([
                'id' => $prevPlayer->get('id'),
                'name' => $prevPlayer->get('name')
            ]));

            // identifying player sports
            $player->put('sport', $team == "allblacks" ? "rugby" : "nba");

            // push the completed player details to collection
            $players->push($player);
        }

        return response()->json($players);
    }

    /**
     * Show a player profile
     *
     * @param  int $id
     * @return \Illuminate\View\View
     */
    public function show($id = null)
    {
        $id = $id ?? 1;

        $player = $this->player($id);

        // check if $player is empty
        if($player->isEmpty()){
            return redirect('/allblacks');
        }

        // getting the next and prev player
        $nextPlayer = $this->player($id+1);
        if($nextPlayer->isEmpty()){
            $nextPlayer = $this->player(1);
        }

        $prevPlayer = $this->player($id-1);
        if($prevPlayer->isEmpty()){
            $allPlayers = $this->allPlayers();

            $prevPlayer = collect($allPlayers->last());
        }

        // split first & last name
        $names = collect(preg_split('/\s+/', $player->get('name')));
        $player->put('last_name', $names->pop());
        $player->put('first_name', $names->join(' '));

        // determine the image filename from the name
        $player->put('image', $this->image($player->get('name')));

        // stats to feature
        $player->put('featured', $this->feature($player));

        // attaching next and prev players
        $player->put('next_player', collect([
            'id' => $nextPlayer->get('id'),
            'name' => $nextPlayer->get('name')
        ]));
        $player->put('prev_player', collect([
            'id' => $prevPlayer->get('id'),
            'name' => $prevPlayer->get('name')
        ]));

        // player sport
        $player->put('sport', 'rugby');

        return view('player', $player);
    }

    /**
     * Retrieve player data from the API
     *
     * @param int $id
     * @return \Illuminate\Support\Collection
     */
    protected function player(int $id): Collection
    {
        // return collect([
        //     "tries" => 21,
        //     "games" => 102,
        //     "number" => 9,
        //     "position" => "Halfback",
        //     "points" => 107,
        //     "name" => "Aaron Smith",
        //     "height" => 173,
        //     "age" => 33,
        //     "conversions" => 1,
        //     "weight" => 83,
        //     "penalties" => 0,
        //     "id" => "1",
        // ]);

        $baseEndpoint = 'https://www.zeald.com/developer-tests-api/x_endpoint/allblacks';

        $json = Http::get("$baseEndpoint/id/$id", [
            'API_KEY' => config('api.key'),
        ])->json();

        return collect(array_shift($json));
    }

    /**
     * Retrieve all players data from the API
     *
     * @param  string $team
     * @return \Illuminate\Support\Collection
     */
    protected function allPlayers(string $team): Collection
    {
        if($team == 'nba'){
            $baseEndpoint = 'https://www.zeald.com/developer-tests-api/x_endpoint/nba.players';
        }else{
            $baseEndpoint = 'https://www.zeald.com/developer-tests-api/x_endpoint/allblacks';
        }

        $json = Http::get($baseEndpoint, [
            'API_KEY' => config('api.key'),
        ])->json();

        return collect($json);
    }

    /**
     * Retrieve all stats data from the API
     *
     * @param  string $team
     * @return \Illuminate\Support\Collection
     */
    protected function getStats(string $team): Collection
    {
        $baseEndpoint = 'https://www.zeald.com/developer-tests-api/x_endpoint/nba.stats';

        $json = Http::get($baseEndpoint, [
            'API_KEY' => config('api.key'),
        ])->json();

        return collect($json);
    }

    /**
     * Determine the image for the player based off their name
     *
     * @param string $name
     * @return string filename
     */
    protected function image(string $name): string
    {
        return preg_replace('/\W+/', '-', strtolower($name)) . '.png';
    }

    /**
     * Build stats to feature for this player
     *
     * @param \Illuminate\Support\Collection $player
     * @return \Illuminate\Support\Collection features
     */
    protected function feature(Collection $player, string $team, Collection $stats): Collection
    {
        if($team == "allblacks"){
            return collect([
                ['label' => 'Points', 'value' => $player->get('points')],
                ['label' => 'Games', 'value' => $player->get('games')],
                ['label' => 'Tries', 'value' => $player->get('tries')],
            ]);
        }else{
            $features = collect();

            foreach ($stats as $key => $stat){
                $stat = collect($stat);

                if($stat->get('player_id') == $player->get('id'))
                    $features = collect($stat);
            }

            $assists    = number_format($features->get('assists') / $features->get('games'), 1);
            $points     = number_format($features->get('points') / $features->get('games'), 1);
            $rebounds   = number_format($features->get('rebounds') / $features->get('games'), 1);

            return collect([
                ['label' => 'Assist Per Game', 'value' => $assists],
                ['label' => 'Points Per Game', 'value' => $points],
                ['label' => 'Rebounds Per Game', 'value' => $rebounds],
            ]);
        }
    }
}
