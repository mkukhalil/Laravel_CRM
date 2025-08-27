<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use App\Models\Client;
use App\Notifications\LeadAssigned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            $query = Lead::with(['assignedToUser', 'creator']);
        } elseif ($user->hasRole('Manager')) {
            $query = Lead::with(['assignedToUser', 'creator'])
                ->where('assigned_by', $user->id);
        } else {
            // Agent — only see assigned leads
            $query = Lead::with(['assignedToUser', 'creator'])
                ->where('assigned_to', $user->id);
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $leads = $query->latest()->get();
        $agents = $user->hasRole(['Admin', 'Manager']) ? User::role('Agent')->get() : [];

        return view('leads.index', compact('leads', 'agents'));
    }

    public function create()
    {
        if (!Auth::user()->hasRole(['Admin', 'Manager'])) {
            abort(403, 'Unauthorized action.');
        }

        $agents = User::role('Agent')->get();
        return view('leads.create', compact('agents'));
    }

    public function store(Request $request)
{
    if (!Auth::user()->hasRole(['Admin', 'Manager'])) {
        abort(403, 'Unauthorized action.');
    }

    $validated = $request->validate([
        'name'         => 'required|string|max:255',
        'email'        => 'nullable|email',
        'phone'        => 'nullable|string|max:20',
        'source'       => 'nullable|string|max:100',
        'status'       => 'nullable|string|max:50',
        'assigned_to'  => 'nullable|exists:users,id',
    ]);

    $validated['created_by'] = Auth::id();

    if (Auth::user()->hasRole('Manager') && $request->filled('assigned_to')) {
        $validated['assigned_by'] = Auth::id();
    }

    $lead = Lead::create($validated);

    // ✅ Notify assigned Agent
    if (!empty($validated['assigned_to'])) {
        $agent = User::find($validated['assigned_to']);
        $agent->notify(new LeadAssigned($lead, Auth::user()));

    }
if (function_exists('activity')) {
    activity()
        ->causedBy(Auth::user())
        ->performedOn($lead)
        ->event('lead.created')
        ->withProperties(['assigned_to' => $lead->assigned_to])
        ->log('Lead created');
}
    return redirect()->route('leads.index')->with('success', 'Lead created successfully.');
}

    public function show(Lead $lead)
    {
        $this->authorizeView($lead);
        return view('leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        $this->authorizeView($lead);
        $agents = User::role('Agent')->get();

        return view('leads.edit', compact('lead', 'agents'));
    }

    public function update(Request $request, Lead $lead)
{
    $this->authorizeView($lead);

    $validated = $request->validate([
        'name'         => 'required|string|max:255',
        'email'        => 'nullable|email',
        'phone'        => 'nullable|string|max:20',
        'source'       => 'nullable|string|max:100',
        'status'       => 'nullable|string|max:50',
        'assigned_to'  => 'nullable|exists:users,id',
    ]);

    if (Auth::user()->hasRole('Manager') && $request->filled('assigned_to')) {
        $validated['assigned_by'] = Auth::id();
    }

    $lead->update($validated);

    // ✅ Notify assigned Agent (only if assignment changed)
    if (!empty($validated['assigned_to']) && $lead->wasChanged('assigned_to')) {
        $agent = User::find($validated['assigned_to']);
        $agent->notify(new LeadAssigned($lead, Auth::user()));

    }
    if (function_exists('activity')) {
    activity()
        ->causedBy(Auth::user())
        ->performedOn($lead)
        ->event('lead.updated')
        ->withProperties($lead->getChanges()) // shows changed fields
        ->log('Lead updated');
}

    return redirect()->route('leads.index')->with('success', 'Lead updated successfully.');
}

   public function destroy($id)
{
    $lead = Lead::findOrFail($id);
    $user = Auth::user();

    // Restrict deletion of Converted leads to Admin only
    if ($lead->status === 'Converted' && !$user->hasRole('Admin')) {
        return response()->json(['error' => 'Only Admin can delete converted leads.'], 403);
    }

    // Role-based deletion rules for non-converted
    if (
        $user->hasRole('Admin') ||
        ($user->hasRole('Manager') && $lead->assigned_by == $user->id) ||
        ($user->hasRole('Agent') && $lead->assigned_to == $user->id)
    ) {
        // If converted, remove client record too
        if ($lead->status === 'Converted') {
            Client::where('email', $lead->email)->delete();
        }

        $lead->delete();
        return response()->json(['success' => true]);
    }

    return response()->json(['error' => 'Unauthorized'], 403);
}



    protected function authorizeView(Lead $lead)
    {
        $user = Auth::user();

        if ($user->hasRole('Admin')) return;

        if ($user->hasRole('Manager') && $lead->assigned_by == $user->id) return;

        if ($user->hasRole('Agent') && $lead->assigned_to == $user->id) return;

        abort(403, 'Unauthorized');
    }

    public function convertToClient(Lead $lead)
    {
        if (Client::where('email', $lead->email)->exists()) {
            return back()->with('error', 'This lead is already converted into a client.');
        }

        $client = Client::create([
            'name'         => $lead->name,
            'email'        => $lead->email,
            'phone'        => $lead->phone,
            'company_name' => $lead->company,
            'address'      => $lead->address,
            'status'       => 'Active',
            'assigned_to'  => $lead->assigned_to,
            'created_by'   => Auth::id(),
        ]);

        $lead->status = 'Converted';
        $lead->save();

        return redirect()->route('clients.show', $client->id)
            ->with('success', 'Lead successfully converted to client.');
    }
 public function updateStatus(Request $request, $id)
{
    $lead = Lead::findOrFail($id);

    // ✅ Role-based authorization (same as authorizeView)
    $user = Auth::user();
    if ($user->hasRole('Admin')) {
        // allowed
    } elseif ($user->hasRole('Manager') && $lead->assigned_by == $user->id) {
        // allowed
    } elseif ($user->hasRole('Agent') && $lead->assigned_to == $user->id) {
        // allowed
    } else {
        abort(403, 'Unauthorized action.');
    }

    // ✅ Only Admin can move OUT of Converted
    if ($lead->status === 'Converted' && $request->status !== 'Converted' && !$user->hasRole('Admin')) {
        abort(403, 'Only Admin can move a lead out of Converted.');
    }

    $oldStatus = $lead->status;
    $lead->status = $request->status;
    $lead->save();

    // ✅ Auto-convert to client if moved into 'Converted'
    if ($request->status === 'Converted') {
        if (!Client::where('email', $lead->email)->exists()) {
            Client::create([
                'name'         => $lead->name,
                'email'        => $lead->email,
                'phone'        => $lead->phone,
                'company_name' => $lead->company_name ?? null,
                'address'      => $lead->address ?? null,
                'status'       => 'Active',
                'assigned_to'  => $lead->assigned_to,
                'created_by'   => Auth::id(),
            ]);
        }
    }

    // ✅ Remove client if moved OUT of 'Converted'
    if ($oldStatus === 'Converted' && $request->status !== 'Converted') {
        Client::where('email', $lead->email)->delete();
    }

    return response()->json(['success' => true]);
}




}
