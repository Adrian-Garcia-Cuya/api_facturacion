<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Rules\UniqueRucRule;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies = Company::where('user_id', JWTAuth::user()->id)->get();

        return response()->json($companies, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'social_reason' => 'required|string',
            'ruc' => [
                'required',
                'string',
                'regex:/^(10|20)\d{9}$/',
                new UniqueRucRule(),
            ],
            'address' => 'required|string',
            'logo' => 'nullable|image',
            'sol_user' => 'required|string',
            'sol_pass' => 'required|string',
            'certificate' => 'required|file|mimes:pem,txt',
            'client_id' => 'nullable|string',
            'client_secret' => 'nullable|string',
            'production' => 'nullable|boolean',
        ]);

        if($request->hasFile('logo'))
        {
            $data['logo_path'] = $request->file('logo')->store('logos');
        }

        $data['certificate_path'] = $request->file('certificate')->store('certificates');
        $data['user_id'] = JWTAuth::user()->id;

        $company = Company::create($data);

        return response()->json([
            'message' => 'Empresa creada satisfactoriamente',
            'company' => $company
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($company)
    {
        $company = Company::where('ruc', $company)
                            ->where('user_id', JWTAuth::user()->id)
                            ->firstOrFail();

        return response()->json($company, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $company)
    {
        $company = Company::where('ruc', $company)
        ->where('user_id', JWTAuth::user()->id)
        ->firstOrFail();
        
        $data = $request->validate([
            'social_reason' => 'nullable|string',
            'ruc' => [
                'nullable',
                'string',
                'regex:/^(10|20)\d{9}$/',
                new UniqueRucRule($company->id)
            ],
            'address' => 'nullable|string|min:5',
            'logo' => 'nullable|image',
            'sol_user' => 'nullable|string|min:5',
            'sol_pass' => 'nullable|string|min:5',
            'certificate' => 'nullable|file|mimes:pem,txt',
            'client_id' => 'nullable|string',
            'client_secret' => 'nullable|string',
            'production' => 'nullable|boolean',
        ]);
        
        if($request->hasFile('logo'))
        {
            $data['logo_path'] = $request->file('logo')->store('logos');
        }
        if($request->hasFile('certificate'))
        {
            $data['certificate_path'] = $request->file('certificate')->store('certificates');
        }
        

        $company->update($data);

        return response()->json([
            'message' => 'Empresa actualizada correctamente',
            'company' => $company
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($company)
    {
        $company = Company::where('ruc', $company)
                            ->where('user_id', JWTAuth::user()->id)
                            ->firstOrFail();
        $company->delete();

        return response()->json(['message' => 'Empresa eliminada correctamente'], 200);
    }
}
