    <script src="{{ asset( 'admin/js/bundle.js' ) }}"></script>
    <script src="{{ asset( 'admin/js/scripts.js' ) }}"></script>
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