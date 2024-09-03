<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    
   <h1 class="text-center">Student Enrollment Report</h1>
    <h1 class="text-center">Date: {{ date('Y-m-d') }}</h1>

    @php
        $serialNumber = 1;
    @endphp

    @foreach($students as $index => $student)
        <h2>{{ $index + 1 }}. {{ $student['student_name'] }} (Student ID: {{ $student['student_id'] }})</h2>
      
        
        @foreach($student['courses'] as $course)
            <h3>Course: {{ $course['course_name'] }} (ID: {{ $course['course_id'] }})</h3>
           <p>Enrollment Date: {{ \Carbon\Carbon::parse($course['enrollment_date'])->format('Y-m-d') }}</p>
            <table>
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Payment Type</th>
                        <th>Payment Status</th>
                        <th>Paid Amount</th>
                        <th>Payment Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($course['payments'] as $payment)
                        <tr>
                            <td>{{ $payment['transaction_id'] }}</td>
                            <td>{{ $payment['payment_type'] }}</td>
                            <td>{{ $payment['payment_status'] }}</td>
                            <td>{{ $payment['paid_amount'] }}</td>
                            <td>{{ $payment['payment_date'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach

     
    @endforeach
</body>
</html>
