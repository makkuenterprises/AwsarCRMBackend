<!DOCTYPE html>
<html>
<head>
    <title>Attendance Report</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Attendance Report for {{ $course_name }}</h1>
    <p>From: {{ $startDate ?? 'N/A' }} To: {{ $endDate ?? 'N/A' }}</p>

    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Total Days</th>
                <th>Present Days</th>
                <th>Absent Days</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
                <tr>
                    <td>{{ $student['id'] }}</td>
                    <td>{{ $student['name'] }}</td>
                    <td>{{ $student['phone'] }}</td>
                    <td>{{ $student['total_days'] }}</td>
                    <td>{{ $student['present_days'] }}</td>
                    <td>{{ $student['absent_days'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
