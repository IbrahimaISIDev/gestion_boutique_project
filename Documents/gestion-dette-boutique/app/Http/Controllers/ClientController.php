<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Resources\ClientCollection;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Models\User;
use App\Traits\RestResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use mysql_xdevapi\Exception;
use Spatie\QueryBuilder\QueryBuilder;

class ClientController extends Controller
{
    use RestResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
     //  return Client::whereNotNull('user_id')->get();
        $include = $request->has('include')?  [$request->input('include')] : [];

        $data = Client::with($include)->whereNotNull('user_id')->get();
        //return  response()->json(['data' => $data]);
      //  return  ClientResource::collection($data);
       // return new ClientCollection($data);
        $clients = QueryBuilder::for(Client::class)
            ->allowedFilters(['surname'])
            ->allowedIncludes(['user'])
            ->get();
        return new ClientCollection($clients);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request)
    {
        try {
            DB::beginTransaction();
            $clientRequest =  $request->only('surname','adresse','telephone');
            $client= Client::create($clientRequest);
            if ( $request->has('user')){
                $user = User::create([
                    'nom' => $request->input('user.nom'),
                    'prenom' => $request->input('user.prenom'),
                    'login' => $request->input('user.login'),
                    'password' => $request->input('user.password'),
                    'role' => $request->input('user.role'),
                ]);

                $user->client()->save($client);
            }
            DB::commit();
            return $this->sendResponse(new ClientResource($client),);
        }catch (Exception $e){
            DB::rollBack();
             return $this->sendResponse(new ClientResource($e->getMessage()),);
    }




    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }




}
