<?php
$toll_expense_create = 'toll_expense_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.toll_expenses' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $toll_expense_create }}_transaction_number" class="col-sm-5 col-form-label">{{ __( 'expenses.transaction_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $toll_expense_create }}_transaction_number">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $toll_expense_create }}_transaction_time" class="col-sm-5 col-form-label">{{ __( 'datatables.transaction_time' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $toll_expense_create }}_transaction_time">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $toll_expense_create }}_posted_date" class="col-sm-5 col-form-label">{{ __( 'expenses.posted_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $toll_expense_create }}_posted_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $toll_expense_create }}_transaction_type" class="col-sm-5 col-form-label">{{ __( 'expenses.transaction_type' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $toll_expense_create }}_transaction_type" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'expenses.transaction_type' ) ] ) }}</option>
                            @foreach( $data['transaction_type'] as $key => $transaction_type )
                            <option value="{{ $key }}">{{ $transaction_type }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row reload-location hidden">
                    <label for="{{ $toll_expense_create }}_reload_location" class="col-sm-5 col-form-label">{{ __( 'expenses.reload_location' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $toll_expense_create }}_reload_location">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $toll_expense_create }}_entry_location" class="col-sm-5 col-form-label">{{ __( 'expenses.entry_location' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $toll_expense_create }}_entry_location">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $toll_expense_create }}_entry_sp" class="col-sm-5 col-form-label">{{ __( 'expenses.entry_sp' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $toll_expense_create }}_entry_sp">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $toll_expense_create }}_exit_location" class="col-sm-5 col-form-label">{{ __( 'expenses.exit_location' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $toll_expense_create }}_exit_location">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $toll_expense_create }}_exit_sp" class="col-sm-5 col-form-label">{{ __( 'expenses.exit_sp' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $toll_expense_create }}_exit_sp">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $toll_expense_create }}_amount" class="col-sm-5 col-form-label">{{ __( 'expenses.amount' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $toll_expense_create }}_amount">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $toll_expense_create }}_balance" class="col-sm-5 col-form-label">{{ __( 'expenses.balance' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $toll_expense_create }}_balance">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $toll_expense_create }}_class" class="col-sm-5 col-form-label">{{ __( 'expenses.class' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $toll_expense_create }}_class" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'expenses.class' ) ] ) }}</option>
                            @foreach( $data['class'] as $key => $class )
                            <option value="{{ $key }}">{{ $class }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $toll_expense_create }}_tag_number" class="col-sm-5 col-form-label">{{ __( 'expenses.tag_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $toll_expense_create }}_tag_number" value="00000000">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $toll_expense_create }}_vehicle" class="col-sm-5 col-form-label">{{ __( 'expenses.vehicle' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $toll_expense_create }}_vehicle" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'expenses.vehicle' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $toll_expense_create }}_remarks" class="col-sm-5 col-form-label">{{ __( 'expenses.remarks' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $toll_expense_create }}_remarks" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $toll_expense_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $toll_expense_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let tec = '#{{ $toll_expense_create }}';

        $( tec + '_transaction_time' ).flatpickr( {
            enableTime: true,
            enableSeconds:true,
        } );

        $( tec + '_posted_date' ).flatpickr();

        $( tec + '_transaction_type' ).on( 'change', function() {
            if ( $( this ).val() == '2' ) {
                $( '.reload-location' ).removeClass( 'hidden' );
            } else {
                $( '.reload-location' ).addClass( 'hidden' );
            }
        } );

        $( tec + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.toll_expense.index' ) }}';
        } );

        $( tec + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'transaction_number', $( tec + '_transaction_number' ).val() );
            formData.append( 'transaction_time', $( tec + '_transaction_time' ).val() );
            formData.append( 'posted_date', $( tec + '_posted_date' ).val() );
            formData.append( 'transaction_type', $( tec + '_transaction_type' ).val() );
            formData.append( 'reload_location', $( tec + '_reload_location' ).val() );
            formData.append( 'entry_location', $( tec + '_entry_location' ).val() );
            formData.append( 'entry_sp', $( tec + '_entry_sp' ).val() );
            formData.append( 'exit_location', $( tec + '_exit_location' ).val() );
            formData.append( 'exit_sp', $( tec + '_exit_sp' ).val() );
            formData.append( 'amount', $( tec + '_amount' ).val() );
            formData.append( 'balance', $( tec + '_balance' ).val() );
            formData.append( 'class', $( tec + '_class' ).val() );
            formData.append( 'tag_number', $( tec + '_tag_number' ).val() );
            formData.append( 'vehicle', null === $( tec + '_vehicle' ).val() ? '' : $( tec + '_vehicle' ).val() );
            formData.append( 'remarks', $( tec + '_remarks' ).val() );
            
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
            url: '{{ route( 'admin.toll_expense.createTollExpense' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.toll_expense.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( tec + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                        // $( '.form-control.is-invalid:first' ).get( 0 ).scrollIntoView( { block: 'center' } );
                        // $( '.form-select.is-invalid:first' ).get( 0 ).scrollIntoView( { block: 'center' } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        $( tec + '_vehicle' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.vehicle.allVehicles' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        custom_search: params.term, // search term
                        status: 10,
                        start: params.page ? params.page : 0,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.vehicles.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.name + ' (' + v.license_plate + ')',
                        } );
                    } );

                    return {
                        results: processedResult,
                        pagination: {
                            more: ( params.page * 10 ) < data.recordsFiltered
                        }
                    };
                }
            },
        } );
    } );
</script>