<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Invoice</title>
        <style>
            * {
                margin: 0;
            }

            body {
                font-family: Arial, sans-serif;
            }

            .invoice {
                width: 80%;
                margin: 0 auto;
                padding: 20px;
            }

            .invoice-header {
                text-align: center;
                margin-bottom: 20px;
            }

            .invoice-details {
                margin-bottom: 20px;
            }

            .invoice-items {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
                margin-top:90px;
            }

            .invoice-items th, .invoice-items td {
                padding: 8px;
                text-align: left;
            }

            .invoice-total {
                text-align: right;
            }

            .invoice-footer {
                text-align: center;
            }

            span {
                font-size:9px;
            }

            th {
                font-weight: normal;
            }

            thead {
                border-top: 1px black solid;
                border-bottom: 1px black solid;
            }

            .footer {
                padding:20px;
            }
        </style>
    </head>
    <body>
        @php
            $uom_decimal = [
                __( 'booking.ton' ) => 2,
                __( 'booking.trip' ) => 0,
                __( 'booking.pallets' ) => 4,
            ];
        @endphp
        <div class="invoice" @if(!empty($type) && $type == 'preview') style="max-width:700px;" @endif>
            <div class="invoice-header">
                <span style="margin-bottom:5px;font-weight:bold; font-size:16px;">{{ $company->name }}</span><br>
                <span>REGISTRATION NO: {{ $company->registration_no }}</span><br>
                <div style="margin-left:auto;margin-right:auto;max-width:250px;font-size:10px;">{{ $company->address }}</div>
                <span>TEL:{{ $company->phone_number }} &nbsp; Email:{{ $company->email }}</span><br>
                <br>
                <span style="font-weight:bold; font-size:16px">INVOICE</span>
            </div>
            <div style="margin-bottom:50px;">
                <span style="width:50%;float:left;">
                    To: {{ $customer->name }}
                    <br>
                    <div style="max-width:150px;">{{ $customer->address }}</div>
                    <div style="max-width:150px;">{{ $customer->address_2 }}</div>
                </span>
                <div style="width:50%;float:right;text-align:right">
                    <span style="font-weight:600;">INVOICE No: {{ $invoice_detail['invoice_number'] }}</span>
                    <br>
                    <span style="margin-right:25px;">Date: {{ $invoice_detail['invoice_date'] }}</span>
                </div>
            </div>
            <table class="invoice-items">
                <thead>
                    <tr>
                        <th>
                            <span>D/O <br> DATE</span>
                        </th>
                        <th style="max-width:60px !important;">
                            <span>REF NO.</span>
                        </th>
                        <th>
                            <span>LORRY NO</span>
                        </th>
                        <th>
                            <span>DESTINATION</span>
                        </th>
                        <th>
                            <span>QTY</span>
                        </th>
                        <th>
                            <span>RATE</span>
                        </th>
                        <th>
                            <span>AMOUNT <br> (RM)</span>
                        </th>
                        <th>
                            <span>&nbsp;</span>
                        </th>
                    </tr>
                </thead>
                @php
                    $nett = 0;
                    $row = 0;
                @endphp
                <tbody style="min-height:800px;">
                    @foreach($grouped as $plate => $group)
                        @foreach($group as $key => $item)
                            @if($key == 'items')
                                @php
                                    $nett += $group['total_amount'];
                                @endphp

                                @foreach($item as $data)
                                    @php
                                        $row++;
                                    @endphp

                                    <tr>
                                        <td>
                                            <span>{{ $data->delivery_order_date ?? '' }}<br> &nbsp;</span>
                                        </td>
                                        <td style="min-width:100px;">
                                            <span>
                                                @foreach($data->references as $reference)
                                                    {{ $reference }}{{ !$loop->last ? ',' : '' }}
                                                    @if($loop->even && !$loop->last)
                                                        <br>
                                                    @endif
                                                    @if($loop->count == 1 && $loop->first || $loop->count == 2 && $loop->last)
                                                        <br> &nbsp;
                                                    @endif
                                                @endforeach
                                            </span>
                                        </td>
                                        <td>
                                            <span>{{ $data->license_plate ?? '' }} <br> @if(!$loop->last) <br><br> @elseif($loop->last) <strong>{{ $data->license_plate ?? '' }}</strong> @endif</span>
                                        </td>
                                        <td style="max-width:100px;">
                                            <span>{{ $data->pickup_address->city ?? '' }} - {{ $data->dropoff_address->city ?? '' }}</span>
                                        </td>
                                        <td>
                                            <span>{{ number_format($data->customer_quantity, $uom_decimal[$data->customer_unit_of_measurement]) ?? '' }} {{ $data->customer_unit_of_measurement ?? '' }}<br> &nbsp;</span>
                                        </td>
                                        <td>
                                            <span>{{ number_format($data->customer_rate, 2) ?? '' }}<br> &nbsp;</span>
                                        </td>
                                        <td>
                                            <span>{{ number_format($data->customer_total_amount, 2) ?? '' }}<br> &nbsp;</span>
                                        </td>
                                        <td>
                                            @if($loop->last)
                                                <span><strong><br>{{ number_format($group['total_amount'], 2) ?? ''  }}</strong></span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                    @endforeach
                    @if($row <= 12)
                        @for($i = 1; $i <= (12 - $row); $i++)
                            <tr>
                                <td>&nbsp;</td>
                            </tr>
                        @endfor
                    @endif
                </tbody>
            </table>
            <div class="invoice-total" style="border-top:1px solid black;border-bottom:1px solid black; padding: 5px 0;">
                <div>
                    <span><strong>Nett Amount:</strong></span>
                    <span style="margin:0 30px;">${{ number_format($nett, 2) ?? '' }}</span>
                </div>
            </div>
            <div class="footer">
                <div style="float:left;width:70%;">
                    <span>All cheques to be crosses and payable to the order of {{ $company->name }} or to be credited into our bank {{ $company->bank_name }} account to {{ $company->account_no }}</span>
                    <br><br><br>
                    <span>Any descrepancies to be reported and settled within (7) days from invoice date.</span>
                </div>
                <div style="float:right;width:29%;">
                    <div style="margin-left:30px">
                        <span style="font-size:11px;">
                            <strong style="margin:0;padding:0;">{{ $company->name }}</strong>
                        </span>
                        <br><br><br><br>
                        <span style="display:block;text-align:center;border-top:1px solid black;padding-top:5px;">Authrised Signature</span>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>