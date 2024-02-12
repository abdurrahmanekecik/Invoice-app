<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            'terms_and_conditions' => 'Default Term and Condition',
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

    public function store(Request $request)
    {
        $invoiceItem = $request->input('invoice_item');
        $invoicedata['sub_total'] = $request->input('sub_total');
        $invoicedata['total'] = $request->input('total');
        $invoicedata['customer_id'] = $request->input('customer_id');
        $invoicedata['number'] = $request->input('number');
        $invoicedata['date'] = $request->input('date');
        $invoicedata['due_date'] = $request->input('due_date');
        $invoicedata['discount'] = $request->input('discount');
        $invoicedata['reference'] = $request->input('reference');
        $invoicedata['terms_and_conditions'] = $request->input('term_and_condition');

        $invoice = Invoice::create($invoicedata);
        foreach (json_decode($invoiceItem) as $item) {
            $itemdata['product_id'] = $item->id;
            $itemdata['invoice_id'] = $item->id;
            $itemdata['unit_price'] = $item->unit_price;
            $itemdata['quantity'] = $item->quantity;
            InvoiceItem::create($itemdata);

        }


    }

    public function show($id)
    {

        $invoice = Invoice::with('customer', 'items.product')->find($id);
        return response()->json([
            'invoice' => $invoice], 200);
    }
}
