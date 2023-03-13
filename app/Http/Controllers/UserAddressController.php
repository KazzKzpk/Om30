<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class UserAddressController extends Controller
{
    /**
     * Store a newly created Address in storage.
     */
    public function store(Request $request)
    {
        // Validate input
        $input = $request->all('user_id', 'cep', 'number', 'number_ex');
        $validator = Validator::make($input, [
            'cep'    => 'required|min:8|max:9',
            'number' => 'required'
        ]);

        if ($validator->fails())
            return response()->json([
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);

        // Validate user & address
        $user = User::find($input['user_id'])->toArray();
        if (count($user) <= 0)
            return response()->json([
                'error' => 'User not found.'
            ], Response::HTTP_BAD_REQUEST);

        $user = $user[0];
        if (isset($user['address']) === true && $user['address'] !== null)
            return response()->json([
                'error' => 'Address already registered on user.'
            ], Response::HTTP_BAD_REQUEST);

        // Get CEP data
        $addressController = new AddressController();
        $data = $addressController->show($request);
        if ($data instanceof JsonResponse)
            return response($data->getContent(), Response::HTTP_BAD_REQUEST);
        $cep = (array)$data['data'];

        // Save address
        $address = new UserAddress($cep);
        $address->user_id   = $input['user_id'];
        $address->number    = $input['number'];
        $address->number_ex = $input['number_ex'];
        $address->save();

        return ['data' => $address];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserAddress $address)
    {
        // Validate input and return
        $input = $request->all();
        $validator = Validator::make($input, [
            'cep'    => 'required|min:8|max:9',
            'number' => 'required'
        ]);

        if ($validator->fails())
            return response()->json([
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);

        // Get CEP data
        $addressController = new AddressController();
        $data = $addressController->show($request);
        if ($data instanceof JsonResponse)
            return response($data->getContent(), Response::HTTP_BAD_REQUEST);
        $cep = (array)$data['data'];

        // Update address
        $address->street    = $cep->logradouro;
        $address->number    = $input['number'];
        $address->district  = $cep->bairro;
        $address->city      = $cep->localidade;
        $address->state     = $cep->uf;
        if (isset($input['number_ex']) === true)
            $address->number_ex = $input['number_ex'];
        $address->save();

        return ['data' => $address];
    }
}
