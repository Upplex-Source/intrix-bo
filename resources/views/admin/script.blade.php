    <script src="{{ asset( 'admin/js/bundle.js' ) }}"></script>
    <script src="{{ asset( 'admin/js/scripts.js' ) }}"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.datatables.net/v/bs5/dt-1.13.4/datatables.min.js"></script>
    <script>
        document.addEventListener( 'DOMContentLoaded', function() {

            $( '#_logout' ).click( function( e ) {

                e.preventDefault();

                $.ajax( {
                    url: '{{ route( 'admin.signout' ) }}',
                    type: 'POST',
                    data: { '_token': '{{ csrf_token() }}' },
                    success: function() {
                        document.getElementById( 'logoutForm' ).submit();
                    }
                } );
            } );
        } );
    </script>