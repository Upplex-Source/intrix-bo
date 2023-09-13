<script src="https://cdn.jsdelivr.net/npm/@event-calendar/build@2.3.3/event-calendar.min.js" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@event-calendar/build@2.3.3/event-calendar.min.css" crossorigin="anonymous">

<div class="card">
    <div class="card-body">
        <div id="ec"></div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let ec = new EventCalendar( document.getElementById( 'ec' ), {
            view: 'dayGridMonth',
            editeable: false,
            eventStartEditable: false,
            // dateClick: function( dateClickInfo ) {
            //     addEvent();
            // },
            eventClick: function( e ) {
                console.log( e );
                window.open( '{{ route( 'admin.booking.edit' ) }}?id=' + e.event.id );
            },
            eventSources: [ {
                events: function( e ) {
                    console.log( 'fetching...' );
                    let start = e.startStr.split( 'T' )[0],
                        end = e.endStr.split( 'T' )[0];

                    $.ajax( {
                        url: '{{ route( 'admin.booking.calendarAllBookings' ) }}',
                        type: 'POST',
                        data: { start, end, _token: '{{ csrf_token() }}' },
                        success: function( response ) {
                            loadEvent( response );
                        }
                    } );

                    return [];
                }
            } ],
            events: [],
        } );

        function loadEvent( response ) {
            response.map( function( v, i ) {
                ec.addEvent( v );
            } );
        }

        function addEvent() {

            ec.addEvent( {
                id: 'aa',
                allDay: true,
                start: '2023-09-13 00:00:00',
                end: '2023-09-13 23:59:59',
                title: {
                    html: '<strong>Hello</strong>',
                }
            } );
        }
    } );
</script>