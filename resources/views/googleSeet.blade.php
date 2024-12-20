<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Sheet Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        .message {
            margin-bottom: 20px;
            font-size: 18px;
            color: green;
        }
    </style>
</head>
<body>
    <h1>Google Sheet Test</h1>

    @if(isset($message))
        <div class="message">{{ $message }}</div>
    @endif

    @if(!empty($data))
        <table>
            <thead>
                <tr>
                    @foreach($data[0] as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach(array_slice($data, 1) as $row)
                    <tr>
                        @foreach($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No data to display.</p>
    @endif
</body>
</html>
