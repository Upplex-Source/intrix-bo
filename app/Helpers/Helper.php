<?php

namespace App\Helpers;

use Hashids\Hashids;
use Carbon\Carbon;

use App\Models\{
    OtpAction,
    TmpUser,
    Option,
    Order,
    Adjustment,
};

use Illuminate\Support\Facades\{
    Crypt
};

class Helper {

    public static function websiteName() {
        return config( 'app.name' );
    }

    public static function assetVersion() {
        return '?v=1.02';
    }

    public static function moduleActions() {

        return [
            'add',
            'view',
            'edit',
            'delete'
        ];
    }

    public static function taxTypes() {
        return [
            1 => [
                'title' => 'SST',
                'description' => 'Sales and Service Tax',
                'percentage' => 6,
                'type' => 'service',
            ],
            2 => [
                'title' => 'Sales Tax',
                'description' => 'Tax on goods',
                'percentage' => 10,
                'type' => 'sales',
            ],
        ];
    }

    public static function QuotationStatuses() {
        return [
            10 => 'Quotation',
            12 => 'Sales Order',
            13 => 'Invoice',
            14 => 'Delivery Order',
        ];
    }

    public static function numberFormat( $number, $decimal, $isRound = false ) {
        if ( $isRound ) {
            return number_format( $number, $decimal );    
        } else {
            return number_format( bcdiv( $number, 1, $decimal ), $decimal );
        }
    }

    public static function numberFormatV2( $number, $decimal, $displayComma = false, $isRound = false ) {
        $formatted = '';
        if ( $isRound ) {
            $formatted = number_format( $number, $decimal );
        } else {
            $formatted = number_format( bcdiv( $number, 1, $decimal ), $decimal );
        }

        if ( $displayComma ) {
            return $formatted;
        } else {
            return str_replace( ',', '', $formatted );
        }
    }

    public static function numberFormatNoComma( $number, $decimal ) {
        return str_replace( ',', '', number_format( $number, $decimal ) );
    }

    public static function curlGet( $endpoint, $header = array(

    ) ) {

        $curl = curl_init();

        curl_setopt_array( $curl, array(
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
        ) );

        $response = curl_exec ($curl );
        $error = curl_error( $curl );
        
        curl_close( $curl );

        if( $error ) {
            return false;
        } else {
            return $response;
        }
    }

    public static function curlPost( $endpoint, $data, $header = array(
        "accept: */*",
        "accept-language: en-US,en;q=0.8",
        "content-type: application/json",
    ) ) {
        
        $curl = curl_init();
        
        curl_setopt_array( $curl, array(
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => $header
        ) );
        
        $response = curl_exec ($curl );
        $error = curl_error( $curl );
        
        curl_close( $curl );
        
        if( $error ) {
            return false;
        } else {
            return $response;
        }
    }

    public static function exportReport( $html, $model ) {

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
        $spreadsheet = $reader->loadFromString( $html );

        foreach( $spreadsheet->getActiveSheet()->getColumnIterator() as $column ) {
            $spreadsheet->getActiveSheet()->getColumnDimension( $column->getColumnIndex() )->setAutoSize( true );
        }

        $filename = $model . '_' . date( 'ymd_His' ) . '.xlsx';

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter( $spreadsheet, 'Xlsx' );
        $writer->save( 'storage/'.$filename );

        $content = file_get_contents( 'storage/'.$filename );

        header( "Content-Disposition: attachment; filename=".$filename );
        unlink( 'storage/'.$filename );
        exit( $content );
    }

    public static function columnIndex( $object, $search ) {
        foreach ( $object as $key => $o ) {
            if ( $o['id'] == $search ) {
                return $key;
            }
        }
    }

    public static function encode( $id ) {

        $hashids = new Hashids( config( 'app.key' ) );

        return $hashids->encode( $id );
    }

    public static function decode( $id ) {

        $hashids = new Hashids( config( 'app.key' ) );

        return $hashids->decode( $id )[0];
    }

    public function adminNotifications() {

        $notifications = AdminNotification::select( 
            'admin_notifications.*',
            \DB::raw( '( SELECT COUNT(*) FROM admin_notification_seens AS a WHERE a.admin_notification_id = admin_notifications.id AND a.admin_id = ' .auth()->user()->id. ' ) as is_read' )
        )->where( function( $query ) {
            $query->where( 'admin_id', auth()->user()->id );
            $query->orWhere( 'role_id', auth()->user()->role );
        } )->orWhere( function( $query ) {
            $query->whereNull( 'admin_id' );
            $query->whereNull( 'role_id' );
        } )->orderBy( 'admin_notifications.created_at', 'DESC' )->get();

        $totalUnread = AdminNotificationSeen::where( 'admin_id', auth()->user()->id )->count();

        $data['total_unread'] = count( $notifications ) - $totalUnread;
        $data['notifications'] = $notifications;

        $data['is_notification_box_opened'] = 0;
        $nbo = AdminMeta::where( 'meta_key', 'is_notification_box_opened' )->first();
        if ( $nbo ) {
            $data['is_notification_box_opened'] = $nbo->meta_value;
        }

        return $data;
    }

