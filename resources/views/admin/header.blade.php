<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="shortcut icon" href="{{ asset( 'favicon.ico' ) . Helper::assetVersion() }}">

    @if ( @$header )
        <title>{{ @$header['title'] }} | {{ Helper::websiteName() }}</title>
    @else
        <title>{{ Helper::websiteName() }} Admin Panel</title>
    @endif

    <!-- StyleSheets  -->
    <link rel="stylesheet" href="{{ asset( 'admin/css/dashlite.min.css' . Helper::assetVersion() ) }}">
    <link rel="stylesheet" href="{{ asset( 'admin/css/extended.css' . Helper::assetVersion() ) }}">
    <link href="{{ asset( 'admin/css/flatpickr.min.css' ) . Helper::assetVersion() }}" rel="stylesheet">
    <link href="{{ asset( 'admin/css/select2.min.css' ) . Helper::assetVersion() }}" rel="stylesheet">
    <link href="{{ asset( 'admin/css/select2-bootstrap-5-theme.min.css' ) . Helper::assetVersion() }}" rel="stylesheet">
    <link href="{{ asset( 'admin/css/flatpickr-monthSelect.css' ) . Helper::assetVersion() }}" rel="stylesheet">

</head>