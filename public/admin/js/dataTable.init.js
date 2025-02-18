
document.addEventListener( 'DOMContentLoaded', function() {
    dt_table = $(dt_table_name).DataTable({
        language: dt_table_config.language,
        autoHeight: true,
        layout: {
            topStart: {
                buttons: ['copyHtml5', 'excelHtml5', 'csvHtml5', 'pdfHtml5']
            }
        },
        ajax: {
            type: 'POST',
            url: dt_table_config.ajax.url,
            data: dt_table_config.ajax.data,
            dataSrc: dt_table_config.ajax.dataSrc,
            error: function (xhr, error, code) {
                console.log(xhr);
                console.log(error);
                console.log(code);
            },
        },
        lengthMenu: [5, 10, 25, 50, 100], // Define the options for the dropdown
        pageLength: 10, 
        responsive: true,
        processing: true,
        serverSide: true,
        order: dt_table_config.order,
        ordering: true,
        scrollX: true,
        searchCols: dt_table_config.searchCols ? dt_table_config.searchCols : [],
        columns: dt_table_config.columns,
        columnDefs: dt_table_config.columnDefs,
        searching: false, // Disable the search bar
        dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6 text-end'l>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'mt-2 col-sm-12 col-md-5'i><'mt-2 col-sm-12 col-md-7 text-end'p>>",
        buttons: [
            {
                extend: 'copyHtml5',
                text: '<i class="fa fa-copy"></i>', // Copy icon
                className: 'btn btn-light', // Optional: Bootstrap styling
                titleAttr: 'Copy to clipboard', // Tooltip
                exportOptions: {
                    rows: function (idx, data, node) {
                        let exportOnlySelected = $("#exportSelected").is(":checked");
                        if (exportOnlySelected) {
                            // Check if the checkbox in the row is checked
                            let checkbox = $(node).find('.select-row');
                            return checkbox.is(':checked');
                        } else {
                            // Export all rows
                            return true;
                        }
                    },
                    columns: ':not(:last-child)'
                },
                customize: function (win) {
                    $(win.document).find('.dt-button').removeClass('dt-button');
                }

            },
            {
                extend: 'excelHtml5',
                text: '<i class="fa fa-file-excel"></i>', // Excel icon
                className: 'btn btn-success',
                titleAttr: 'Export to Excel',
                exportOptions: {
                    rows: function (idx, data, node) {
                        let exportOnlySelected = $("#exportSelected").is(":checked");
                        if (exportOnlySelected) {
                            // Check if the checkbox in the row is checked
                            let checkbox = $(node).find('.select-row');
                            return checkbox.is(':checked');
                        } else {
                            // Export all rows
                            return true;
                        }
                    },
                    columns: ':not(:last-child)'
                },
                customize: function (win) {
                    // Remove the dt-button class from the export buttons
                    $(win.document).find('.dt-button').removeClass('dt-button');
                }

            },
            {
                extend: 'csvHtml5',
                text: '<i class="fa fa-file-csv"></i>', // CSV icon
                className: 'btn btn-info',
                titleAttr: 'Export to CSV',
                exportOptions: {
                    rows: function (idx, data, node) {
                        let exportOnlySelected = $("#exportSelected").is(":checked");
                        if (exportOnlySelected) {
                            // Check if the checkbox in the row is checked
                            let checkbox = $(node).find('.select-row');
                            return checkbox.is(':checked');
                        } else {
                            // Export all rows
                            return true;
                        }
                    },
                    columns: ':not(:last-child)'
                },
                customize: function (win) {
                    // Remove the dt-button class from the export buttons
                    $(win.document).find('.dt-button').removeClass('dt-button');
                }

            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fa fa-file-pdf"></i>', // PDF icon
                className: 'btn btn-danger',
                titleAttr: 'Export to PDF',
                exportOptions: {
                    rows: function (idx, data, node) {
                        let exportOnlySelected = $("#exportSelected").is(":checked");
                        if (exportOnlySelected) {
                            // Check if the checkbox in the row is checked
                            let checkbox = $(node).find('.select-row');
                            return checkbox.is(':checked');
                        } else {
                            // Export all rows
                            return true;
                        }
                    },
                    columns: ':not(:last-child)'
                },
                customize: function (win) {
                    // Remove the dt-button class from the export buttons
                    $(win.document).find('.dt-button').removeClass('dt-button');
                }

            },
        ],
        footerCallback: function (row, data, start, end, display) {
            // Example: Calculate total for column index 3
            var api = this.api();
            var total = api
                .column(3, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return parseFloat(a) + parseFloat(b);
                }, 0);

            // Update footer
            $(api.column(3).footer()).html('Total: ' + total.toFixed(2));
        },
        createdRow: function (row) {
            $(row).addClass('nk-tb-item');
        },
        initComplete: function () {
            const exportCheckbox = `
                <div class="my-3">
                    <input type="checkbox" id="exportSelected" name="exportSelected">
                    <label for="exportSelected" class="ms-1">Export ONLY selected rows</label>
                </div>
            `;
            $('.dt-buttons').append(exportCheckbox);
            $(dt_table_name + '_filter').remove();

            let rawName = dt_table_name.replace('#', '');
            let lengthSelect2 = $('.dataTables_length select');
            lengthSelect2.addClass('custom-dropdown');
        },
        drawCallback: function (response) {
            if (response.json.subTotal != undefined) {
                if (Array.isArray(response.json.subTotal)) {
                    $.each(response.json.subTotal, function (i, v) {
                        $('.dataTables_scrollFoot .subtotal').eq(i).html(v);
                        $('.dataTables_scrollFoot .grandtotal').eq(i).html(response.json.grandTotal[i]);
                    });
                }
            }
        },
    });

    function positionDropdown(event) {
        var dropdown = document.querySelector('.dropdown-menu');
        var trigger = event.target; // The element that triggered the dropdown
        var rect = trigger.getBoundingClientRect();
        var dropdownWidth = dropdown.offsetWidth;
        var dropdownHeight = dropdown.offsetHeight;
        console.log(event)
        // Calculate positions
        var top = rect.top + window.pageYOffset + rect.height;
        var left = rect.left + window.pageXOffset;
      
        // Ensure dropdown does not overflow the viewport
        var viewportWidth = window.innerWidth;
        var viewportHeight = window.innerHeight;
      
        if (left + dropdownWidth > viewportWidth) {
          left = viewportWidth - dropdownWidth;
        }
      
        if (top + dropdownHeight > viewportHeight) {
          top = rect.top + window.pageYOffset - dropdownHeight;
        }
      
        dropdown.style.top = top + 'px';
        dropdown.style.left = left + 'px';
        dropdown.style.position = "absolute";
        dropdown.style.zIndex = "9999"; 
    }

    document.querySelector('.dropdown-toggle').addEventListener('click', positionDropdown);
  
    $(dt_table_name).on('shown.bs.dropdown', function (event) {

        let rowCount = document.querySelectorAll(dt_table_name +" tbody tr").length;
        
        setTimeout(() => {
            const scrollBody = $('.dt-scroll-body');
            const dropdown = $(event.target).closest('.dropdown').find('.dropdown-menu');
            const dropdownOffset = dropdown.offset();
            const scrollBodyOffset = scrollBody.offset();
    
            if (rowCount === 1) {
                scrollBody.css("height", "125px");
            }

            if (dropdownOffset && scrollBodyOffset) {
                const dropdownPosition = dropdownOffset.top - scrollBodyOffset.top + scrollBody.scrollTop();
    
                console.log('Dropdown Offset Top:', dropdownOffset.top);
                console.log('ScrollBody Offset Top:', scrollBodyOffset.top);
                console.log('ScrollBody ScrollTop:', scrollBody.scrollTop());
                console.log('Calculated Dropdown Position:', dropdownPosition);
    
                scrollBody.scrollTop(dropdownPosition);
            } else {
                console.error('Offsets not found for Dropdown or ScrollBody');
            }
        }, 10); // 10ms delay
    });
    
   
   $(dt_table_name).on('hide.bs.dropdown', function () {
        $('.dt-scroll-body').css( "overflow-y", "auto" );
   })   

    $( dt_table_name ).on( 'page.dt length.dt order.dt search.dt', function() {
        table_no = dt_table.page.info().page * dt_table.page.info().length;
    } );

    $( dt_table_name ).on( 'preXhr.dt', function( e, settings, data ) {
        
        window['columns'].forEach( function( v, i ) {
            if ( v.type != 'default' ) {
                data[v.id] = window[v.id];
            }
        } );
    } );

    $( '.listing-filter > input' ).on( 'keydown keypress', function(e) {

        let that = $( this );
        clearTimeout( timeout );
        timeout = setTimeout( function(){
            window[that.data( 'id' )] = that.val();
            console.log(window[that.data( 'id' )]);
            dt_table.draw();
        }, 500 );
    } );

    $( '.listing-filter > select' ).on( 'change', function() {

        let that = $( this );
        window[that.data( 'id' )] = that.val();
        dt_table.draw();
    } );

    $( '.dt-export' ).click( function() {
        let sort = dt_table.order(),
            url = 'order[0][column]='+sort[0][0]+'&order[0][dir]='+sort[0][1];

        window['columns'].forEach( function( v, i ) {
            if ( v.type != 'default' ) {
                if ( v.type == 'checkbox' ) {
                    let checkboxValue = [];
                    $.each( $( '*[data-id="trxtype"]' ), function( i, v ) {
                        if ( $( v ).is( ':checked' ) ) {
                            checkboxValue.push( $( v ).val() );
                        }
                    } );
                    url += ( '&' + v.id + '=' + checkboxValue.join( ',' ) );
                } else {
                    url += ( '&' + v.id + '=' + $( '#' + v.id ).val() );
                }
            }
        } );

        const urlParams = new URL( exportPath );
        let newExportPath = urlParams.origin + urlParams.pathname;

        if ( urlParams.search != '' ) {
            url += urlParams.search.replace( '?', '&' );
        }

        window.location.href = newExportPath + '?' + url;
    } );

} );