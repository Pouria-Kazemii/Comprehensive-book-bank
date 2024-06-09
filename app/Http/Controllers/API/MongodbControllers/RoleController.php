<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    ///////////////////////////////////////////////Search///////////////////////////////////////////////////
    public function search(Request $request)
    {
        $searchWord = $request->input('searchWord', '');
        $status = 404;
        $data = [];
        $roles = [];

        if ($searchWord !== '') {
            // Read partners.xrule that matches the search word
            $partnersRule = BookIrBook2::where('partners.xrule', 'LIKE', "%$searchWord%")
                ->pluck('partners');

            // Extract roles
            foreach ($partnersRule as $partnerRules) {
                foreach ($partnerRules as $partnerRule) {
                    if (isset($partnerRule['xrule']) && stripos($partnerRule['xrule'], $searchWord) !== false) {
                        $roles[] = $partnerRule['xrule'];
                    }
                }
            }

            // Get unique roles
            $uniqueRoles = array_unique($roles);

            // Format the data for response
            foreach ($uniqueRoles as $role) {
                $data[] = ["value" => $role];
            }

            if (!empty($data)) {
                $status = 200;
            }
        }

        // Response
        return response()->json(
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["list" => $data]
            ],
            $status
        );
        }

    }
