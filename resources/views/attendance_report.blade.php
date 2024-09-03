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
         .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1 class="text-center">Attendance Report for {{ $course_name }}</h1>
    <h3 class="text-center">From: {{ $startDate ?? 'N/A' }} To: {{ $endDate ?? 'N/A' }}</h3>
<hr>
<br>
    <table>
        <thead>
            <tr>
                <th> ID</th>
                <th>Name</th>
                <th>Father's Name</th>
                <th>Phone</th>
                <th>Total </th>
                <th>Present </th>
                <th>Absent </th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
                <tr>
                    <td>{{ $student['id'] }}</td>
                    <td>{{ $student['name'] }}</td>
                    <td>{{ $student['fname'] }}</td>
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
