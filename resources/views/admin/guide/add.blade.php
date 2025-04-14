<?php 
    $guides = $data['guides'];
    $type = $data['type'];
    $fileType = $data['file_type'];
    $country = $data['country'];
?>

<style>
    .sortable-placeholder {
        background: #f8f9fa;
        border: 2px dashed #ccc;
        height: 100px;
    }
    .guide-img {
        width: 80%; /* ✅ Increased size */
        height: 120px;
        object-fit: contain; /* ✅ Ensures it maintains aspect ratio */
    }
    .list-group-item {
        display: flex;
        justify-content: center; /* ✅ Centers content */
        align-items: center;
        text-align: center;
    }

    #guide-list {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        padding: 0;
    }
    #guide-list .list-group-item {
        width: 100%;
        text-align: center;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 10px;
    }
    .guide-img {
        width: 100%;
        max-width: 150px;
        object-fit: cover;
    }

    #guide-list li:hover {
        background: #e9ecef;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    }

    .sortable-placeholder {
        background: #dee2e6;
        border: 2px dashed #6c757d;
        height: 130px;
        border-radius: 8px;
        margin-bottom: 10px;
    }

</style>

<?php $guide_create = 'guide_create'; ?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.guides' ) ) ] ) }} ( {{ $type }} )</h3>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-inner">
        <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>

        <div class="mb-3 row">
            <label for="{{ $guide_create }}_country" class="col-sm-5 col-form-label">{{ __( 'guide.country' ) }}</label>
            <div class="col-sm-7">
                <select class="form-select form-select-sm" id="{{ $guide_create }}_country" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'guide.country' ) ] ) }}">
                </select>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3">
            <label>{{ __( 'guide.file' ) }}</label>
            <div class="dropzone mb-3" id="{{ $guide_create }}_image" style="min-height: 0px;">
                <div class="dz-message needsclick">
                    <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                </div>
            </div>
            <div class="invalid-feedback"></div>
        </div>
        <ul id="guide-list" class="list-group">
            @foreach($guides as $guide)
                <li class="list-group-item d-flex flex-column align-items-center justify-content-center position-relative" data-id="{{ $guide->id }}">
                    <img src="{{ $guide->thumbnail_path }}" class="guide-img rounded">
                    <p class="text-center">{{ $guide->title != "" ? $guide->title : '-' }}</p>
                    <!-- Dropdown -->
                    <div class="dropdown mt-2">
                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <em class="icon ni ni-more-h"></em>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <button class="dropdown-item edit-guide" data-id="{{ $guide->id }}">Edit</button>
                            </li>
                            <li>
                                <button class="dropdown-item text-danger delete-guide" data-id="{{ $guide->id }}">Delete</button>
                            </li>
                        </ul>
                    </div>
                </li>
            @endforeach
        </ul>
        
    </div>
</div>

<!-- jQuery (Make sure jQuery is included before jQuery UI) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>

