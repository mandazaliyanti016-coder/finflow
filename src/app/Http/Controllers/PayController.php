<?php

namespace App\Http\Controllers;

use App\Models\Invoice;

class PayController extends Controller
{
    public function show(string $ref)
    {
        $invoice = Invoice::where('external_ref', $ref)->firstOrFail();

        if ($invoice->status === 'pending' && $invoice->expires_at && $invoice->expires_at->isPast()) {
            $invoice->update(['status' => 'expired']);
        }

        return view('pay.show', compact('invoice'));
    }
}
