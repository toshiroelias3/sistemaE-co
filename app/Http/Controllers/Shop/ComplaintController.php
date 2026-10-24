<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Mail\ComplaintRegistered;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class ComplaintController extends Controller
{
    /**
     * Formulario del Libro de Reclamaciones virtual
     */
    public function create()
    {
        return view('shop.complaints.create');
    }

    /**
     * Registra la hoja de reclamación y envía la copia al consumidor
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'consumer_name'    => 'required|string|max:255',
            'document_type'    => ['required', Rule::in(Complaint::DOCUMENT_TYPES)],
            'document_number'  => 'required|string|max:20',
            'address'          => 'required|string|max:255',
            'phone'            => ['required', 'string', 'max:30', 'regex:/^[0-9+\s-]{6,30}$/'],
            'email'            => 'required|email|max:255',
            'is_minor'         => 'boolean',
            'guardian_name'    => 'required_if_accepted:is_minor|nullable|string|max:255',
            'item_type'        => ['required', Rule::in(array_keys(Complaint::ITEM_TYPES))],
            'order_reference'  => 'nullable|string|max:60',
            'amount'           => 'nullable|numeric|min:0|max:99999999',
            'item_description' => 'required|string|max:255',
            'type'             => ['required', Rule::in(array_keys(Complaint::TYPES))],
            'detail'           => 'required|string|max:3000',
            'consumer_request' => 'required|string|max:2000',
            'accepted'         => 'accepted',
        ], [
            'guardian_name.required_if_accepted' => 'Si eres menor de edad, indica el nombre de tu padre, madre o apoderado.',
            'accepted.accepted' => 'Debes confirmar que los datos son correctos.',
            'phone.regex' => 'Ingresa un número de teléfono válido.',
        ]);

        $complaint = Complaint::create(array_merge(
            collect($data)->except('accepted')->map(fn ($v) => is_string($v) ? strip_tags($v) : $v)->all(),
            ['user_id' => Auth::id(), 'is_minor' => $request->boolean('is_minor'), 'status' => 'pendiente']
        ))->refresh();

        // La copia por correo no debe impedir que la hoja quede registrada
        try {
            Mail::to($complaint->email)->send(new ComplaintRegistered($complaint));
        } catch (\Throwable $e) {
            Log::error("No se pudo enviar la copia de la hoja {$complaint->code}: {$e->getMessage()}");
        }

        return redirect()->route('complaints.show', $complaint->token)
            ->with('success', "Registramos tu hoja de reclamación {$complaint->code}. Te enviamos una copia a {$complaint->email}.");
    }

    /**
     * Constancia de la hoja (enlace privado con token)
     */
    public function show(string $token)
    {
        $complaint = Complaint::where('token', $token)->firstOrFail();

        return view('shop.complaints.show', compact('complaint'));
    }
}
