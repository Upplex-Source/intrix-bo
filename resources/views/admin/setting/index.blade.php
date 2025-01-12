<?php
$setting = 'setting';
?>

<div class="card">
    <div class="card-body">
        <div class="row gy-3">
            <div class="col-md-2">                
                <div class="list-group" role="tablist">
                    <a class="list-group-item list-group-item-action active" data-bs-toggle="list" href="#ms" role="tab">{{ __( 'setting.bonus_settings' ) }}</a>
                </div>
            </div>
            <div class="col-md-10">
                <div class="tab-content p-2">
                    <div class="tab-pane fade show active" id="ms" role="tabpanel">
                        <h5 class="card-title mb-0">{{ __( 'setting.bonus_settings' ) }}</h5>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3 row">
                                    <label for="{{ $setting }}_convertion_rate" class="col-sm-5 col-form-label">{{ __( 'setting.points_convertion' ) }} (RM 1 SPEND = <span id="convertion_rate_preview"></span> Points)</label>
                                    <div class="col-sm-7">
                                        <input type="number" class="form-control form-control-sm" id="{{ $setting }}_convertion_rate">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="{{ $setting }}_register_bonus" class="col-sm-5 col-form-label">{{ __( 'setting.register_bonus' ) }}</label>
                                    <div class="col-sm-7">
                                        <input type="number" class="form-control form-control-sm" id="{{ $setting }}_register_bonus">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="{{ $setting }}_referral_register_bonus_points" class="col-sm-5 col-form-label">{{ __( 'setting.referral_register_bonus_points' ) }}</label>
                                    <div class="col-sm-7">
                                        <input type="number" class="form-control form-control-sm" id="{{ $setting }}_referral_register_bonus_points">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="{{ $setting }}_referral_spending_bonus_points" class="col-sm-5 col-form-label">{{ __( 'setting.referral_spending_bonus_points' ) }} (RM 1 SPEND = <span id="referral_spending_bonus_points_preview"></span> Points)</label>
                                    <div class="col-sm-7">
                                        <input type="number" class="form-control form-control-sm" id="{{ $setting }}_referral_spending_bonus_points">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button class="btn btn-sm btn-primary" id="bs_save">{{ __( 'template.save_changes' ) }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        getSettings();

        let s = '#{{ $setting }}';

        $(s+'_convertion_rate').on('keyup', function () {
            $( '#convertion_rate_preview').text( $(this).val() );
        });

        $(s+'_referral_spending_bonus_points').on('keyup', function () {
            $( '#referral_spending_bonus_points_preview').text( $(this).val() );
        });

        $( '#bs_save' ).on( 'click', function() {

            resetInputValidation();

            $.ajax( {
                url: '{{ route( 'admin.setting.updateBonusSetting' ) }}',
                type: 'POST',
                data: {
                    convertion_rate: $( s + '_convertion_rate' ).val(),
                    referral_register_bonus_points: $( s + '_referral_register_bonus_points' ).val(),
                    register_bonus: $( s + '_register_bonus' ).val(),
                    referral_spending_bonus_points: $( s + '_referral_spending_bonus_points' ).val(),
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.show();
                },
                error: function( error ) {
                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( s + '_' + key ).addClass( 'is-invalid' ).next().text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.show();       
                    }
                }
            } );
        } );

        function getSettings() {

            $.ajax( {
                url: '{{ route( 'admin.setting.bonusSettings' ) }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {
                    if ( response ) {
                        response.forEach(item => {
                            if (item.option_name === "CONVERTION_RATE") {
                                $( s + '_convertion_rate').val( item.option_value );
                                $( '#convertion_rate_preview').text( item.option_value );
                            }

                            if (item.option_name === "REFERRAL_REGISTER") {
                                $( s + '_referral_register_bonus_points').val( item.option_value );
                            }

                            if (item.option_name === "REFERRAL_SPENDING") {
                                $( s + '_referral_spending_bonus_points').val( item.option_value );
                                $( '#referral_spending_bonus_points_preview').text( item.option_value );
                            }

                            if (item.option_name === "REGISTER_BONUS") {
                                $( s + '_register_bonus').val( item.option_value );
                            }
                        });
                    }
                },
            } );
        }
    } );
</script>

