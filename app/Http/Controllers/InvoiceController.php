<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Barryvdh\DomPDF\Facade\Pdf;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

    public function edit($id)
    {
        $invoice = Invoice::with('customer', 'items.product')->find($id);
        return response()->json([
            'invoice' => $invoice], 200);
    }

    public function update(Request $request, $id)
    {
        $invoice = Invoice::find($id);

        $invoice->sub_total = $request->input('sub_total');
        $invoice->total = $request->input('total');
        $invoice->customer_id = $request->input('customer_id');
        $invoice->number = $request->input('number');
        $invoice->date = $request->input('date');
        $invoice->due_date = $request->input('due_date');
        $invoice->discount = $request->input('discount');
        $invoice->reference = $request->input('reference');
        $invoice->terms_and_conditions = $request->input('term_and_condition');
        $invoice->save();
        $invoiceItem = $request->input('invoice_item');
        $invoice->items()->delete();

        foreach (json_decode($invoiceItem) as $item) {
            $itemdata['product_id'] = $item->product_id;
            $itemdata['invoice_id'] = $item->id;
            $itemdata['unit_price'] = $item->unit_price;
            $itemdata['quantity'] = $item->quantity;
            InvoiceItem::create($itemdata);

        }
    }

    public function destroy($id)
    {
        $invoice = Invoice::find($id);
        $invoice->items()->delete();
        $invoice->delete();
    }

    public function sendAi(Request $request)
    {
        $companyUuid = '324ae4a9-61d1-443b-89b4-d48fc123f2ab';
        $authToken = "1Y993BY-3STMW61-G0YA6EQ-CPZ77KS";

        $client = new Client([
            'headers' => [
                'Authorization' => "Bearer {$authToken}",
                'Accept' => 'application/json',
            ],
        ]);

        try {
            // Workspace sorgusu
            $response = $client->get("https://slhyosb0.repcl.net/api/v1/workspace/". $companyUuid);
            $workspace = json_decode($response->getBody()->getContents(), true);






            if (!$workspace || empty($workspace['workspace'])) {

                $client = new Client([
                    'headers' => [
                        'Authorization' => "Bearer {$authToken}",
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/x-www-form-urlencoded',

                    ],
                ]);

                $response = $client->post("https://slhyosb0.repcl.net/api/v1/workspace/new", [
                    'form_params' => [
                        'name' => $companyUuid,
                    ],
                ]);

                $workspaceResponse = json_decode($response->getBody()->getContents(), true);


                if (!$workspaceResponse || !isset($workspaceResponse['workspace']['id'])) {
                    Log::error('Workspace oluşturulamadı.', ['response' => $workspaceResponse]);
                    return response()->json([
                        'error' => 'Workspace oluşturulamadı.',
                    ], 500);
                }


                Log::info('Workspace başarıyla oluşturuldu.', ['workspace' => $workspaceResponse]);
            } else {
                Log::info('Zaten mevcut workspaces bulundu.', ['workspaces' => $workspace]);
            }

            // Fatura işleme
            $invoiceIds = $request->input('invoiceIds', []);

            foreach ($invoiceIds as $invoiceId) {
                $invoice = Invoice::with('customer', 'items.product')->find($invoiceId);





                if (!$invoice && $invoice->ai_status == 1) {
                    Log::warning("Fatura bulunamadı veya gönderilmiş. ID: $invoiceId");
                    continue;
                }

                // PDF oluşturma
                $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice->toArray()]);
                $pdfPath = "invoices/{$invoice->id}.pdf";

                // PDF'i public storage alanına kaydetme
                Storage::disk('public')->put($pdfPath, $pdf->output());

                // API'ye yükleme
                $client = new Client();

                try {
                    $response = $client->post('https://slhyosb0.repcl.net/api/v1/document/upload', [
                        'headers' => [
                            'Authorization' => "Bearer {$authToken}",

                        ],
                        'multipart' => [
                            [
                                'name' => 'file',
                                'contents' => fopen(Storage::disk('public')->path($pdfPath), 'r'),
                                'filename' => basename($pdfPath),
                            ],
                        ],
                    ]);

                    $responseBody = json_decode($response->getBody()->getContents(), true);



                    if ($response->getStatusCode() === 200) {



                        $newRequestBody = [
                            "adds" => array_map(function ($document) {
                                return $document['location'];
                            }, $responseBody['documents']) // Documents içerisindeki 'location' alanını alıyoruz
                        ];



                        // Yeni istek için Guzzle Client
                        $client = new Client();

                        // Yeni isteği oluşturma
                        $newResponse = $client->post('https://slhyosb0.repcl.net/api/v1/workspace/'. $companyUuid .'/update-embeddings', [
                            'headers' => [
                                'Content-Type' => 'application/json',
                                'Authorization' => "Bearer {$authToken}", // Authorization Token
                            ],
                            'json' => $newRequestBody,
                        ]);

                        // Yeni isteğin yanıtını işleme
                        if ($newResponse->getStatusCode() === 200) {
                            $newResponseBody = json_decode($newResponse->getBody()->getContents(), true);
                            Log::info("Yeni istek başarıyla gerçekleştirildi.", ['response' => $newResponseBody]);
                        } else {
                            Log::warning("Yeni istekte beklenmeyen bir durum oluştu.", [
                                'status_code' => $newResponse->getStatusCode(),
                                'response' => $newResponse->getBody()->getContents(),
                            ]);
                        }
                    }else {
                        Log::warning("Fatura yükleme sırasında beklenmeyen bir durum oluştu.", ['response' => $responseBody]);
                    }
                } catch (\Exception $e) {
                    Log::error("Fatura yükleme başarısız oldu.", [
                        'error' => $e->getMessage(),
                    ]);
                }

                if ($response === false) {
                    Log::error("Fatura yükleme başarısız oldu. ID: $invoiceId");
                } else {
                    Log::info("Fatura başarıyla yüklendi. ID: $invoiceId");
                }

                // AI durumunu güncelleme
                $invoice->ai_status = 1;
                $invoice->save();
            }

            return response()->json(['message' => 'Faturalar işlendi ve yüklendi.'], 200);

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('API isteği başarısız.', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'API isteği başarısız: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            Log::error('Hata oluştu.', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function chatAi(Request $request)
    {
        $companyUuid = '324ae4a9-61d1-443b-89b4-d48fc123f2ab';
        $authToken = "1Y993BY-3STMW61-G0YA6EQ-CPZ77KS";

        $client = new Client([
            'headers' => [
                'Authorization' => "Bearer {$authToken}",
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        try {
            // İstek gövdesi
            $body = json_encode([
                'message' => $request->input('message'),
                'mode' => 'chat'
            ]);

            // Workspace sorgusu
            $response = $client->post("https://slhyosb0.repcl.net/api/v1/workspace/" . $companyUuid . '/chat', [
                'body' => $body
            ]);

            $responseBody = json_decode($response->getBody(), true);

            return response()->json([
                'status' => 'success',
                'response' => $responseBody['textResponse'] ?? 'No response received.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process chat: ' . $e->getMessage(),
            ], 500);
        }
    }

}
