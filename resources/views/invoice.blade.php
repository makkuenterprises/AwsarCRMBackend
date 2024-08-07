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
              background: #fff;
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
            margin-bottom: 5px;
        }
        .info div {
            display: table-cell;
            width: 50%;
            padding: 2px;
            vertical-align: top;
        }
        .company-info h2,
        .student-info h2 {
            color: #000000;
            margin-top: 0;
        }
        .invoice-details, .payment-details, .totals {
            margin-top: 10px;
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
             @if($details->logo)
        <div class="logo">
            <img src="{{$details->logo}}" alt="Company Logo" style="max-width: 200px; height: auto;">
        </div>
    @else
        <p>No logo available</p>
    @endif 
           <div class="company-info">
    <h2>Institute Details</h2>
    <p><strong>Institute Name:</strong> {{ $details->business_name }}</p>
    <p><strong>Address:</strong> {{ $details->address ?? 'Address' }}</p>
    <p><strong>Phone:</strong> {{ $details->phone ?? 'Phone' }}</p>
    <p><strong>Email:</strong> {{ $details->email ?? 'Email' }}</p>
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
                    <td>Rs.  {{ $paymentHistory->paid_amount }}</td>
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
                    @php
    // Function to format number as currency
    function formatCurrency($amount) {
        return number_format(floatval(str_replace(',', '', $amount)), 2, '.', ',');
    }
@endphp
                    <td>{{ $invoice->invoice_no }}</td>
                    <td>{{ $invoice->course_name }}</td>
                    <td>Rs. {{ formatCurrency($invoice->total_amount) }}</td>
<td>Rs. {{ formatCurrency($invoice->paid_amount) }}</td>
<td>Rs. {{ formatCurrency($invoice->remaining_amount) }}</td>
                    <td>{{ $invoice->invoice_date }}</td>
                </tr>
                @endforeach
            </table>
        </div>

        <div class="totals">
            <div>
                <strong>Total Amount: </strong>Rs.  {{ $totalAmount }}
            </div>
            <div>
                <strong>Paid Amount: </strong>Rs.  {{ $paidAmount }}
            </div>
            <div>
                <strong>Outstanding Amount: </strong>Rs.  {{ $outstandingAmount }}
            </div>
        </div>

        <div class="footer">
            <p>Thank you for your enrollment with AWSAR CLASSES. We hope you find our services valuable and enriching.</p>
        </div>
    </div>
</body>
</html>
