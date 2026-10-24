<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    /**
     * Hojas de reclamación recibidas, pendientes primero
     */
    public function index()
    {
        $complaints = Complaint::orderByRaw("status = 'pendiente' desc")->latest()->paginate(15);

        return view('admin.complaints.index', compact('complaints'));
    }

    public function show(Complaint $complaint)
    {
        return view('admin.complaints.show', compact('complaint'));
    }

    /**
     * Registra la respuesta del proveedor
     */
    public function update(Request $request, Complaint $complaint)
    {
        $request->validate(['response' => 'required|string|max:5000']);

        $complaint->update([
            'response'     => $request->response,
            'status'       => 'respondido',
            'responded_at' => $complaint->responded_at ?? now(),
        ]);

        return redirect()->route('admin.complaints.show', $complaint)
            ->with('success', 'Respuesta registrada. Recuerda enviarla también al correo del consumidor.');
    }
}
