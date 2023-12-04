<?php

namespace App\Http\Controllers\Admin;

use Helper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Request;

use App\Services\{
    CompanyService,
    InvoiceService,
};
use Illuminate\Support\Facades\DB;
use stdClass;

class InvoiceController extends Controller
{
    public function previewInvoice(Request $request)
    {
        if(!$request->id){
            InvoiceService::validateInputs($request);
        }else{
            $request->merge( [
                'id' => Helper::decode( $request->id ),
            ] );

            $oldRequest = $request;
            $request = new stdClass;

            $invoice = Invoice::with( [
                'customer',
                'company',
            ] )->find( $oldRequest->id );

            $request->company_id = $invoice->company_id;
            $request->customer_id = $invoice->customer_id;
            $request->invoice_number = $invoice->invoice_number;
            $request->invoice_date = $invoice->invoice_date;
            $request->delivery_order_number = $invoice->do_number;
        }

        $data = InvoiceService::retrieveInvoiceDetail($request);

        $company = Company::find($request->company_id);
        $customer = Customer::find($request->customer_id);
        $invoice_detail['invoice_number'] = $request->invoice_number;
        $invoice_detail['invoice_date'] = $request->invoice_date;
        $grouped = [];
        $uom = [
            '1' => __( 'booking.ton' ),
            '2' => __( 'booking.trip' ),
            '3' => __( 'booking.pallets' ),
        ];

        foreach ($data as $item) {
            $licensePlate = $item->license_plate;
            
            if (!isset($grouped[$licensePlate])) {
                $grouped[$licensePlate] = [
                    'items' => [],
                'total_amount' => 0,
                ];
            }

            $item->references = explode(',', $item->references);
            $item->customer_unit_of_measurement = $uom[$item->customer_unit_of_measurement];

            $item->pickup_address = DB::table('booking_addresses')->where(['booking_id' => $item->id, 'type' => 1])->first();
            $item->dropoff_address = DB::table('booking_addresses')->where(['booking_id' => $item->id, 'type' => 2])->first();
            $grouped[$licensePlate]['items'][] = $item;
            $grouped[$licensePlate]['total_amount'] += floatval($item->customer_total_amount);
            $type = 'preview';
        }
        return view('admin.invoice.preview', compact('grouped', 'invoice_detail', 'company', 'customer', 'type'));
    }

    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.invoices' );
        $this->data['content'] = 'admin.invoice.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.invoices' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '1' => __( 'datatables.pending' ),
        ];
        $this->data['data']['company'] = InvoiceService::get();

        return view('admin.main')->with($this->data);
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.invoices' ) ) ] );
        $this->data['content'] = 'admin.invoice.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.invoice.index' ),
                'text' => __( 'template.invoices' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.invoices' ) ) ] ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['company'] = CompanyService::get();
        $this->data['data']['uom'] = [
            '1' => __( 'booking.ton' ),
            '2' => __( 'booking.trip' ),
            '3' => __( 'booking.pallets' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allInvoices( Request $request ) {
        return InvoiceService::allInvoices( $request );
    }

    public function oneInvoice( Request $request ){
        return InvoiceService::oneInvoice( $request );
    }

    public function downloadInvoice( Request $request ){
        return InvoiceService::downloadInvoice( $request );
    }

    public function createInvoice( Request $request ) {
        return InvoiceService::createInvoice( $request );
    }

    public function updateInvoice( Request $request) {
        return InvoiceService::updateInvoice( $request ); 
    }

    public function deleteInvoice( Request $request) {
        return InvoiceService::deleteInvoice( $request );
    }
}
