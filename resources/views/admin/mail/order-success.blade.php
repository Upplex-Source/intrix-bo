<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #F79932;
        }
        .header img {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .header h1 {
            color: #F79932;
            margin: 10px 0;
            font-size: 24px;
        }
        .content {
            line-height: 1.8;
        }
        .order-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .order-info p {
            margin: 8px 0;
        }
        .order-items {
            margin: 20px 0;
        }
        .order-items table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-items th {
            background-color: #F79932;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .order-items td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        .order-items tr:last-child td {
            border-bottom: none;
        }
        .total-section {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #ddd;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }
        .total-row.grand-total {
            font-weight: bold;
            font-size: 18px;
            color: #F79932;
            padding-top: 10px;
            border-top: 2px solid #F79932;
            margin-top: 10px;
        }
        .delivery-info {
            background-color: #f0f8ff;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .delivery-info h3 {
            margin-top: 0;
            color: #333;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #F79932;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .highlight {
            color: #F79932;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('admin/images/logo.png') }}" alt="Company Logo">
            <h1>Order Confirmed!</h1>
            <p style="color: #666; margin: 0;">Thank you for your purchase</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $order->fullname ?? $order->company_name }}</strong>,</p>

            <p>We are pleased to confirm that your order has been successfully placed and payment received.</p>

            <div class="order-info">
                <p><strong>Order Reference:</strong> <span class="highlight">{{ $order->reference }}</span></p>
                <p><strong>Order Date:</strong> {{ $order->created_at->format('d M Y, h:i A') }}</p>
                <p><strong>Payment Method:</strong> Credit Card</p>
            </div>

            @if(count($orderMetas) > 0)
            <div class="order-items">
                <h3>Order Items:</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orderMetas as $meta)
                        <tr>
                            <td>
                                <strong>{{ $meta['product']['title'] ?? 'Product' }}</strong>
                                @if($meta['color'])
                                <br><small style="color: #666;">Color: {{ $meta['color'] }}</small>
                                @endif
                            </td>
                            <td>{{ $meta['quantity'] }}</td>
                            <td>RM {{ number_format($meta['subtotal'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if(count($addOnMetas) > 0)
            <div class="order-items">
                <h3>Add-ons:</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Add-on</th>
                            <th>Quantity</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($addOnMetas as $addon)
                        <tr>
                            <td><strong>{{ $addon['add_on']['title'] ?? 'Add-on' }}</strong></td>
                            <td>{{ $addon['quantity'] }}</td>
                            <td>RM {{ number_format($addon['subtotal'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if($order->freeGift)
            <div class="order-items">
                <h3>Free Gift:</h3>
                <p style="background-color: #fff3cd; padding: 10px; border-radius: 5px;">
                    <strong>{{ $order->freeGift->title }}</strong> - Included with your order!
                </p>
            </div>
            @endif

            <div class="total-section">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>RM {{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount > 0)
                <div class="total-row">
                    <span>Discount:</span>
                    <span>- RM {{ number_format($order->discount, 2) }}</span>
                </div>
                @endif
                <div class="total-row">
                    <span>Tax:</span>
                    <span>RM {{ number_format($order->tax, 2) }}</span>
                </div>
                <div class="total-row grand-total">
                    <span>Total Amount:</span>
                    <span>RM {{ number_format($order->total_price, 2) }}</span>
                </div>
            </div>

            <div class="delivery-info">
                <h3>Delivery Information</h3>
                <p><strong>Delivery Address:</strong></p>
                <p>
                    {{ $order->address_1 }}<br>
                    @if($order->address_2)
                    {{ $order->address_2 }}<br>
                    @endif
                    {{ $order->postcode }} {{ $order->city }}<br>
                    {{ $order->state }}, {{ $order->country }}
                </p>
                <p><strong>Contact:</strong> {{ $order->phone_number }}</p>
                <p><strong>Shipping Method:</strong> Free Shipping (3 - 7 days)</p>
            </div>

            <p style="margin-top: 30px;">Your order is now being processed and will be shipped within 3-7 business days. You will receive a shipping confirmation email with tracking details once your order has been dispatched.</p>

            <p>If you have any questions or concerns about your order, please don't hesitate to contact us.</p>

            <p style="margin-top: 20px;">Thank you for shopping with us!</p>
        </div>

        <div class="footer">
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
