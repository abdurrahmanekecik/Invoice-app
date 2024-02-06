<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('customer')->orderBy('id', 'desc')->get();
        return response()->json([
            'invoices'=> $invoices],200);
    }
    public function search(Request $request)
    {
        $search = $request->input('search');
        if($search){
            $invoices = Invoice::with('customer')->where('id', 'LIKE', "%{$search}%")->get();
        }else{
            $invoices  =  $this->index();
        }
        return response()->json([
            'invoices'=> $invoices],200);

    }
}
