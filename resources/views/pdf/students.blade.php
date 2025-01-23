<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
    <h1>Students List</h1>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Course Title</th>
                <th>Status</th>
                <th>Date of Birth</th>
                <th>Contact Number</th>
                <th>Address</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $student->student_id }}</td>
                    <td>
                        {{ $student->title }}
                        {{ $student->first_name }}
                        {{ $student->middle_name }}
                        {{ $student->last_name }}
                    </td>
                    <td>{{ $student->email }}</td>
                    <td>{{ $student->course_title->name ?? 'N/A' }}</td>
                    <td>{{ $student->student_status->name ?? 'N/A' }}</td>
                    <td>{{ $student->date_of_birth }}</td>
                    <td>{{ $student->contact_number }}</td>
                    <td>
                        {{ $student->address }},
                        {{ $student->city }},
                        {{ $student->country }},
                        {{ $student->postcode }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">No data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('d-m-Y') }}
    </div>
</body>
</html>
