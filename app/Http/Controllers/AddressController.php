<?php

namespace App\Http\Controllers;

use App\Jobs\UserImport;
use App\Models\UserAddress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Components\CEP;

class AddressController extends Controller
{
    /**
     * Display the specified User.
     */
    public function show(Request $request)
    {
        // Validate input
        $input = $request->all();
        $validator = Validator::make($input, [
            'cep' => 'required|min:8|max:9'
        ]);

        if ($validator->fails())
            return response()->json([
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);

        // Get CEP data
        $input['cep'] = Str::onlyNumbers($input['cep']);
        $cep = CEP::get($input['cep']);

        if ($cep === null)
            return response()->json([
                'errors' => ['cep' => 'The cep field are invalid.']
            ], Response::HTTP_BAD_REQUEST);

        return ['data' => $cep];
    }
}
