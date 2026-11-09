<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindLink - Admin Consent Records</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f4f7f6; 
            padding: 40px; 
            margin: 0;
        }
        .container { 
            background-color: #ffffff; 
            padding: 32px; 
            border-radius: 12px; 
            box-shadow: 0 4px 16px rgba(0,0,0,0.08); 
            max-width: 800px;
            margin: 0 auto;
        }
        h3 {
            color: #1f2937;
            margin-top: 0;
            margin-bottom: 24px;
            font-size: 24px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        th, td { 
            border-bottom: 1px solid #e5e7eb; 
            padding: 12px; 
            text-align: left; 
            color: #4b5563;
        }
        th { 
            background-color: #f9fafb; 
            color: #374151; 
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="container">
    <h3>Student Consent Records</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Student ID</th>
                <th>Consent Status</th>
                <th>Agreed At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($consents as $consent)
            <tr>
                <td>{{ $consent->id }}</td>
                <td>{{ $consent->student_id ?? 'N/A' }}</td>
                <td>
                    @if($consent->is_agreed)
                        <span style="color: #2A9D8F; font-weight: bold;">Agreed</span>
                    @else
                        <span style="color: #e74c3c; font-weight: bold;">Pending</span>
                    @endif
                </td>
                <td>{{ $consent->agreed_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

</body>
</html>