<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<div style="max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; text-align: center;">
    <img src="{{ asset( 'admin/images/logo.png' ) }}" alt="Logo" style="max-width: 150px; margin-bottom: 20px;" />

    <h2 style="color: #333; margin-bottom: 15px;">User Details</h2>

    <table style="width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">
        <tr>
            <td style="padding: 10px; font-weight: bold; text-align: left;">Full Name:</td>
            <td style="padding: 10px; text-align: left;">{{ $data['full_name'] }}</td>
        </tr>
        <tr>
            <td style="padding: 10px; font-weight: bold; text-align: left;">Email:</td>
            <td style="padding: 10px; text-align: left;">{{ $data['email'] }}</td>
        </tr>
        <tr>
            <td style="padding: 10px; font-weight: bold; text-align: left;">Phone Number:</td>
            <td style="padding: 10px; text-align: left;">{{ $data['phone_number'] ?? '-' }}</td>
        </tr>
        <tr>
            <td style="padding: 10px; font-weight: bold; text-align: left;">Location:</td>
            <td style="padding: 10px; text-align: left;">{{ $data['location'] ?? '-' }}</td>
        </tr>
        <tr>
            <td style="padding: 10px; font-weight: bold; text-align: left;">Model:</td>
            <td style="padding: 10px; text-align: left;">{{ $data['model'] ?? '-' }}</td>
        </tr>
    </table>
</div>
