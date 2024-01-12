<!DOCTYPE html>
<html>

<head>
    <title>Invoice</title>
    <style>
        /* Add your styling here */
    </style>
</head>

<body>
    {{-- <img src="{{ asset('images/logo.png') }}" alt="Company Logo"> --}}

    <h1>Invoice</h1>
    <span>Status: {{ $invoice->status }}</span>

    <p><strong>Property Address:</strong> {{ $property->address }}</p>
    <p><strong>Phone:</strong> {{ $property->phone }}</p>
    <p><strong>Email:</strong> {{ $property->email }}</p>

    <table border="1">
        <thead>
            <tr>
                <th>S/N</th>
                <th>Date</th>
                <th>Transaction</th>
                <th>Charges(Debit)</th>
                <th>Payments(Credit)</th>
                <th>Folio(Balance)</th>
            </tr>
        </thead>
        <tbody>
            @if (isset($charges) && $charges->count() > 0)
                @php
                    $sl = 1;
                @endphp
                @foreach ($charges as $charge)
                    <tr>
                        <td>{{ $sl++ }}</td>
                        <td>{{ \Carbon\Carbon::parse($charge->date_incurred)->format('Y-m-d') }}</td>
                        <td>{{ $charge->description }}</td>
                        <td>{{ number_format($charge->amount, 2) }}</td>
                        <td>
                            @if (isset($payments) && $payments->count() > 0)
                                @php
                                    $paymentAmount = 0;
                                @endphp
                                @foreach ($payments as $payment)
                                    @if (
                                        \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') ==
                                            \Carbon\Carbon::parse($charge->date_incurred)->format('Y-m-d'))
                                        {{ number_format($payment->amount, 2) }}
                                        @php
                                            $paymentAmount += $payment->amount;
                                        @endphp
                                    @endif
                                @endforeach
                            @endif
                        </td>
                        <td>
                            @php
                                $outstandingBalance = $charge->amount - $paymentAmount;
                                $outstandingBalances[$charge->id] = $outstandingBalance;
                            @endphp
                            {{ number_format($outstandingBalance, 2) }}
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>


    <p><strong>Total Paid (CR):</strong> ${{ $totalPayments }}</p>
    <p><strong>Total Amount (DR):</strong> ${{ $totalAmount }}</p>
    <p><strong>Outstanding Balance:</strong> ${{ number_format(array_sum($outstandingBalances), 2) }}</p>

    <p class="footer">
        Guest Signature({{ $guest->last_name }}): ___________________ <br>
        Agent Signature: ___________________
    </p>

    <!-- Any additional information -->

    {{-- <pre>
        Debug Information:
        Charges: @php print_r($charges); @endphp
        Payments: @php print_r($payments); @endphp
        Outstanding Balances: @php print_r($outstandingBalances); @endphp
    </pre> --}}
</body>

</html>
