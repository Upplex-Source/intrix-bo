<?php

namespace App\Services;

use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Hash,
    Validator,
    Mail,
    Crypt,
};

use App\Mail\EnquiryEmail;
use App\Mail\OtpMail;

use Illuminate\Validation\Rules\Password;

use App\Models\{
    Guest,
    OtpAction,
    TmpUser,
    MailContent,
    Wallet,
    Option,
    WalletTransaction,
    GuestNotification,
    GuestNotificationSeen,
    GuestNotificationGuest,
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

use PragmaRX\Google2FAQRCode\Google2FA;

class GuestService
{
    public static function allGuests( $request ) {

        $guest = Guest::select( 'guests.*' )->orderBy( 'created_at', 'DESC' );

        $filterObject = self::filter( $request, $guest );
        $guest = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'guest.0.column' ) != 0 ) {
            $dir = $request->input( 'guest.0.dir' );
            switch ( $request->input( 'guest.0.column' ) ) {
                case 1:
                    $guest->orderBy( 'created_at', $dir );
                    break;
                case 2:
                    $guest->orderBy( 'guestname', $dir );
                    break;
                case 3:
                    $guest->orderBy( 'email', $dir );
                    break;
            }
        }

        $guestCount = $guest->count();

        $limit = $request->length;
        $offset = $request->start;

        $guests = $guest->skip( $offset )->take( $limit )->get();

        if ( $guests ) {
            $guests->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = Guest::count();

        $data = [
            'guests' => $guests,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $guestCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->registered_date ) ) {
            if ( str_contains( $request->registered_date, 'to' ) ) {
                $dates = explode( ' to ', $request->registered_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'guests.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->registered_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'guests.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->guestname ) ) {
            $model->where( 'guestname', 'LIKE', '%' . $request->guestname . '%' );
            $filter = true;
        }

        if ( !empty( $request->email ) ) {
            $model->where( 'email', 'LIKE', '%' . $request->email . '%' );
            $filter = true;
        }

        if ( !empty( $request->phone_number ) ) {
            $model->where( 'phone_number', 'LIKE', '%' . $request->phone_number . '%' );
            $filter = true;
        }

        if ( !empty( $request->title ) ) {
            $model->where( 'phone_number', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'email', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneGuest( $request ) {

        $guest = Guest::find( Helper::decode( $request->id ) );

        return response()->json( $guest );
    }

    public static function createGuest( $request ) {

        $validator = Validator::make( $request->all(), [
            'guestname' => [ 'nullable', 'alpha_dash', 'unique:guests,guestname', new CheckASCIICharacter ],
            'email' => [ 'nullable', 'bail', 'unique:guests,email', 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'fullname' => [ 'nullable' ],
            'phone_number' => [ 'required', 'digits_between:8,15', function( $attribute, $value, $fail ) use ( $request ) {

                $exist = Guest::where( 'phone_number', $value )
                    ->first();

                if ( $exist ) {
                    $fail( __( 'validation.exists' ) );
                    return false;
                }
            } ],
            'password' => [ 'required', Password::min( 8 ) ],
        ] );

        $attributeName = [
            'guestname' => __( 'guest.guestname' ),
            'email' => __( 'guest.email' ),
            'fullname' => __( 'guest.fullname' ),
            'password' => __( 'guest.password' ),
            'phone_number' => __( 'guest.phone_number' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createGuestObject = [
                'fullname' => $request->fullname ?? null,
                'guestname' => $request->guestname ?? null,
                'email' => $request->email ? strtolower( $request->email ) : null,
                'phone_number' => $request->phone_number,
                'calling_code' => '+60',
                'password' => Hash::make( $request->password ),
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'state' => $request->state,
                'city' => $request->city,
                'postcode' => $request->postcode,
                'status' => 10,
                'invitation_code' => strtoupper( \Str::random( 6 ) ),
            ];

            $createGuest = Guest::create( $createGuestObject );

            for ( $i = 1; $i <= 2; $i++ ) {
                $guestWallet = Wallet::create( [
                    'guest_id' => $createGuest->id,
                    'type' => $i,
                    'balance' => 0,
                ] );
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.guests' ) ) ] ),
        ] );
    }

    public static function updateGuest( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'guestname' => [ 'nullable', 'alpha_dash', 'unique:guests,guestname,' . $request->id, new CheckASCIICharacter ],
            'email' => [ 'nullable', 'bail', 'unique:guests,email,' . $request->id, 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'fullname' => [ 'nullable' ],
            'phone_number' => [ 'required', 'digits_between:8,15', function( $attribute, $value, $fail ) use ( $request ) {
                
                $exist = Guest::where( 'phone_number', $value )
                    ->where( 'id', '!=', $request->id )
                    ->first();

                if ( $exist ) {
                    $fail( __( 'validation.exists' ) );
                    return false;
                }
            } ],
            'password' => [ 'nullable', Password::min( 8 ) ],
        ] );

        $attributeName = [
            'guestname' => __( 'guest.guestname' ),
            'email' => __( 'guest.email' ),
            'fullname' => __( 'guest.fullname' ),
            'password' => __( 'guest.password' ),
            'phone_number' => __( 'guest.phone_number' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateGuest = Guest::find( $request->id );
            $updateGuest->guestname = strtolower( $request->guestname );
            $updateGuest->email = strtolower( $request->email );
            $updateGuest->phone_number = $request->phone_number;
            $updateGuest->address_1 = $request->address_1 ?? $updateGuest->address_1;
            $updateGuest->address_2 = $request->address_2 ?? $updateGuest->address_2;
            $updateGuest->state = $request->state ?? $updateGuest->state;
            $updateGuest->city = $request->city ?? $updateGuest->city;
            $updateGuest->postcode = $request->postcode ?? $updateGuest->postcode;
            $updateGuest->calling_code = '+60';
            $updateGuest->fullname = $request->fullname;

            if ( !empty( $request->password ) ) {
                $updateGuest->password = Hash::make( $request->password );
            }

            $updateGuest->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.guests' ) ) ] ),
        ] );
    }

    public static function updateGuestStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $updateGuest = Guest::find( $request->id );
        $updateGuest->status = $request->status;
        $updateGuest->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.guests' ) ) ] ),
        ] );
    }

    public static function createGuestClient( $request ) {

        $validator = Validator::make( $request->all(), [
            'email' => [ 'required', 'bail', 'unique:guests,email', 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'fullname' => [ 'required' ],
            'phone_number' => [ 'required', 'digits_between:8,15', function( $attribute, $value, $fail ) use ( $request ) {

                $exist = Guest::where( 'phone_number', $value )
                    ->first();

                if ( $exist ) {
                    $fail( __( 'validation.exists' ) );
                    return false;
                }
            } ],
            'password' => [ 'required', 'confirmed', Password::min( 8 ) ],
        ] );

        $attributeName = [
            'email' => __( 'guest.email' ),
            'fullname' => __( 'guest.fullname' ),
            'password' => __( 'guest.password' ),
            'phone_number' => __( 'guest.phone_number' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createGuestObject = [
                'name' => strtolower( $request->fullname ),
                'fullname' => $request->fullname,
                'email' => strtolower( $request->email ),
                'phone_number' => $request->phone_number,
                'password' => Hash::make( $request->password ),
                'status' => 10,
            ];

            $createGuest = Guest::create( $createGuestObject );
            
            $createGuest->save();
            
            $createGuest = Guest::create( [
                'guest_id' => $createGuest->id,
                'fullname' => $request->fullname,
                'guest_name' => $request->fullname,
                'feedback_email' => $createGuest->email,
                'calling_code' => '+60',
                'phone_number' => $createGuest->phone_number,
            ] );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.guests' ) ) ] ),
        ] );
    }

    public static function updateProfile( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            // 'name' => [ 'required', 'alpha_dash', 'unique:guests,name,' . $request->id, new CheckASCIICharacter ],
            'email' => [ 'required', 'bail', 'unique:guests,email,' . $request->id, 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'fullname' => [ 'required' ],
            'phone_number' => [ 'required', 'digits_between:8,15', function( $attribute, $value, $fail ) use ( $request ) {
                
                $exist = Guest::where( 'phone_number', $value )
                    ->where( 'id', '!=', $request->id )
                    ->first();

                if ( $exist ) {
                    $fail( __( 'validation.exists' ) );
                    return false;
                }
            } ],
            'password' => [ 'nullable', Password::min( 8 ) ],
            'address_1' => [ 'nullable' ],
            'address_2' => [ 'nullable' ],
            'city' => [ 'nullable' ],
            'state' => [ 'nullable' ],
            'postcode' => [ 'nullable' ],
        ] );

        $attributeName = [
            'guestname' => __( 'guest.guestname' ),
            'email' => __( 'guest.email' ),
            'fullname' => __( 'guest.fullname' ),
            'password' => __( 'guest.password' ),
            'phone_number' => __( 'guest.phone_number' ),
            'address_1' => __( 'guest.address_1' ),
            'address_2' => __( 'guest.address_2' ),
            'city' => __( 'guest.city' ),
            'state' => __( 'guest.state' ),
            'postcode' => __( 'guest.postcode' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateGuest = Guest::find( $request->id );
            // $updateGuest->name = strtolower( $request->name );
            $updateGuest->email = strtolower( $request->email );
            $updateGuest->phone_number = $request->phone_number;
            $updateGuest->fullname = $request->fullname;

            $updateGuest = Guest::find( $request->id );
            $updateGuest->address_1 = $request->address_1;
            $updateGuest->address_2 = $request->address_2;
            $updateGuest->city = $request->city;
            $updateGuest->state = $request->state;
            $updateGuest->postcode = $request->postcode;

            if ( !empty( $request->password ) ) {
                $updateGuest->password = Hash::make( $request->password );
            }

            $updateGuest->save();
            $updateGuest->save();

            DB::commit();

            return redirect()->route('web.profile')->with('success', __('template.x_updated', ['title' => Str::singular(__('template.guests'))]));

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.guests' ) ) ] ),
        ] );
    }
    
    public static function updateGuestProfile( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'guest_name' => [ 'nullable' ],
            'guest_fullname' => [ 'nullable' ],
            'feedback_email' => [ 'nullable' ],
            'guest_phone_number' => [ 'nullable' ],
            'address_1' => [ 'nullable' ],
            'address_2' => [ 'nullable' ],
            'city' => [ 'nullable' ],
            'state' => [ 'nullable' ],
            'postcode' => [ 'nullable' ],
        ] );

        $attributeName = [
            'guest_name' => __( 'guest.guest_name' ),
            'guest_fullname' => __( 'guest.fullname' ),
            'feedback_email' => __( 'guest.feedback_email' ),
            'guest_phone_number' => __( 'guest.phone_number' ),
            'address_1' => __( 'guest.address_1' ),
            'address_2' => __( 'guest.address_2' ),
            'city' => __( 'guest.city' ),
            'state' => __( 'guest.state' ),
            'postcode' => __( 'guest.postcode' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateGuest = Guest::find( $request->id );
            $updateGuest->guest->guest_name = $request->guest_name;
            $updateGuest->guest->fullname = $request->fullname;
            $updateGuest->guest->feedback_email = $request->feedback_email;
            $updateGuest->guest->phone_number = $request->guest_phone_number;
            $updateGuest->guest->address_1 = $request->address_1;
            $updateGuest->guest->address_2 = $request->address_2;
            $updateGuest->guest->postcode = $request->postcode;
            $updateGuest->guest->state = $request->state;
            $updateGuest->guest->city = $request->city;
            $updateGuest->guest->save();

            DB::commit();

            return redirect()->route('web.profile')->with('success', __('template.x_updated', ['title' => Str::singular(__('template.guests'))]));

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.guests' ) ) ] ),
        ] );
    }

    public static function forgotPasswordOtp( $request ) {

        DB::beginTransaction();

        $validator = Validator::make( $request->all(), [
            'phone_number' => [ 'required' ],
        ] );

        $attributeName = [
            'phone_number' => __( 'guest.phone_number' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        try {

            $data['otp_code'] = '';
            $data['identifier'] = '';

            $existingGuest = Guest::where( 'phone_number', $request->phone_number )->first();
            if ( $existingGuest ) {
                $forgotPassword = Helper::requestOtp( 'forgot_password', [
                    'id' => $existingGuest->id,
                    'email' => $existingGuest->email,
                    'phone_number' => $existingGuest->phone_number,
                    'calling_code' => $existingGuest->calling_code,
                ] );
                
                DB::commit();

                // Mail::to( $existingGuest->email )->send(new OtpMail( $forgotPassword ));
    
                if (Mail::failures() != 0) {
                    
                    $response = [
                        'data' => [
                            'title' => $forgotPassword ? __( 'guest.otp_email_success' ) : '',
                            'note' => $forgotPassword ? __( 'guest.otp_email_success_note', [ 'title' => $existingGuest->email ] ) : '',
                            'identifier' => $forgotPassword['identifier'],
                            'otp_code' => '#DEBUG - ' . $forgotPassword['otp_code'],
                        ]
                    ];
    
                    return $response;
                }

                return "Oops! There was some error sending the email.";
            } else {
                return response()->json([
                    'message' => __('guest.guest_not_found'),
                    'message_key' => 'get_guest_failed',
                    'data' => null,
                ]);
            }

        } catch ( \Throwable $th ) {

            DB::rollBack();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine()
            ], 500 );
        }

        return response()->json( [
            'message_key' => 'request_otp_success',
            'data' => [
                'title' => $data['title'],
                'note' => $data['note'],
                'otp_code' => $data['otp_code'],
                'identifier' => $data['identifier'],
            ],
        ] );
    }

    public static function checkPhoneNumber( $request ) {

        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'phone_number' => ['required', 'exists:guests,phone_number'],
        ], [
            'phone_number.required' => __('The phone number field is required.'),
            'phone_number.exists' => __('The phone number does not exist in our records.'),
        ]);

        $attributeName = [
            'email' => __( 'guest.email' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        try {

            $existingGuest = Guest::where( 'phone_number', $request->phone_number )->first();
            if ( $existingGuest ) {
               
                $response = [
                    'data' => [
                        'message_key' => 'guest_exist',
                        'message' => __('guest.guest_exist'),
                        'errors' => [
                            'guest' => __('guest.guest_exist'),
                        ]
                    ]
                ];

                return $response;

            } else {
                return response()->json([
                    'message' => __('guest.guest_not_found'),
                    'message_key' => __('guest.get_guest_failed'),
                    'errors' => [
                        'guest' => __('guest.guest_not_found'),
                    ]
                ], 422 );
            }

        } catch ( \Throwable $th ) {

            DB::rollBack();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine()
            ], 500 );
        }
    }

    public static function resetPassword( $request ) {

        DB::beginTransaction();

        try {
            $request->merge( [
                'identifier' => Crypt::decryptString( $request->identifier ),
            ] );
        } catch ( \Throwable $th ) {
            return response()->json( [
                'message' =>  __( 'guest.invalid_otp' ),
            ], 500 );
        }

        $validator = Validator::make( $request->all(), [
            'identifier' => [ 'required', function( $attribute, $value, $fail ) use ( $request, &$currentOtpAction ) {

                $currentOtpAction = OtpAction::lockForUpdate()
                    ->find( $value );

                if ( !$currentOtpAction ) {
                    $fail( __( 'guest.invalid_otp' ) );
                    return false;
                }

                if ( $currentOtpAction->status != 1 ) {
                    $fail( __( 'guest.invalid_otp' ) );
                    return false;
                }

                if ( Carbon::parse( $currentOtpAction->expire_on )->isPast() ) {
                    $fail( __( 'guest.invalid_otp' ) );
                    return false;
                }

                if ( $currentOtpAction->otp_code != $request->otp_code ) {
                    $fail( __( 'guest.invalid_otp' ) );
                    return false;
                }

            } ],
            'password' => [ 'required', 'confirmed', Password::min( 8 ) ],
        ] );

        $attributeName = [
            'password' => __( 'guest.password' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        try {

            $updateGuest = Guest::find( $currentOtpAction->guest_id );
            $updateGuest->password = Hash::make( $request->password );
            $updateGuest->save();

            $currentOtpAction->status = 10;
            $currentOtpAction->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollBack();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine()
            ], 500 );
        }

        return response()->json( [
            'message' => 'reset_success',
            'message_key' => 'reset_success',
            'data' => null,
        ] );
    }

    // Api
    public static function registerGuest( $request ) {

        $request->merge( [
            'phone_number' => ltrim($request->phone_number, '0'),
        ] );

        try {
            $request->merge( [
                'identifier' => Crypt::decryptString( $request->identifier ),
            ] );
        } catch ( \Throwable $th ) {
            return response()->json( [
                'message' => __( 'validation.header_message' ),
                'errors' => [
                    'identifier' => [
                        __( 'guest.invalid_otp' ),
                    ],
                ]
            ], 422 );
        }

        $validator = Validator::make( $request->all(), [
            'otp_code' => [ 'required' ],
            'identifier' => [ 'required', function( $attribute, $value, $fail ) use ( $request, &$currentTmpUser ) {

                $currentTmpUser = TmpUser::lockForUpdate()->find( $value );

                if ( !$currentTmpUser ) {
                    $fail( __( 'guest.invalid_otp' ) );
                    return false;
                }

                if ( $currentTmpUser->status != 1 ) {
                    $fail( __( 'guest.invalid_otp' ) );
                    return false;
                }

                if ( $currentTmpUser->otp_code != $request->otp_code ) {
                    $fail( __( 'guest.invalid_otp' ) );
                    return false;
                }

                if ( $currentTmpUser->phone_number != $request->phone_number ) {
                    $fail( __( 'guest.invalid_phone_number' ) );
                    return false;
                }
            } ],
            'email' => [ 'nullable', 'bail', 'unique:guests,email', 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'fullname' => [ 'nullable' ],
            'calling_code' => [ 'nullable', 'exists:countries,calling_code' ],
            'phone_number' => [ 'nullable', 'digits_between:8,15', function( $attribute, $value, $fail ) {
                $exist = Guest::where( 'calling_code', request( 'calling_code' ) )
                ->where( 'phone_number', $value )->where( 'status', 10 )
                ->orWhere('phone_number', ltrim($value, '0'))
                ->first();
                if ( $exist ) {
                    $fail( __( 'validation.exists' ) );
                    return false;
                }
            } ],
            'password' => [ 'required', 'confirmed', Password::min( 8 ) ],
            'invitation_code' => [ 'sometimes', 'exists:guests,invitation_code' ],
        ] );

        $attributeName = [
            'email' => __( 'guest.email' ),
            'fullname' => __( 'guest.fullname' ),
            'password' => __( 'guest.password' ),
            'phone_number' => __( 'guest.phone_number' ),
            'calling_code' => __( 'guest.calling_code' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createGuestObject = [
                'fullname' => $request->fullname ? strtolower( $request->fullname ) : null,
                'guestname' => $request->email ? strtolower( $request->email ) : null,
                'email' => $request->email ? strtolower( $request->email ) : null,
                'phone_number' => $request->phone_number,
                'calling_code' => $request->calling_code,
                'password' => Hash::make( $request->password ),
                'status' => 10,
                'invitation_code' => strtoupper( \Str::random( 6 ) ),
            ];

            $referral = Guest::where( 'invitation_code', $request->invitation_code )->first();

            if ( $referral ) {
                $createGuestObject['referral_id'] = $referral->id;
                $createGuestObject['referral_structure'] = $referral->referral_structure . '|' . $referral->id;
            }

            $createGuest = Guest::create( $createGuestObject );
            // assign register bonus
            $registerBonus = Option::getRegisterBonusSettings();

            for ( $i = 1; $i <= 2; $i++ ) {
                $guestWallet = Wallet::create( [
                    'guest_id' => $createGuest->id,
                    'type' => $i,
                    'balance' => 0,
                ] );

                if ( $registerBonus && $i == 2 ) {
                    WalletService::transact( $guestWallet, [
                        'amount' => $registerBonus->option_value,
                        'remark' => 'Register Bonus',
                        'type' => $guestWallet->type,
                        'transaction_type' => 20,
                    ] );
                }
            }

            // assign referral bonus
            $referralBonus = Option::getReferralBonusSettings();
            if( $referral && $registerBonus){

                $referralWallet = $referral->wallets->where('type',2)->first();

                if( $referralWallet ) {
                    WalletService::transact( $referralWallet, [
                        'amount' => $referralBonus->option_value,
                        'remark' => 'Register Bonus',
                        'type' => $referralWallet->type,
                        'transaction_type' => 22,
                    ] );
                }
            }

            $currentTmpUser = TmpUser::find( $request->identifier );
            $currentTmpUser->status = 10;
            $currentTmpUser->save();

            self::createGuestNotification(
                $createGuest->id,
                'notification.register_success',
                'notification.register_success_content',
                'register',
                'home'
            );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'data' => [ 
                'message' => __( 'guest.register_success' ),
                'message_key' => 'register_success',
                'guest' => $createGuest,
                'token' => $createGuest->createToken( 'guest_token' )->plainTextToken
            ],
        ] );

    }

    public static function loginGuest( $request ) {

        $request->merge( [ 'account' => 'test' ] );

        $request->validate( [
            'phone_number' => 'required',
            'password' => 'required',
            'account' => [ 'sometimes', function( $attributes, $value, $fail ) {

                $guest = Guest::where( 'phone_number', request( 'phone_number' ) )
                ->orWhere('phone_number', ltrim(request('phone_number'), '0'))
                ->first();

                if ( !$guest ) {
                    $fail( __( 'guest.guest_wrong_guest' ) );
                    return 0;
                }

                if ( !Hash::check( request( 'password' ), $guest->password ) ) {
                    $fail( __( 'guest.guest_wrong_guest_password' ) );
                    return 0;
                }

                if( $guest->status == 20 ) {
                    $fail( __( 'guest.account_suspended' ) );
                    return 0;
                }
            } ],
        ] );

        $guest = Guest::where( 'phone_number', $request->phone_number )->first();

        // Register OneSignal
        if ( !empty( $request->register_token ) ) {
            self::registerOneSignal( $guest->id, $request->device_type, $request->register_token );
        }

        return response()->json( [
            'data' => [ 
                'message' => __( 'guest.login_success' ),
                'message_key' => 'login_success',
                'guest' => $guest,
                'token' => $guest->createToken( 'guest_token' )->plainTextToken
            ],
        ] );
    }

    public static function getGuest( $request, $filterClientCode ) {

        $guest = Guest::with( ['wallets'] )->find( auth()->guest()->id );

        if ( $guest ) {
            $guest->makeHidden( [
                'status',
                'updated_at',
            ] );
        }

        if($guest->wallets){ 
            foreach($guest->wallets as $wallet){
                $wallet->append([
                    'listing_balance',
                    'formatted_type'
                ]);
            }
        }
    
        // If guest not found, return early with error response
        if (!$guest) {
            return response()->json([
                'message' => __('guest.guest_not_found'),
                'message_key' => 'get_guest_failed',
                'data' => null,
            ]);
        }
    
        // Success response
        return response()->json([
            'message' => '',
            'message_key' => 'get_guest_success',
            'data' => $guest,
        ]);
    }

    public static function updateGuestApi( $request ) {

        $validator = Validator::make( $request->all(), [
            'guestname' => [ 'nullable', 'unique:guests,guestname,' . auth()->guest()->id, ],
            'email' => [ 'nullable', 'unique:guests,email,' . auth()->guest()->id, ],
            'date_of_birth' => ['nullable', 'date'],
        ] );

        $attributeName = [
            'guestname' => __( 'guest.guestname' ),
            'date_of_birth' => __( 'guest.date_of_birth' ),
            'email' => __( 'guest.email' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        $updateGuest = Guest::find( auth()->guest()->id );
        $updateGuest->guestname = $request->guestname;
        $updateGuest->date_of_birth = $request->date_of_birth;
        $updateGuest->email = $request->email;
        $updateGuest->save();

        return response()->json( [
            'message' => __( 'guest.guest_updated' ),
            'message_key' => 'update_guest_success',
            'data' => $updateGuest
        ] );
    }

    public static function updateGuestPassword( $request ) {

        $validator = Validator::make( $request->all(), [
            'old_password' => [ 'required', Password::min( 8 ), function( $attribute, $value, $fail ) {
                if ( !Hash::check( $value, auth()->guest()->password ) ) {
                    $fail( __( 'guest.old_password_not_match' ) );
                }
            } ],
            'password' => [ 'required', Password::min( 8 ), 'confirmed' ],
        ] );

        $attributeName = [
            'old_password' => __( 'guest.old_password' ),
            'password' => __( 'guest.password' ),
            'password_confirmation' => __( 'guest.password_confirmation' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        $updateGuest = Guest::find( auth()->guest()->id );
        $updateGuest->password = Hash::make( $request->password );
        $updateGuest->save();

        return response()->json( [
            'message' => __( 'guest.guest_password_updated' ),
            'message_key' => 'update_guest_password_success',
        ] );
    }

    public static function requestOtp( $request ) {

        $validator = Validator::make( $request->all(), [
            'request_type' => [ 'required', 'in:1,2' ],
        ] );

        $attributeName = [
            'request_type' => __( 'guest.request_type' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        if ( $request->request_type == 1 ) {
            
            $validator = Validator::make( $request->all(), [
                'phone_number' => [ 'required', 'digits_between:8,15', function( $attribute, $value, $fail ) use ( $request ) {

                    if ( mb_substr( $value, 0, 1 ) == 0 ) {
                        $value = mb_substr( $value, 1 );
                    }

                    $guest = Guest::where( 'phone_number', $value )
                        ->orWhere('phone_number', ltrim($value, '0'))
                        ->first();

                    if ( $guest ) {
                        $fail( __( 'validation.unique' ) );
                    }
                } ],
                'request_type' => [ 'required', 'in:1' ],
            ] );
    
            $attributeName = [
                'phone_number' => __( 'guest.phone_number' ),
                'request_type' => __( 'guest.request_type' ),
            ];
    
            foreach ( $attributeName as $key => $aName ) {
                $attributeName[$key] = strtolower( $aName );
            }
    
            $validator->setAttributeNames( $attributeName )->validate();
    
            $expireOn = Carbon::now()->addMinutes( '10' );

            try {

                $createTmpUser = Helper::requestOtp( 'register', [
                    'calling_code' => $request->calling_code,
                    'phone_number' => $request->phone_number,
                    'email' => $request->email,
                ] );
    
                DB::commit();
                $phoneNumber  = $request->calling_code . $request->phone_number;

                // Mail::to( $request->email )->send(new OtpMail( $createTmpUser ));
                
                return response()->json( [
                    'message' => $request->calling_code . $request->phone_number,
                    'message_key' => 'request_otp_success',
                    'data' => [
                        'otp_code' => '#DEBUG - ' . $createTmpUser['otp_code'],
                        'identifier' => $createTmpUser['identifier'],
                        'title' => $createTmpUser ? __( 'guest.otp_email_success' ) : '',
                        'note' => $createTmpUser ? __( 'guest.otp_email_success_note', [ 'title' => $phoneNumber ] ) : ''
                    ]
                ] );
    
            } catch ( \Throwable $th ) {
    
                \DB::rollBack();
                abort( 500, $th->getMessage() . ' in line: ' . $th->getLine() );
            }
        } else { // Resend

            try {
                $request->merge( [
                    'identifier' => Crypt::decryptString( $request->identifier ),
                ] );

            } catch ( \Throwable $th ) {
                return response()->json( [
                    'message' => __( 'validation.header_message' ),
                    'errors' => [
                        'identifier' => [
                            __( 'guest.invalid_otp' ),
                        ],
                    ]
                ], 422 );
            }

            $validator = Validator::make( $request->all(), [
                'identifier' => [ 'required', function( $attribute, $value, $fail ) {
    
                    $current = TmpUser::find( $value );

                    if ( !$current ) {
                        $fail( __( 'guest.invalid_request' ) );
                        return false;
                    }
                    
                    $exist = TmpUser::where( 'phone_number', $current->phone_number )->where( 'status', 1 )->count();
                    if ( $exist == 0 ) {
                        $fail( __( 'guest.invalid_request' ) );
                        return false;
                    }
                } ],

            ] );

            $attributeName = [
                'identifier' => __( 'guest.phone_number' ),
            ];
    
            foreach ( $attributeName as $key => $aName ) {
                $attributeName[$key] = strtolower( $aName );
            }
            $currentTmp = TmpUser::find( $request->identifier );
    
            $validator->setAttributeNames( $attributeName )->validate();
            $phoneNumber  = $request->calling_code . $request->phone_number;
            $updateTmpUser = Helper::requestOtp( 'resend', [
                'calling_code' => $request->calling_code,
                'identifier' => $request->identifier,
                'title' => __( 'guest.otp_email_success' ),
                'note' =>  __( 'guest.otp_email_success_note', [ 'title' => $phoneNumber ] )
            ] );

            DB::commit();

            // Mail::to( $currentTmp->email )->send(new OtpMail( $updateTmpUser ));

            return response()->json( [
                'message' => 'resend_otp_success',
                'message_key' => 'resend_otp_success',
                'data' => [
                    'otp_code' => '#DEBUG - ' . $updateTmpUser['otp_code'],
                    'identifier' => $updateTmpUser['identifier'],
                ]
            ] );
        }
    }

    public static function createEnquiryMail( $request ) {

        $validator = Validator::make( $request->all(), [
            'fullname' => [ 'nullable' ],
            'email' => [ 'required' ],
            'phone_number' => [ 'required' ],
            'message' => [ 'nullable' ],
        ] );

        $attributeName = [
            'fullname' => __( 'guest.fullname' ),
            'email' => __( 'guest.email' ),
            'phone_number' => __( 'guest.phone_number' ),
            'message' => __( 'guest.message' ),
        ];
        
        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $mailContent = MailContent::create( [
                'fullname' => $request->fullname,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'remarks' =>$request->message,
            ] );
            
            DB::commit();

            // Mail::to( config( 'services.mail.receiver' ) )->send(new EnquiryEmail( $mailContent ));
            
            return response()->json( [
                'data' => [
                    'message_key' => 'Enquiry Received!',
                    'message_key' => 'enquiry_received',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_enquiry_failed',
            ], 500 );
        }
    }

    public static function deleteVerification($request)
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required'],
        ], [
            'password.required' => __('The password field is required.'),
        ]);
    
        $attributeName = [
            'password' => __('guest.password'),
        ];
    
        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }
    
        $validator->setAttributeNames($attributeName)->validate();
    
        try {
            // Assume the authenticated guest is making this request
            $currentGuest = auth()->guest();
    
            if (!$currentGuest) {
                return response()->json([
                    'message' => __('guest.not_authenticated'),
                    'message_key' => 'guest_not_authenticated',
                    'data' => null,
                ], 401);
            }
    
            // Verify password
            if (!Hash::check($request->password, $currentGuest->password)) {
                return response()->json([
                    'message' => __('guest.invalid_password'),
                    'message_key' => 'invalid_password',
                    'errors' => [
                        'guest' => __('guest.invalid_password'),
                    ]
                ], 422);
            }
    
            return response()->json([
                'message' => __('guest.password_verified'),
                'message_key' => 'account_deleted',
                'data' => null,
            ]);
    
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }
    }

    public static function deleteConfirm( $request ) {

        $validator = Validator::make($request->all(), [
            'password' => ['required'],
        ], [
            'password.required' => __('The password field is required.'),
        ]);
    
        $attributeName = [
            'password' => __('guest.password'),
        ];
    
        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }
    
        $validator->setAttributeNames($attributeName)->validate();
    
        try {
            // Assume the authenticated guest is making this request
            $currentGuest = auth()->guest();
    
            if (!$currentGuest) {
                return response()->json([
                    'message' => __('guest.not_authenticated'),
                    'message_key' => 'guest_not_authenticated',
                    'data' => null,
                ], 401);
            }
    
            // Verify password
            if (!Hash::check($request->password, $currentGuest->password)) {
                return response()->json([
                    'message' => __('guest.invalid_password'),
                    'message_key' => 'invalid_password',
                    'errors' => [
                        'guest' => __('guest.invalid_password'),
                    ]
                ], 422);
            }
    
            DB::beginTransaction();
    
            $currentGuest->status = 20;
            $currentGuest->save();
            DB::commit();
    
            return response()->json([
                'message' => __('guest.account_deleted'),
                'message_key' => 'account_deleted',
                'data' => null,
            ]);
    
        } catch (\Throwable $th) {
            DB::rollBack();
    
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }
    }
    

    public static function getNotifications( $request ) {

        $notifications = GuestNotification::select(
            'guest_notifications.*',
            // DB::raw( '( SELECT COUNT(*) FROM guest_notification_seens AS a WHERE a.guest_notification_id = guest_notifications.id AND a.guest_id = ' .request()->guest()->id. ' ) as is_read' )
            DB::raw( 'CASE WHEN guest_notification_seens.id > 0 THEN 1 ELSE 0 END as is_read' )
        )->where( function( $query ) {
            $query->where( 'guest_notifications.status', 10 );
            $query->where( 'guest_notifications.is_broadcast', 10 );
            $query->orWhere( 'guest_notification_guests.guest_id', auth()->guest()->id );
        } );

        $notifications->leftJoin( 'guest_notification_guests', function( $query ) {
            $query->on( 'guest_notification_guests.guest_notification_id', '=', 'guest_notifications.id' );
            // $query->on( 'guest_notification_guests.guest_id', '=', DB::raw( auth()->guest()->id ) );
        } );

        // $notifications->leftJoin( 'guest_notification_seens', 'guest_notification_seens.guest_notification_id', '=', 'guest_notifications.id' );
        $notifications->leftJoin( 'guest_notification_seens', function( $query ) {
            $query->on( 'guest_notification_seens.guest_notification_id', '=', 'guest_notifications.id' );
            $query->on( 'guest_notification_seens.guest_id', '=', DB::raw( auth()->guest()->id ) );
        } );

        $notifications->when( !empty( $request->type ), function( $query ) {
            return $query->where( 'guest_notifications.type', request( 'type' ) );
        } );

        $notifications->when( $request->is_read != '' , function( $query ) {
            if ( request( 'is_read' ) == 0 ) {
                return $query->whereNull( 'guest_notification_seens.id' );
            } else {
                return $query->where( 'guest_notification_seens.id', '>', 0 );
            }
        } );

        $notifications->when( $request->notification != '' , function( $query ) use( $request ) {
            return $query->where( 'guest_notifications.id', $request->notification );
        } );

        $notifications->orderBy( 'guest_notifications.created_at', 'DESC' );

        $notifications = $notifications->simplePaginate( empty( $request->per_page ) ? 100 : $request->per_page );

        return response()->json( $notifications );
    }

    public static function getNotification( $request ) {

        $notification = GuestNotification::find( $request->notification );

        return response()->json( [
            'data' => $notification,
        ] );
    }

    public static function updateNotificationSeen( $request ) {

        $notification = GuestNotification::find( $request->notification );
        if ( !$notification ) {
            return response()->json( [
                'message' => '',
            ] );
        }

        GuestNotificationSeen::firstOrCreate( [
            'guest_notification_id' => $request->notification,
            'guest_id' => auth()->guest()->id,
        ], [
            'guest_notification_id' => $request->notification,
            'guest_id' => auth()->guest()->id,
        ] );

        return response()->json( [
            'message' => __( 'notification.notification_seen' ),
        ] );
    }

    public static function createGuestNotification( $guest, $title = null, $content = null, $slug = null, $key = null ){

        $createNotification = GuestNotification::create( [
            'type' => 2,
            'title' => $title,
            'content' => $content,
            'url_slug' => $slug ? \Str::slug( $slug ) : null,
            'system_title' => NULL,
            'system_content' => NULL,
            'system_data' => NULL,
            'meta_data' => NULL,
            'key' => $key,
        ] );

        $createGuestNotificationGuest = GuestNotificationGuest::create( [
            'guest_notification_id' => $createNotification->id,
            'guest_id' => $guest,
        ] );

    }
}