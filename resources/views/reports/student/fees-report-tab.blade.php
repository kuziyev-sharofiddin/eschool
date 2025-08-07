<div class="card">
    <div class="card-body">
        <h4 class="card-title">{{ __('fees_report') }}</h4>
        @if(isset($studentFees) && count($studentFees) > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>{{ __('fees_name') }}</th>
                        <th>{{ __('type') }}</th>
                        <th>{{ __('amount') }}</th>
                        <th>{{ __('due_date') }}</th>
                        <th>{{ __('paid_amount') }}</th>
                        <th>{{ __('payment_mode') }}</th>
                        <th>{{ __('date') }}</th>
                        <th>{{ __('status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($studentFees as $fee)
                    <tr>
                        <td>{{ $fee->fees->name ?? '-' }}</td>
                        <td>
                            @if(isset($fee->fees->fees_class_type) && count($fee->fees->fees_class_type) > 0)
                                @if(isset($fee->fees->fees_class_type[0]->fees_type))
                                    {{ $fee->fees->fees_class_type[0]->fees_type->name ?? __('Compulsory') }}
                                @else
                                    {{ __('compulsory') }}
                                @endif
                            @else
                                {{ __('compulsory') }}
                            @endif
                        </td>
                        <td>{{ number_format($fee->amount ?? 0, 2) }}</td>
                        <td>{{ $fee->fees->due_date ?? '-' }}</td>
                        <td>
                            @php
                                $paidAmount = 0;
                                if(isset($fee->compulsory_fee) && count($fee->compulsory_fee) > 0) {
                                    foreach($fee->compulsory_fee as $cf) {
                                        $paidAmount += $cf->amount ?? 0;
                                    }
                                }
                            @endphp
                            {{ number_format($paidAmount, 2) }}
                        </td>
                        <td>
                            @if(isset($fee->compulsory_fee) && count($fee->compulsory_fee) > 0)
                                {{ $fee->compulsory_fee[0]->mode ?? '-' }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $fee->date ?? '-' }}</td>
                        <td>
                            @php
                                $status = $fee->status ?? 'unpaid';
                                
                                $badgeClass = 'badge-secondary';
                                if($status == 'paid') {
                                    $badgeClass = 'badge-success';
                                } elseif($status == 'partial') {
                                    $badgeClass = 'badge-warning';
                                } elseif($status == 'unpaid') {
                                    $badgeClass = 'badge-secondary';
                                } elseif($status == 'overdue') {
                                    $badgeClass = 'badge-danger';
                                }
                            @endphp
                            <label class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</label>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="alert alert-info mt-3">
            {{ __('no_fees_records_found_for_this_student') }}
        </div>
        @endif
    </div>
</div>
