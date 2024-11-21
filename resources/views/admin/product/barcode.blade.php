<!DOCTYPE html>
<html>
<head>
    <title>Barcode PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .barcode-section {
            margin-bottom: 30px;
        }
        .barcode {
            margin-top: 10px;
            margin-bottom: 10px;
        }
        hr {
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <h1>Product Barcodes</h1>

    @foreach ($barcodes as $key => $barcode)
        <div class="barcode-section">
            <h2>{{ $barcode['product_name'] }}</h2>
            <p><strong>Price:</strong>  {{ ($barcode['product_price']) }}</p>
            <div class="barcode" style="width: {{ $width * 50 }}px; height: {{ $height * 2 }}px;">
                {!! $barcode['barcode'] !!}
            </div>
        </div>
        <hr>
    @endforeach

</body>
</html>
