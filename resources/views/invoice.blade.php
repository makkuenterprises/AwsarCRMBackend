    <!-- resources/views/invoice.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <title>Invoice</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { width: 80%; margin: auto; }
        h1 { text-align: center; }
        .invoice-header, .invoice-footer { text-align: center; margin-top: 20px; }
        .invoice-details { margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f4f4f4; }
    </style>
</head>
<body>
    <div class="container">
        <div class="invoice-header">
            <h1>Invoice</h1>
        </div>

        <div class="student-details">
            <h2>Student Information</h2>
            <p><strong>Name:</strong> {{ $student->name }}</p>
            <p><strong>Email:</strong> {{ $student->email }}</p>
            <p><strong>Phone:</strong> {{ $student->phone }}</p>
            <p><strong>City:</strong> {{ $student->city }}</p>
            <p><strong>Father's Name:</strong> {{ $student->fname }}</p>
            <p><strong>Father's Phone:</strong> {{ $student->fphone }}</p>
        </div>

        <div class="invoice-details">
            <h2>Invoice Details</h2>
            <table>
                <tr>
                    <th>Invoice No</th>
                    <th>Course Name</th>
                    <th>Total Amount</th>
                    <th>Paid Amount</th>
                    <th>Remaining Amount</th>
                    <th>Invoice Date</th>
                </tr>
                @foreach ($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->invoice_no }}</td>
                    <td>{{ $invoice->course_name }}</td>
                    <td>{{ $invoice->total_amount }}</td>
                    <td>{{ $invoice->paid_amount }}</td>
                    <td>{{ $invoice->remaining_amount }}</td>
                    <td>{{ $invoice->invoice_date }}</td>
                </tr>
                @endforeach
            </table>
        </div>

        <div class="payment-histories">
            <h2>Payment Histories</h2>
            <table>
                <tr>
                    <th>Transaction ID</th>
                    <th>Payment Type</th>
                    <th>Payment Status</th>
                    <th>Paid Amount</th>
                    <th>Payment Date</th>
                </tr>
                @foreach ($paymentHistories as $paymentHistory)
                <tr>
                    <td>{{ $paymentHistory->transaction_id }}</td>
                    <td>{{ $paymentHistory->payment_type }}</td>
                    <td>{{ $paymentHistory->payment_status }}</td>
                    <td>{{ $paymentHistory->paid_amount }}</td>
                    <td>{{ $paymentHistory->payment_date }}</td>
                </tr>
                @endforeach
            </table>
        </div>

        <div class="invoice-footer">
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>
</html>
