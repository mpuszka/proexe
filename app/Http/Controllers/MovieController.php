<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use External\Bar\Movies\MovieService as BarMovies;
use External\Baz\Movies\MovieService as BazMovies;
use External\Foo\Movies\MovieService as FooMovies;

use Carbon\Carbon;
use \Cache;

class MovieController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getTitles(Request $request): JsonResponse
    {
        $barMovies = new BarMovies;
        $bazMovies = new BazMovies;
        $fooMovies = new FooMovies;
        $allTitles = [];

        try {
            $barTitles = $barMovies->getTitles();
            $bazTitles = $bazMovies->getTitles();
            $fooTitles = $fooMovies->getTitles();
            
            $allTitles = $fooTitles;

            $barTitlesArray = (isset($barTitles['titles']) ? $barTitles['titles'] : []);
            if (!empty($barTitlesArray) )
            {
                foreach($barTitlesArray as $title)
                {
                    $allTitles[] = $title['title'];
                } 
            }

            $bazTitlesArray = (isset($bazTitles['titles']) ? $bazTitles['titles'] : []);
            if (!empty($bazTitlesArray) )
            {
                foreach($bazTitlesArray as $title)
                {
                    $allTitles[] = $title;
                } 
            }

            $expiresAt = Carbon::now()->addMinutes(10);
            
            Cache::put('titles', $allTitles, $expiresAt);
            
        } catch (\Exception $e){
            $allTitles = Cache::get('titles');

            $this->getTitles($request);
        }        

        return response()->json($allTitles);
    }
}
