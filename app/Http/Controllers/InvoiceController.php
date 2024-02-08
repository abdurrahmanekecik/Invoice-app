<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('customer')->orderBy('id', 'desc')->get();
        return response()->json([
            'invoices' => $invoices], 200);
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        if ($search) {
            $invoices = Invoice::with('customer')->where('id', 'LIKE', "%{$search}%")->get();
        } else {
            $invoices = $this->index();
        }
        return response()->json([
            'invoices' => $invoices], 200);

    }

    public function create(Request $request)
    {
        $counter = Counter::where('key', 'invoice')->first();
        $random = Counter::where('key', 'invoice')->first();

        $invoice = Invoice::orderBy('id', 'desc')->first();
        if ($invoice) {
            $invoice = $invoice->id + 1;
            $counters = $counter->value + $invoice;

        } else {
            $counters = $counter->value;
        }

        $formData = [
            'number' => $counter->prefix . $counters,
            'customer_id' => null,
            'customer_name' => null,
            'date' => date('Y-m-d'),
            'due_date' => date('Y-m-d'),
            'reference' => null,
            'discount' => 0,
            'term_and_condition' => 'Default Term and Condition',
            'items' => [
                [
                    'product_id' => null,
                    'product' => null,
                    'unit_price' => 0,
                    'quantity' => 1,
                ]
            ]
        ];


        return response()->json([
            'formData' => $formData], 200);


    }
}
