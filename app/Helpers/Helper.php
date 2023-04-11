<?php

namespace App\Helpers;

use Hashids\Hashids;
use Carbon\Carbon;

class Helper {

    public static function websiteName() {
        return config( 'app.name' );
    }

    public static function assetVersion() {
        return '?v=1.00';
    }

    public static function moduleActions() {

        return [
            'add',
            'view',
            'edit',
            'delete'
        ];
    }

    public static function numberFormat( $number, $decimal ) {
        return number_format( $number, $decimal );
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

    public function getDisplayTimeUnit( $createdAt ) {

        $created = new Carbon( $createdAt );
        $now = Carbon::now();

        if ( $created->format( 'd' ) != $now->format( 'd' ) ) {

            $difference = $created->startOfDay()->diff( $now->startOfDay() )->days;
            if ( $difference == 1 ) {
                return __( 'template.yesterday' );
            } else {
                if ( $difference <= 7 ) {
                    return __( 'template.' . strtolower( $created->format( 'l' ) ) );
                } else {
                    return $created->format( 'd/m/Y' );
                }
            }

        } else {
            return $created->setTimezone( 'Asia/Kuala_Lumpur' )->format( 'H:i' );
        }
    }
}