<!-- jQuery UI CSS (Optional, for better styling) -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css">
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

    let fc = '#{{ $guide_create }}', fileID = '';

    $(fc + '_cancel').click(() => window.location.href = '{{ route('admin.module_parent.guide.index') }}');

    // ✅ Prevent Dropzone from being attached multiple times
    if (Dropzone.instances.length > 0) {
        Dropzone.instances.forEach(dz => dz.destroy()); // Destroy existing Dropzones before initializing
    }

    // ✅ Ensure Dropzone is initialized once
    if (!$(fc + '_image').hasClass("dz-clickable")) {
        Dropzone.autoDiscover = false;
        let myDropzone = new Dropzone(fc + '_image', {
            url: "{{ route('admin.guide.createGuide') }}",
            maxFiles: 10,
            maxFilesize: 720,
            acceptedFiles: "image/jpeg,image/jpg,image/png,application/pdf,video/mp4,video/mpeg,video/quicktime,video/avi,video/webm",
            addRemoveLinks: true,
            params: function() {
                return {
                    file_type: @json( $fileType ),
                    country: $( fc + '_country' ).val(), 
                    _token: "{{ csrf_token() }}"
                };
            },
            success: function ( file, response ) {
                if ( response.status == 200 ) {
                    let newGuide = $( `
                        <li class="list-group-item d-flex flex-column align-items-center justify-content-center position-relative" data-id="${response.data.id}">
                            <img src="${response.data.url}" class="guide-img rounded">
                            <p class="text-center">${response.data.title}</p>
                            <div class="dropdown mt-2">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <em class="icon ni ni-more-h"></em>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><button class="dropdown-item edit-guide" data-id="${response.data.id}">Edit</button></li>
                                    <li><button class="dropdown-item text-danger delete-guide" data-id="${response.data.id}">Delete</button></li>
                                </ul>
                            </div>
                        </li>
                    `);
                    $( "#guide-list" ).append( newGuide );

                    // ✅ Remove preview for that file
                    myDropzone.removeFile( file );
                }
            }
        });
    }

    // ✅ Initialize Sortable.js
    let sortableList = new Sortable(document.getElementById('guide-list'), {
        animation: 200, // Smooth transition effect
        handle: ".guide-img", // Users can drag by clicking on the image
        ghostClass: 'sortable-placeholder', // Placeholder class when dragging
        handle: ".list-group-item", // Only drag using the list items
        ghostClass: "sortable-placeholder", // CSS class for dragged element
        onEnd: function(evt) {
            let sortedIDs = [];
            $("#guide-list li").each(function() {
                if( $(this).data("id") ){
                    sortedIDs.push($(this).data("id"));
                }
            });

            // ✅ Send updated order to backend
            $.ajax({
                url: "{{ route('admin.guide.updateOrder') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    order: sortedIDs
                },
                success: function(response) {
                    console.log("Guide order updated successfully!");
                },
                error: function(error) {
                    console.error("Error updating guide order", error);
                }
            });
        }
    });

    $( document ).on( 'click', '.edit-guide', function() {
        window.location.href = '{{ route( 'admin.guide.edit' ) }}?id=' + $( this ).data( 'id' );
    } );

    // ✅ Delete Guide
    $(document).on("click", ".delete-guide", function() {
        let guideId = $(this).data("id");
        let guideItem = $(this).closest(".list-group-item"); // Ensure correct targeting

        $( 'body' ).loading( {
            message: '{{ __( 'template.loading' ) }}'
        } );

        $.post('{{ route("admin.guide.updateGuideStatus") }}', {
            _token: '{{ csrf_token() }}',
            id: guideId
        }).done(function(response) {
            $( 'body' ).loading( 'stop' );

            guideItem.fadeOut(300, function() {
                $(this).remove();
            });
        }).fail(function() {
            $( 'body' ).loading( 'stop' );

            alert("Error occurred. Please check your connection.");
        });
    });

    let countrySelect2 = $( fc + '_country' ).select2( {
        theme: 'bootstrap-5',
        width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
        placeholder: $( this ).data( 'placeholder' ),
        closeOnSelect: true,
        allowClear: true,
        ajax: {
            method: 'POST',
            url: '{{ route( 'admin.guide_country.allGuideCountries' ) }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    title: params.term, // search term
                    start: params.page ? params.page : 0,
                    length: 10,
                    _token: '{{ csrf_token() }}',
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;

                let processedResult = [];

                data.guide_countries.map( function( v, i ) {
                    processedResult.push( {
                        id: v.id,
                        text: v.name,
                    } );
                } );

                return {
                    results: processedResult,
                    pagination: {
                        more: ( params.page * 10 ) < data.recordsFiltered
                    }
                };
            }
        }
    } );

    let country = @json( $country );

    if( country ) {
        let option = new Option( country.name, country.id, true, true );
        countrySelect2.append( option )
    }

    $( fc + '_country' ).on( 'select2:select', function (e) {

        $( '#guide-list' ).empty();
        
        let countryId = e.params.data.id;

        $.ajax( {
            url: '{{ route('admin.guide.allGuides') }}',
            type: 'POST',
            data: {
                file_type: @json( $fileType ),
                country_id: countryId,
                length: 100,
                start: 0,
                status: 10,
                _token: '{{ csrf_token() }}'
            },
            success: function ( response ) {
                if ( response.guides && response.guides.length ) {
                    $( '#guide-list' ).empty();

                    response.guides.forEach( function ( guide ) {
                        let guideItem = `
                            <li class="list-group-item d-flex flex-column align-items-center justify-content-center position-relative" data-id="${guide.id}">
                                <img src="${guide.thumbnail_path}" class="guide-img rounded">
                                <p class="text-center">${guide.title}</p>

                                <div class="dropdown mt-2">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <em class="icon ni ni-more-h"></em>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <button class="dropdown-item edit-guide" data-id="${guide.id}">Edit</button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item text-danger delete-guide" data-id="${guide.id}">Delete</button>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        `;
                        $( '#guide-list' ).append( guideItem );
                    } );
                }
            }
        } );
    } );

});

</script>
