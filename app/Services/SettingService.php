<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

use App\Models\{
    Administrator,
};

use PragmaRX\Google2FAQRCode\Google2FA;

class SettingService {

    public static function setupMFA( $request ) {

        $request->validate( [
            'authentication_code' => [ 'bail', 'required', 'numeric', 'digits:6', function( $attribute, $value, $fail ) {
               
                $google2fa = new Google2FA();

                $valid = $google2fa->verifyKey( request( 'mfa_secret' ), $value );
                if ( !$valid ) {
                    $fail( __( 'setting.invalid_code' ) );
                }
            } ],
            'mfa_secret' => 'required',
        ] );

        $updateAdministartor = Administrator::find( auth()->user()->id );
        $updateAdministartor->mfa_secret = \Crypt::encryptString( $request->mfa_secret );
        $updateAdministartor->save();

        return response()->json( [
            'status' => true,
        ] );
    }
}