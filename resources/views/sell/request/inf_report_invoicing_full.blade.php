@extends('layouts.app')
@section('title', __('request.invoicing_inf_report'))
@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h4>@lang('request.invoicing_inf_report') - {{ $request->request_reference }}</h4>
                    <div>
                        <a href="{{ route('home') }}" class="btn btn-sm btn-info">
                            <i class="fas fa-home"></i> @lang('messages.home')
                        </a>
                        <a href="{{ route('request.list.quote.accept') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-list"></i> @lang('request.accepted_quotes')
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Header Information Section -->
                    <div class="header-info-section bg-light p-3 mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>@lang('request.requisition_number'):</strong> {{ $request->request_reference }}
                            </div>
                            <div class="col-md-3">
                                <strong>@lang('contact.customer'):</strong> {{ $request->contact->name }}
                            </div>
                            <div class="col-md-3">
                                <strong>@lang('request.customer_po_number'):</strong> {{ $request->items[0]->po_number ?? 'N/A' }}
                            </div>
                            <div class="col-md-3">
                                <strong>@lang('request.c_po_date'):</strong> {{ $request->created_at->format('Y-m-d') }}
                            </div>
                        </div>
                    </div>

                    <form id="invoicing-inf-report-form" action="{{ route('request.update.invoicing.inf.report') }}" method="POST">
                        @csrf
                        <input type="hidden" name="request_id" value="{{ $request->id }}">
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="toggleHistoryColumns" checked>
                            <label class="form-check-label" for="toggleHistoryColumns">
                                @lang('request.show_history_columns')
                            </label>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th rowspan="2">@lang('request.requisition_number')</th>
                                        <th rowspan="2">@lang('contact.customer')</th>
                                        <th rowspan="2">@lang('product.sku')</th>
                                        <th rowspan="2">@lang('product.description')</th>
                                        <th rowspan="2">@lang('request.accepted_qty')</th>
                                        <th rowspan="2">@lang('request.customer_po_number')</th>
                                        <th rowspan="2">@lang('request.c_po_date')</th>
                                        <th rowspan="2">@lang('request.ipr_qty')</th>
                                        <th rowspan="2" class="history-col">@lang('request.stock_on_hand_hs')</th>
                                        <th rowspan="2" class="history-col">@lang('request.approved_ipr_qty_hs')</th>
                                        <th rowspan="2" class="history-col">@lang('request.in_transit_qty_hs')</th>
                                        <th rowspan="2" class="history-col">@lang('request.committed_qty_hs')</th>
                                        <th colspan="3" class="text-center history-col">@lang('request.invoicing_info')</th>
                                        <th rowspan="2">@lang('request.qty_to_generate_invoice')</th>
                                        <th rowspan="2">@lang('request.invoiced_for_req')</th>
                                        <th rowspan="2">@lang('request.status')</th>
                                    </tr>
                                    <tr>
                                        <th class="history-col">@lang('request.pending_invoice')</th>
                                        <th class="history-col">@lang('request.qty_available_for_invoice')</th>
                                        <th class="history-col">@lang('request.live_available_to_invoice')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($request->items as $item)
                                        <tr data-item-id="{{ $item->id }}">
                                            <td>{{ $request->request_reference }}</td>
                                            <td>{{ $request->contact->name }}</td>
                                            <td>{{ $item->product->sku ?? $item->variation->sub_sku }}</td>
                                            <td>{{ $item->product->name }} {{ $item->variation->name }}</td>
                                            <td class="accepted-qty">{{ $item->accepted_qty }}</td>
                                            <td>{{ $item->po_number }}</td>
                                            <td>{{ $request->created_at->format('Y-m-d') }}</td>
                                            <td>{{ $item->internal_req_qty }}</td>
                                            <td class="history-col">{{ $item->stock_on_hand_hs }}</td>
                                            <td class="history-col">{{ $item->approved_ipr_qty_hs }}</td>
                                            <td class="history-col">{{ $item->in_transit_qty_hs }}</td>
                                            <td class="history-col">{{ $item->committed_qty_hs }}</td>
                                            <td class="history-col">{{ $item->pending_invoice }}</td>
                                            <td class="history-col">{{ $item->available_for_invoice }}</td>
                                            <td class="history-col">{{ $item->available_qty }}</td>
                                            <!-- Editable Qty to generate invoice with restriction -->
                                            <td>
                                                <input type="number" 
                                                    name="items[{{ $item->id }}][qty_to_generate]"
                                                    value="{{ old('items.'.$item->id.'.qty_to_generate', $item->invoiced_qty) }}"
                                                    class="form-control input-sm qty-to-generate"
                                                    min="0"
                                                    max="{{ $item->available_qty }}"
                                                    data-available-qty="{{ $item->available_qty }}"
                                                    data-accepted-qty="{{ $item->accepted_qty }}"
                                                    required>
                                            </td>
                                            <!-- Invoiced for Reg (auto-filled) -->
                                            <td class="invoiced-for-req">{{ $item->invoiced_qty }}</td>
                                            <!-- Status (auto-calculated) -->
                                            <td class="status-col">
                                                @if($item->invoiced_qty == 0)
                                                    <span class="label label-default">@lang('request.not_started')</span>
                                                @elseif($item->invoiced_qty < $item->accepted_qty)
                                                    <span class="label label-warning">@lang('request.partial_invoiced')</span>
                                                @else
                                                    <span class="label label-success">@lang('request.invoiced')</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-primary" id="draft-sale-btn">
                                @lang('request.draft_sale')
                            </button>
                            <a href="{{ route('request.list.quote.accept') }}" class="btn btn-default">
                                @lang('messages.back')
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Toggle history columns
    $('#toggleHistoryColumns').change(function() {
        $('.history-col').toggle(this.checked);
    }).trigger('change');

    // Validate quantity before submission
    $('.qty-to-generate').on('input', function() {
        const availableQty = $(this).data('available-qty');
        let enteredQty = parseFloat($(this).val()) || 0;
        
        if (enteredQty > availableQty) {
            $(this).val(availableQty);
            toastr.warning('@lang('request.qty_exceeds_available')');
        }
        
        // Also ensure it doesn't exceed accepted quantity
        const acceptedQty = $(this).data('accepted-qty');
        if (enteredQty > acceptedQty) {
            $(this).val(acceptedQty);
            toastr.warning('@lang('request.qty_exceeds_accepted')');
        }
    });

    // Form submission handling
    $('#invoicing-inf-report-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        
        // Client-side validation
        let valid = true;
        $('.qty-to-generate').each(function() {
            const qty = parseFloat($(this).val()) || 0;
            const availableQty = parseFloat($(this).data('available-qty'));
            const acceptedQty = parseFloat($(this).data('accepted-qty'));
            
            if (qty > availableQty) {
                valid = false;
                $(this).addClass('is-invalid');
                toastr.error('@lang('request.qty_exceeds_available')');
            } else if (qty > acceptedQty) {
                valid = false;
                $(this).addClass('is-invalid');
                toastr.error('@lang('request.qty_exceeds_accepted')');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!valid) return;

        // Show loading indicator
        $('#draft-sale-btn').html('<i class="fas fa-spinner fa-spin"></i> Processing');
        $('#draft-sale-btn').prop('disabled', true);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success(response.msg);
                    
                    // Update UI with new data
                    $.each(response.items, function(index, item) {
                        const row = form.find(`tr[data-item-id="${item.id}"]`);
                        row.find('.invoiced-for-req').text(item.invoiced_qty);
                        
                        // Update status
                        let statusHtml = '';
                        if (item.invoiced_qty == 0) {
                            statusHtml = '<span class="label label-default">@lang('request.not_started')</span>';
                        } else if (item.invoiced_qty < item.accepted_qty) {
                            statusHtml = '<span class="label label-warning">@lang('request.partial_invoiced')</span>';
                        } else {
                            statusHtml = '<span class="label label-success">@lang('request.invoiced')</span>';
                        }
                        row.find('.status-col').html(statusHtml);
                    });
                } else {
                    toastr.error(response.msg);
                }
            },
            error: function(xhr) {
                let errorMsg = '@lang('messages.something_went_wrong')';
                if (xhr.responseJSON && xhr.responseJSON.msg) {
                    errorMsg = xhr.responseJSON.msg;
                }
                toastr.error(errorMsg);
            },
            complete: function() {
                // Reset button state
                $('#draft-sale-btn').html('@lang('request.draft_sale')');
                $('#draft-sale-btn').prop('disabled', false);
            }
        });
    });
});
</script>
@endsection