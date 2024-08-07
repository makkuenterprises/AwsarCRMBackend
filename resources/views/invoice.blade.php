<!DOCTYPE html>
<html>
<head>
    <title>Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            margin-right: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e2e2;
        }
        .header h1 {
            margin: 0;
            color: #000000;
        }
        .info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .info div {
            display: table-cell;
            width: 50%;
            padding: 10px;
            vertical-align: top;
        }
        .company-info h2,
        .student-info h2 {
            color: #000000;
            margin-top: 0;
        }
        .invoice-details, .payment-details, .totals {
            margin-top: 20px;
        }
        table {
            width: 95%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
            color: #333;
        }
        .totals div {
            font-size: 18px;
            font-weight: bold;
            color: #000000;
            margin-top: 10px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #e2e2e2;
            font-size: 14px;
            margin-right: 22px;
        }
        .footer p {
            text-align: center;
            margin: 0;
            color: #666;
            margin-right: 22px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Invoice</h1>
        </div>

        <div class="info">
            <div class="company-info">
                <h2>Company Details</h2>
                <p><strong>Company Name:</strong> Awsar Classes</p>
                <p><strong>Address:</strong> 123 Business Road, Business City, BC 12345</p>
                <p><strong>Phone:</strong> +1 (234) 567-8900</p>
                <p><strong>Email:</strong> contact@yourcompany.com</p>
            </div>
            <div class="student-info">
                <h2>Student Information</h2>
                <p><strong>Name:</strong> {{ $student->name }}</p>
                <p><strong>Email:</strong> {{ $student->email }}</p>
                <p><strong>Phone:</strong> {{ $student->phone }}</p>
                <p><strong>City:</strong> {{ $student->city }}</p>
                <p><strong>Father's Name:</strong> {{ $student->fname }}</p>
                <p><strong>Father's Phone:</strong> {{ $student->fphone }}</p>
            </div>
        </div>

        <div class="payment-details">
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

        <div class="totals">
            <div>
                <strong>Total Amount: </strong> {{ $totalAmount }}
            </div>
            <div>
                <strong>Paid Amount: </strong> {{ $paidAmount }}
            </div>
            <div>
                <strong>Outstanding Amount: </strong> {{ $outstandingAmount }}
            </div>
        </div>

        <div class="footer">
            <p>Thank you for your enrollment with AWSAR CLASSES. We hope you find our services valuable and enriching.</p>
        </div>
    </div>
</body>
</html>
