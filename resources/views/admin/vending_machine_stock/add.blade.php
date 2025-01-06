<?php
$vending_machine_stock_add = 'vending_machine_stock_add';
?>

<style>
    .card {
        transition: opacity 0.3s ease, transform 0.3s ease;
    }

    .card.removing {
        opacity: 0;
        transform: translateY(-20px);
    }
</style>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.vending_machine_stocks' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="col-sm-12 mb-3 row">
                    <label for="{{ $vending_machine_stock_add }}_froyos" class="col-sm-5 col-form-label">{{ __( 'template.froyos' ) }}</label>
                    <select class="form-select" id="{{ $vending_machine_stock_add }}_froyos" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'template.froyos' ) ] ) }}" multiple="multiple">
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-sm-12 mb-3 row">
                    <label for="{{ $vending_machine_stock_add }}_syrups" class="col-sm-5 col-form-label">{{ __( 'template.syrups' ) }}</label>
                    <select class="form-select" id="{{ $vending_machine_stock_add }}_syrups" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'template.syrups' ) ] ) }}" multiple="multiple">
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-sm-12 mb-5 row">
                    <label for="{{ $vending_machine_stock_add }}_toppings" class="col-sm-5 col-form-label">{{ __( 'template.toppings' ) }}</label>
                    <select class="form-select" id="{{ $vending_machine_stock_add }}_toppings" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'template.toppings' ) ] ) }}" multiple="multiple">
                    </select>
                    <div class="invalid-feedback"></div>
                </div>

                <h5 class="card-title mb-4">{{ __( 'template.stock_management' ) }}</h5>
                <h5 class="card-title mb-4">{{ __( 'template.froyos' ) }}</h5>
                <div class="col-sm-12 mb-3 row" id="vending-machine-stock-froyo-container"></div>
                <h5 class="card-title mb-4">{{ __( 'template.syrups' ) }}</h5>
                <div class="col-sm-12 mb-3 row" id="vending-machine-stock-syrup-container"></div>
                <h5 class="card-title mb-4">{{ __( 'template.toppings' ) }}</h5>
                <div class="col-sm-12 mb-3 row" id="vending-machine-stock-topping-container"></div>

                <div class="text-end">
                    <button id="{{ $vending_machine_stock_add }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $vending_machine_stock_add }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fe = '#{{ $vending_machine_stock_add }}',
                fileID = '';

        $( fe + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.vending_machine_stock.index' ) }}';
        } );

        $( fe + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            // Gather data for each type
            const froyoData = gatherData('froyo');
            const syrupData = gatherData('syrup');
            const toppingData = gatherData('topping');

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append('froyos', JSON.stringify(froyoData));
            formData.append('syrups', JSON.stringify(syrupData));
            formData.append('toppings', JSON.stringify(toppingData));
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.vending_machine_stock.createVendingMachineStock' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.vending_machine_stock.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( fe + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        function gatherData(type) {
            let selectedData = [];
            const container = $(`#vending-machine-stock-${type}-container`);

            container.find('.card').each(function () {
                const cardId = $(this).attr('id');

                console.log("Processing card ID:", cardId); // Log cardId

                if (cardId) {
                    const itemId = cardId.split('-').pop(); // Get the last part after 'type-card-'
                    const quantity = $(this).find(`#${type}-quantity-${itemId}`).val();
                    console.log(`Item ID: ${itemId}, Quantity: ${quantity}`); // Log itemId and quantity

                    // Validate the quantity and itemId
                    if (quantity && !isNaN(quantity) && quantity >= 1) {
                        selectedData.push({ [`${type}_id`]: itemId, quantity: quantity });
                    } else {
                        console.warn(`Invalid quantity for ${type} with ID: ${itemId}. Must be a number and at least 1.`);
                    }
                } else {
                    console.warn('Card ID is missing or invalid');
                }
            });

            return selectedData;
        }


        getVendingMachineStock();

        function getVendingMachineStock() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.vending_machine_stock.oneVendingMachineStock' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    const stockData = response.stocks;

                    stockData.forEach(item => {

                        if (item.froyo_id !== null && $(`#froyo-card-${item.froyo_id}`).length === 0) {

                            let option = new Option(item.froyo.title, item.froyo_id, true, true); 
                            froyoSelect2.append(option);

                            const froyoCard = `
                                <div class="col-md-4 mb-3 card" id="froyo-card-${item.froyo_id}">
                                    <div class="card-inner">
                                        <h5 class="card-title mb-4">${item.froyo.title}</h5>
                                        <div class="mb-3">
                                            <label for="froyo-quantity-${item.froyo_id}" class="form-label">Quantity</label>
                                            <input type="number" class="form-control" id="froyo-quantity-${item.froyo_id}" value="${item.quantity}" placeholder="Enter quantity">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <button type="button" class="btn btn-danger btn-sm remove-froyo-card" data-id="${item.froyo_id}">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            `;
                            $('#vending-machine-stock-froyo-container').append(froyoCard);
                        }

                        if (item.syrup_id !== null && $(`#syrup-card-${item.syrup_id}`).length === 0) {

                            let option = new Option(item.syrup.title, item.syrup_id, true, true); 
                            syrupSelect2.append(option);

                            const syrupCard = `
                                <div class="col-md-4 mb-3 card" id="syrup-card-${item.syrup_id}">
                                    <div class="card-inner">
                                        <h5 class="card-title mb-4">${item.syrup.title}</h5>
                                        <div class="mb-3">
                                            <label for="syrup-quantity-${item.syrup_id}" class="form-label">Quantity</label>
                                            <input type="number" class="form-control" id="syrup-quantity-${item.syrup_id}" value="${item.quantity}" placeholder="Enter quantity">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <button type="button" class="btn btn-danger btn-sm remove-syrup-card" data-id="${item.syrup_id}">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            `;
                            $('#vending-machine-stock-syrup-container').append(syrupCard);
                        }

                        if (item.topping_id !== null && $(`#topping-card-${item.topping_id}`).length === 0) {

                            let option = new Option(item.topping.title, item.topping_id, true, true); 
                            toppingSelect2.append(option);

                            const toppingCard = `
                                <div class="col-md-4 mb-3 card" id="topping-card-${item.topping_id}">
                                    <div class="card-inner">
                                        <h5 class="card-title mb-4">${item.topping.title}</h5>
                                        <div class="mb-3">
                                            <label for="topping-quantity-${item.topping_id}" class="form-label">Quantity</label>
                                            <input type="number" class="form-control" id="topping-quantity-${item.topping_id}" value="${item.quantity}" placeholder="Enter quantity">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <button type="button" class="btn btn-danger btn-sm remove-topping-card" data-id="${item.topping_id}">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            `;
                            $('#vending-machine-stock-topping-container').append(toppingCard);
                        }

                    });

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }

        // Select 2 
        let froyoSelect2 = $( fe + '_froyos' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.froyo.allFroyos' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        custom_search: params.term, // search term
                        status: 10,
                        start: ( ( params.page ? params.page : 1 ) - 1 ) * 10,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.froyos.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.title,
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
        
        let syrupSelect2 = $( fe + '_syrups' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.syrup.allSyrups' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        custom_search: params.term, // search term
                        status: 10,
                        start: ( ( params.page ? params.page : 1 ) - 1 ) * 10,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.syrups.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.title,
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
        
        let toppingSelect2 = $( fe + '_toppings' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.topping.allToppings' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        custom_search: params.term, // search term
                        status: 10,
                        start: ( ( params.page ? params.page : 1 ) - 1 ) * 10,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.toppings.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.title,
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

        function setupSelect2Handlers(type, containerId) {
            const select2Id = `${fe}_${type}s`; // Generate the Select2 ID dynamically based on type
            const container = $(containerId); // Target container for cards

            // Handle selection
            $(select2Id).on('select2:select', function (e) {
                const selectedItem = e.params.data;
                console.log("Selected Item:", selectedItem); // Log the selectedItem

                if ($(`#${type}-card-${selectedItem.id}`).length === 0) {
                    const card = `
                    <div class="col-md-4 mb-3 card" id="${type}-card-${selectedItem.id}">
                        <div class="card-inner">
                            <h5 class="card-title mb-4">${selectedItem.text}</h5>
                            <div class="mb-3">
                                <label for="${type}-quantity-${selectedItem.id}" class="form-label">Serving</label>
                                <input type="number" class="form-control" id="${type}-quantity-${selectedItem.id}" placeholder="Enter quantity">
                                <div class="invalid-feedback"></div>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-${type}-card" data-id="${selectedItem.id}">
                                Remove
                            </button>
                        </div>
                    </div>
                    `;
                    container.append(card);
                }
            });


            // Handle unselection
            $(select2Id).on('select2:unselect', function (event) {
                const itemId = event.params.data.id;
                removeCard(type, itemId, false);
            });

            // Handle card removal
            $(document).on('click', `.remove-${type}-card`, function () {
                const itemId = $(this).data('id');
                removeCard(type, itemId, true);
            });
        }

        // Function to remove a card
        function removeCard(type, itemId, unselectFromSelect2) {
            console.log(type, itemId, unselectFromSelect2)
            const cardId = `#${type}-card-${itemId}`;

            // Remove card
            $(cardId).addClass('removing');
            setTimeout(() => {
                $(cardId).remove();

                // Unselect the item from select2 if specified
                if (unselectFromSelect2) {
                    const select2Id = `${fe}_${type}s`;
                    const select2Element = $(select2Id);
                    const option = select2Element.find(`option[value="${itemId}"]`);
                    if (option.length) {
                        select2Element.val(select2Element.val().filter(id => id !== String(itemId))); // Remove the ID from the selected values
                        select2Element.trigger('change'); // Trigger change to update select2
                    }
                }
            }, 300);
        }

        // Initialize handlers for all types
        setupSelect2Handlers('froyo', '#vending-machine-stock-froyo-container');
        setupSelect2Handlers('syrup', '#vending-machine-stock-syrup-container');
        setupSelect2Handlers('topping', '#vending-machine-stock-topping-container');

    } );
</script>