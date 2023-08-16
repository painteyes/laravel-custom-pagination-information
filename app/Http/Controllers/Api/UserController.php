<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserListResource;

class UserController extends Controller
{
    public function index(Request $request)
    {   
        $search = $request->input('search');

        $users = \App\Models\User::where('name', 'like', "%{$search}%")->paginate(10)->onEachSide(3);
        
        // Aggiunge tutti i parametri di query al paginatore
        // $users->appends(\Request::all());
        
        /**
         * Quando utilizzi una risorsa di Laravel per formattare la risposta, 
         * questa risorsa controlla se l'elemento passato è paginato. 
         * Se lo è, prende i dati effettivi (gli elementi della pagina corrente) 
         * e li formatta usando il metodo toArray della risorsa. 
         * Poi, prende i metadati della paginazione (quelli prodotti dal metodo 
         * toArray del paginatore) e li inserisce nella chiave meta della risposta.
         */
        return UserListResource::collection($users); 

        return new \App\Http\Resources\UserCollection($users); // metodo alternativo
    }
}
