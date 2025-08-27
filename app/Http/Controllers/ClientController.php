<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $user = Auth::user();

    if ($user->hasRole('Admin')) {
        $clients = Client::with('assignedUser')->latest()->get();
    } elseif ($user->hasRole('Manager')) {
        $clients = Client::where('created_by', $user->id)->latest()->get();
    } else {
        $clients = Client::where('assigned_to', $user->id)->latest()->get();
    }

    return view('clients.index', compact('clients'));
}


    /**
     * Show the form for creating a new resource.
     */
 public function create()
{
    return view('clients.create');
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'name'         => 'required|string|max:255',
        'email'        => 'required|email|unique:clients,email',
        'phone'        => 'nullable|string|max:20',
        'company_name' => 'nullable|string|max:255',
        'address'      => 'nullable|string',
        'status'       => 'required|in:Active,Inactive,Prospect',
        'assigned_to'  => 'required|exists:users,id',
    ]);

    $validated['created_by'] = Auth::id();

    Client::create($validated);

    return redirect()->route('clients.index')->with('success', 'Client created successfully.');
}


    /**
     * Display the specified resource.
     */
    public function show(Client $client)
{
    return view('clients.show', compact('client'));
}


    /**
     * Show the form for editing the specified resource.
     */
   public function edit(Client $client)
{
    return view('clients.edit', compact('client'));
}


    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, Client $client)
{
    $validated = $request->validate([
        'name'         => 'required|string|max:255',
        'email'        => 'required|email|unique:clients,email,' . $client->id,
        'phone'        => 'nullable|string|max:20',
        'company_name' => 'nullable|string|max:255',
        'address'      => 'nullable|string',
        'status'       => 'required|in:Active,Inactive,Prospect',
        'assigned_to'  => 'required|exists:users,id',
    ]);

    $client->update($validated);

    return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
}


    /**
     * Remove the specified resource from storage.
     */
   public function destroy(Client $client)
{
    $client->delete();

    return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
}

}
