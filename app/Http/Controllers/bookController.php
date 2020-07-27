<?php

namespace App\Http\Controllers;

use App\Http\Resources\bookResorce;
use App\Models\Book;
use App\Models\Library\Library;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class bookController extends Controller
{
    public function index(Request $request)
    {
        $queryString = $request->queryString;
        $books = Book::where('shabak', 'like', "%$queryString%")
            ->orWhere('shabak', 'like', "%$queryString%")
            ->get();
        if ($books->count() > 20) {
            return 'to many results';
        }
        // return $books->toArray();
        foreach ($books as $book) {
            try {
                $response = Http::retry(10, 100)->get('http://www.samanpl.ir/api/SearchAD/Libs_Show/', [
                    'materialId' => 1,
                    'recordnumber' => $book->recordNumber,
                    'OrgIdOstan' => 0,
                    'OrgIdShahr' => 0,
                ]);
                $response = json_decode($response, true);
            } catch (\Exception $e) {
                $response = null;
            }
            $libraryIds = array();
            foreach ($response['Results'] as $result) {
                // return $result['OrgId'];
                $library = Library::where('libraryCode', $result['OrgId'])->first();
                if ($library) {
                    array_push($libraryIds, $library->id);
                }
            }
            $book->libraries()->detach();
            $book->libraries()->attach($libraryIds);

        }
        $books->load(['libraries.city','libraries.state']);
        return bookResorce::collection($books);
    }
}
