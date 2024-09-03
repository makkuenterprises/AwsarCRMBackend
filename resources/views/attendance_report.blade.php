<!DOCTYPE html>
<html>
<head>
    <title>Attendance Report</title>
    <style>
        .text-center {
            text-align: center;
        }
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
    </style>
</head>
<body>
    <h1 class="text-center">Student Attendance Report</h1>
    <h2 class="text-center">Date Range: {{ $startDate ?? 'N/A' }} to {{ $endDate ?? 'N/A' }}</h2>
    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Email</th>
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
                    <td>{{ $student['email'] }}</td>
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