    public static function getDisplayTimeUnit( $createdAt ) {

        $created = Carbon::createFromFormat( 'Y-m-d H:i:s', $createdAt, 'UTC' )->timezone( 'Asia/Kuala_Lumpur' );
        $now = Carbon::now()->timezone( 'Asia/Kuala_Lumpur' );

        if ( $created->format( 'd' ) != $now->format( 'd' ) ) {

            $difference = $created->clone()->startOfDay()->diff( $now->startOfDay() )->days;
            if ( $difference == 1 ) {
                return __( 'template.yesterday' ) . ' ' . $created->format( 'H:i' );
            } else {
                return $created->format( 'd-m-Y H:i' );
            }

        } else {
            return $created->format( 'H:i' );
        }
    }   
    public static function requestOtp( $action, $data = [] ) {

        $expireOn = Carbon::now()->addMinutes( '10' );

        if ( $action == 'register' ) {

            $callingCode = $data['calling_code'];
            $phoneNumber = $data['phone_number'];
            $email = $data['email'];

            $createOtp = TmpUser::create( [
                'calling_code' => $data['calling_code'],
                'phone_number' => $data['phone_number'],
                'email' => $data['email'],
                'otp_code' => mt_rand( 100000, 999999 ),
                'status' => 1,
                'expire_on' => $expireOn,
            ] );

            $body = 'Your OTP for Infinite Design ' . $action . ' is ' . $createOtp->otp_code;

        } 
        else if ( $action == 'resend' ) {

            $callingCode = $data['calling_code'];
            $tmpUser = $data['tmp_user'];

            $createOtp = TmpUser::find( $tmpUser );
            $createOtp->otp_code = mt_rand( 100000, 999999 );
            $createOtp->expire_on = $expireOn;
            $createOtp->save();

            $phoneNumber = $createOtp->phone_number;

            $body = 'Your OTP for Infinite Design ' . $action . ' is ' . $createOtp->otp_code;

        } 
        else if ( $action == 'forgot_password' ) {

            // $callingCode = $data['calling_code'];
            // $phoneNumber = $data['phone_number'];
            $email = $data['email'];      

            // set previous to status 10
            $resetOtps = OtpAction::where( 'user_id', $data['id'] )->where( 'status', 1 )->update(['status' => 10]);
            
            $createOtp = OtpAction::create( [
                'user_id' => $data['id'],
                'action' => $action,
                'otp_code' => mt_rand( 100000, 999999 ),
                'expire_on' => $expireOn,
            ] );

            $body = 'Your OTP for Infinite Design forgot password is ' . $createOtp->otp_code;

        }else if ( $action == 'update_account' ) {

            // $callingCode = $data['calling_code'];
            // $phoneNumber = $data['phone_number'];
            $email = $data['email'];      
            
            $createOtp = OtpAction::create( [
                'user_id' => $data['id'],
                'action' => $action,
                'otp_code' => mt_rand( 100000, 999999 ),
                'expire_on' => $expireOn,
            ] );

            $body = 'Your OTP for Infinite Design update account is ' . $createOtp->otp_code;

        }else {

            $currentUser = auth()->user();

            // $callingCode = $currentUser->calling_code;
            // $phoneNumber = $currentUser->phone_number;
            $email = $data['email'];      

            $createOtp = OtpAction::create( [
                'user_id' => $currentUser->id,
                'action' => $action,
                'otp_code' => mt_rand( 100000, 999999 ),
                'expire_on' => $expireOn,
            ] );

            $body = 'Your OTP for Infinite Design ' . $action . ' is ' . $createOtp->otp_code;
        }

        return [
            'action' => $action,
            'identifier' => Crypt::encryptString( $createOtp->id ),
            'otp_code' => $createOtp->otp_code,
        ];
    }

    public static function generateAdjustmentNumber()
    {
        return now()->format('YmdHis');
    }

    public static function generatePurchaseNumber()
    {
        return 'PCR' . now()->format('YmdHis');
    }

    public static function generatePurchaseTransactionNumber()
    {
        return 'PCR-T-' . now()->format('YmdHis');
    }

    public static function generateExpenseNumber()
    {
        return 'EXP-T-' . now()->format('YmdHis');
    }

    public static function generateQuotationNumber()
    {
        return 'QWT-' . now()->format('YmdHis');
    }

    public static function generateSalesOrderNumber()
    {
        return 'SO-' . now()->format('YmdHis');
    }

    public static function generateInvoiceNumber()
    {
        return 'INV-' . now()->format('YmdHis');
    }

    public static function generateDeliveryOrderNumber()
    {
        return 'DLV-' . now()->format('YmdHis');
    }
    
}
