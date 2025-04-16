<!DOCTYPE html>
<html lang="tr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura</title>
</head>
<body>
<div class="container">
    <div class="invoice">
        <!-- Header -->
        <h1>Fatura</h1>
        <h2>#{{ $invoice['number'] }}</h2>
        <p><strong>Tarih:</strong> {{ $invoice['date'] }}</p>
        <p><strong>Son Ödeme Tarihi:</strong> {{ $invoice['due_date'] }}</p>

        <!-- Customer Information -->
        <div style="margin-bottom: 20px;">
            <h2>Fatura Sahibi:</h2>
            <p>{{ $invoice['customer']['name'] ?? 'Belirtilmedi' }}</p>
            <p>{{ $invoice['customer']['address'] ?? 'Adres Belirtilmedi' }}</p>
        </div>

        <!-- Item List -->
        <div style="margin-bottom: 20px;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                <tr style="background-color: #f0f0f0; text-align: left;">
                    <th style="padding: 8px; border: 1px solid #ddd;">#</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">Ürün Açıklaması</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">Birim Fiyatı</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">Adet</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">Toplam</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($invoice['items'] as $index => $item)
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd;">{{ $index + 1 }}</td>
                        <td style="padding: 8px; border: 1px solid #ddd;">{{ $item['product']['description'] ?? 'Açıklama Yok' }}</td>
                        <td style="padding: 8px; border: 1px solid #ddd;">$ {{ number_format($item['unit_price'], 2) }}</td>
                        <td style="padding: 8px; border: 1px solid #ddd;">{{ $item['quantity'] }}</td>
                        <td style="padding: 8px; border: 1px solid #ddd;">$ {{ number_format($item['unit_price'] * $item['quantity'], 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <!-- Subtotal and Total -->
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between;">
            <div>
                <p><strong>Ara Toplam:</strong>
                    $ {{ number_format(array_reduce($invoice['items'], function ($carry, $item) {
                            return $carry + ($item['unit_price'] * $item['quantity']);
                        }, 0), 2) }}
                </p>
                <p><strong>İndirim:</strong> $ {{ number_format($invoice['discount'], 2) }}</p>
            </div>
            <div>
                <h2>Genel Toplam:
                    $ {{ number_format(array_reduce($invoice['items'], function ($carry, $item) {
                            return $carry + ($item['unit_price'] * $item['quantity']);
                        }, 0) - $invoice['discount'], 2) }}
                </h2>
            </div>
        </div>
    </div>
</div>
</body>
</html>
