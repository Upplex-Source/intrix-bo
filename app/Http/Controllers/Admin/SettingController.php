<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    SettingService,
};

use Helper;

use PragmaRX\Google2FAQRCode\Google2FA;

class SettingController extends Controller
{
    public function firstSetup( Request $request ) {

        if ( !empty( auth()->user()->mfa_secret ) ) {
            return redirect()->route( 'admin.dashboard' );
        }

        $this->data['header']['title'] = __( 'template.first_setup' );
        
        $this->data['content'] = 'admin.setting.first_setup';

        $google2fa = new Google2FA();

        $secretKey = $google2fa->generateSecretKey();

        $qrCodeUrl = $google2fa->getQRCodeInline(
            Helper::websiteName(),
            auth()->user()->email,
            $secretKey
        );

        $this->data['data']['mfa_qr'] = $qrCodeUrl;
        $this->data['data']['mfa_secret'] = $secretKey;

        return view( 'admin.main_pre_auth' )->with( $this->data );
    }

    public function setupMFA( Request $request ) {

        return SettingService::setupMFA( $request );
    }
}
