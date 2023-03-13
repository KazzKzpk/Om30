<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the Users.
     */
    public function index(Request $request)
    {

        // Validate input
        $input = $request->all('search');
        if ($input['search'] !== null)
            return $this->search($request);

        $users = User::getPaginated();
        return ['data' => $users];
    }

    /**
     * Store a newly created User in storage.
     */
    public function store(Request $request)
    {
        // Validate input and return
        $input = $request->all();
        $validator = Validator::make($input, [
            'name'        => 'required',
            'name_mother' => 'required',
            'birth'       => ('required|date_format:Y-m-d|before:' . date(DATE_ATOM)),
            'cpf'         => 'required|min:11|max:14|cpf',
            'cns'         => 'required|cns',
        ]);

        if ($validator->fails())
            return response()->json([
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);

        // Validate CPF duplicated
        $input['cpf'] = Str::onlyNumbers($input['cpf']);
        if (User::isRegisteredCPF($input['cpf']) === true)
            return response()->json([
                'errors' => ['cpf' => 'The cpf field is already registered.']
            ], Response::HTTP_BAD_REQUEST);

        // Validate CNS duplicated
        $input['cns'] = Str::onlyNumbers($input['cns']);
        if (User::isRegisteredCNS($input['cns']) === true)
            return response()->json([
                'errors' => ['cns' => 'The cns field is already registered.']
            ], Response::HTTP_BAD_REQUEST);

        // Save user and update matchcode
        $user = new User($input);
        $user->updateMatchCode();
        $user->save();

        return ['data' => $user->load('address')];
    }

    /**
     * Display a listing of the Users.
     */
    public function search(Request $request)
    {
        // Validate input
        $input = $request->all(['mode', 'search']);

        if ($input['mode'] !== 'cpf' && $input['mode'] !== 'cns')
            $input['mode'] = 'name';

        // Search by CPF
        if ($input['mode'] === 'cpf') {
            $user = User::getByCPF(Str::onlyNumbers($input['search']))->toArray();
            if (count($user) <= 0)
                return response()->json([
                    'errors' => ['cpf' => 'User not found by cpf.']
                ], Response::HTTP_BAD_REQUEST);
            return ['data' => $user[0]->load('address')];
        }

        // Search by CNS
        if ($input['mode'] === 'cns') {
            $user = User::getByCNS(Str::onlyNumbers($input['search']))->toArray();
            if (count($user) <= 0)
                return response()->json([
                    'errors' => ['cns' => 'User not found by cns.']
                ], 400);
            return ['data' => $user[0]->load('address')];
        }

        // Search by Name WildCard (matchcode)
        $user = User::getByMatchCode($input['search']);
        if (count($user->toArray()) <= 0)
            return response()->json([
                'errors' => ['name' => 'User not found by name matchcode.']
            ], Response::HTTP_BAD_REQUEST);
        return ['data' => $user[0]->load('address')];
    }

    /**
     * Display the specified User.
     */
    public function show(User $user)
    { return ['data' => $user->load('address')]; }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // Validate input and return
        $input = $request->all();
        $validator = Validator::make($input, [
            'name'        => 'required',
            'name_mother' => 'required',
            'birth'       => ('required|date_format:Y-m-d|before:' . date(DATE_ATOM))
        ]);

        if ($validator->fails())
            return response()->json([
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);

        // Update user
        $user->name        = $input['name'];
        $user->name_mother = $input['name_mother'];
        $user->birth       = $input['birth'];
        if (isset($input['address_id']) === true)
            $user->address_id = $input['address_id'];
        $user->updateMatchCode();
        $user->save();

        return ['data' => $user->load('address')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user)
    {
        $user->load('address');

        $userId = $user->toArray()['id'];
        $user->delete();
        return ['data' => ['user_id' => $userId]];
    }
}
