<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Attendance Report</h1>

    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Father's Name</th>
                <th>Father's Phone</th>
                <th>Total Absent Days</th>
                <th>Absent Days This Month</th>
                <th>Today's Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
                <tr>
                    <td>{{ $student['id'] }}</td>
                    <td>{{ $student['name'] }}</td>
                    <td>{{ $student['phone'] }}</td>
                    <td>{{ $student['fname'] }}</td>
                    <td>{{ $student['fphone'] }}</td>
                    <td>{{ $student['total_absent_days'] }}</td>
                    <td>{{ $student['absent_days_current_month'] }}</td>
                    <td>{{ $student['today_status'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
