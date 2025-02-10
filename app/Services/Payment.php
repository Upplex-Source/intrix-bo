<?php

namespace App\Services; // Correct namespace

use IPay88\Payment\Request as IPay88Request;
use IPay88\Payment\Response as IPay88Response;

class Payment {
    protected $_merchantCode;
    protected $_merchantKey;

    public function __construct() {
        $this->_merchantCode = config('services.ipay88.merchant_code');
        $this->_merchantKey = config('services.ipay88.merchant_key');
    }

    public function createPayment($requestParams) {
        $request = new IPay88Request($this->_merchantKey);

        $data = [
            'merchantCode'  => $request->setMerchantCode($this->_merchantCode),
            'paymentId'     => $request->setPaymentId(1),
            'refNo'         => $request->setRefNo($requestParams->reference),
            'amount'        => $request->setAmount($requestParams->price),
            'currency'      => $request->setCurrency('MYR'),
            'prodDesc'      => $request->setProdDesc('Testing'),
            'userName'      => $request->setUserName($requestParams->fullname ? $requestParams->fullname : $requestParams->company_name),
            'userEmail'     => $request->setUserEmail($requestParams->email ? $requestParams->email : 'intrixguest@gmail.com' ),
            'userContact'   => $request->setUserContact($requestParams->phone_number),
            'remark'        => $request->setRemark($requestParams->remarks),
            'lang'          => $request->setLang('UTF-8'),
            'signature'     => $request->getSignature(),
            'responseUrl'   => $request->setResponseUrl(config('services.ipay88.staging_callabck_url')),
            'backendUrl'    => $request->setBackendUrl(config('services.ipay88.staging_callabck_url')),
        ];

        IPay88\Payment\Request::make($this->_merchantKey, $this->_data);
    }

    public function handleResponse() {
        $response = (new IPay88Response())->init($this->_merchantCode);
        return response()->json($response);
    }
}
