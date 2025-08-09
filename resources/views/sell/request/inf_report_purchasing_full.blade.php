<!-- resources/views/sell/request/inf_report_purchasing_full.blade.php -->
@extends('layouts.app')

@section('title', __('request.purchasing_inf_report'))

@section('content')
<div class="container-fluid">
    <!-- Blue Bar Separator -->
    <div class="blue-bar" style="height: 4px; background-color: #007bff; margin-bottom: 20px;"></div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">@lang('request.purchasing_inf_report') - {{ $request->request_reference }}</h4>
                    <div>
                        <button type="button" class="btn btn-sm btn-light" id="print-report">
                            <i class="fas fa-print"></i> @lang('messages.print')
                        </button>
                        <a href="{{ route('request.invoicing.inf.report.full', ['id' => $request->id]) }}" 
                           class="btn btn-sm btn-info">
                            <i class="fas fa-file-invoice"></i> @lang('request.invoicing_report')
                        </a>
                        <a href="{{ route('requests') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-times"></i> @lang('messages.close')
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Header Information -->
                    <div class="mb-4">
                        <h5 class="text-primary mb-3">@lang('request.header_information')</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>@lang('request.requisition_number')</th>
                                        <th>@lang('contact.customer')</th>
                                        <th>@lang('request.customer_po_number')</th>
                                        <th>@lang('request.c_po_date')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ $request->request_reference }}</td>
                                        <td>{{ $request->contact->name }}</td>
                                        <td>{{ $request->items[0]->po_number ?? 'N/A' }}</td>
                                        <td>{{ $request->created_at->format('Y-m-d') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Stock History with Toggle -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="text-primary">@lang('request.stock_history')</h5>
                            <button type="button" class="btn btn-sm btn-outline-info" id="toggle-history-columns">
                                <i class="fas fa-eye-slash"></i> @lang('request.show_hide_history')
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>@lang('product.sku')</th>
                                        <th>@lang('product.description')</th>
                                        <th class="history-column">@lang('request.available_qty')</th>
                                        <th class="history-column">@lang('request.stock_on_hand_hs')</th>
                                        <th class="history-column">@lang('request.approved_ipr_qty_hs')</th>
                                        <th class="history-column">@lang('request.in_transit_qty_hs')</th>
                                        <th class="history-column">@lang('request.committed_qty_hs')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($request->items as $item)
                                    <tr>
                                        <td>{{ $item->product->sku ?? $item->variation->sub_sku }}</td>
                                        <td>{{ $item->product->name }} {{ $item->variation->name }}</td>
                                        <td class="history-column">{{ $item->available_qty }}</td>
                                        <td class="history-column">{{ $item->stock_on_hand_hs }}</td>
                                        <td class="history-column">{{ $item->approved_ipr_qty_hs }}</td>
                                        <td class="history-column">{{ $item->in_transit_qty_hs }}</td>
                                        <td class="history-column">{{ $item->committed_qty_hs }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Information Section -->
                    <div class="alert alert-info">
                        <p class="mb-0">@lang('request.internal_req_qty_note')</p>
                    </div>

                    <!-- Draft IPR Section -->
                    <div class="mb-4">
                        <h5 class="text-primary mb-3">@lang('request.draft_ipr_section')</h5>
                        <form id="inf-report-form" action="{{ route('request.update.inf.report') }}" method="POST">
                            @csrf
                            <input type="hidden" name="request_id" value="{{ $request->id }}">
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="3%">
                                                <input type="checkbox" id="select-all-items" class="form-check-input">
                                            </th>
                                            <th>@lang('product.sku')</th>
                                            <th>@lang('request.suggested_qty_to_request')</th>
                                            <th>@lang('request.internal_req_qty')</th>
                                            <th>@lang('request.cso_new_purchasing_req')</th>
                                            <th>@lang('request.received_for_order')</th>
                                            <th>
                                                @lang('request.status')
                                                <select class="form-control form-control-sm status-header-select mt-1">
                                                    <option value="">@lang('messages.all')</option>
                                                    <option value="Not Started">@lang('request.not_started')</option>
                                                    <option value="Requested">@lang('request.requested')</option>
                                                    <option value="Pending Approval">@lang('request.pending_approval')</option>
                                                    <option value="Approved">@lang('request.req_approved')</option>
                                                    <option value="Ordered">@lang('request.ordered')</option>
                                                    <option value="Received">@lang('request.received')</option>
                                                </select>
                                            </th>
                                            <th>@lang('request.committed_for_order')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($request->items as $item)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="items[{{ $item->id }}][select]" 
                                                    class="form-check-input item-select"
                                                    data-item-id="{{ $item->id }}">
                                            </td>
                                            <td>{{ $item->product->sku ?? $item->variation->sub_sku }}</td>
                                            <td>
                                                <input type="number" 
                                                    name="items[{{ $item->id }}][suggested_qty]" 
                                                    value="{{ $item->suggested_qty }}" 
                                                    class="form-control form-control-sm" readonly>
                                            </td>
                                            <td>
                                                <input type="number" 
                                                    name="items[{{ $item->id }}][internal_req_qty]" 
                                                    value="{{ $item->suggested_qty }}" 
                                                    class="form-control form-control-sm internal-req-qty"
                                                    data-item-id="{{ $item->id }}"
                                                    min="0"
                                                    step="1"
                                                    @if($item->draft_saved) readonly @endif>
                                            </td>
                                            <td>
                                                <input type="text" 
                                                    name="items[{{ $item->id }}][cso_new_purchasing_req_no]" 
                                                    value="{{ $item->cso_new_purchasing_req_no }}" 
                                                    class="form-control form-control-sm">
                                            </td>
                                            <td>
                                                <input type="number" 
                                                    name="items[{{ $item->id }}][received_qty]" 
                                                    value="{{ $item->received_qty }}" 
                                                    class="form-control form-control-sm">
                                            </td>
                                            <td>
                                                <select name="items[{{ $item->id }}][status_purchase]" 
                                                        class="form-control form-control-sm status-purchase-select">
                                                    <option value="Not Started" {{ $item->status_purchase == 'Not Started' ? 'selected' : '' }}>
                                                        @lang('request.not_started')
                                                    </option>
                                                    <option value="Requested" {{ $item->status_purchase == 'Requested' ? 'selected' : '' }}>
                                                        @lang('request.requested')
                                                    </option>
                                                    <option value="Pending Approval" {{ $item->status_purchase == 'Pending Approval' ? 'selected' : '' }}>
                                                        @lang('request.pending_approval')
                                                    </option>
                                                    <option value="Approved" {{ $item->status_purchase == 'Approved' ? 'selected' : '' }}>
                                                        @lang('request.req_approved')
                                                    </option>
                                                    <option value="Ordered" {{ $item->status_purchase == 'Ordered' ? 'selected' : '' }}>
                                                        @lang('request.ordered')
                                                    </option>
                                                    <option value="Received" {{ $item->status_purchase == 'Received' ? 'selected' : '' }}>
                                                        @lang('request.received')
                                                    </option>
                                                </select>
                                            </td>
                                            <td>{{ $item->committed_for_order }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-4 d-flex justify-content-between">
                                <button type="button" class="btn btn-info" id="draft-ipr">
                                    <i class="fas fa-file-draft"></i> @lang('request.draft_ipr')
                                </button>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> @lang('messages.save')
                                    </button>
                                    <a href="{{ route('requests') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> @lang('messages.close')
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
$(document).ready(function() {
    // Toggle history columns
    $('#toggle-history-columns').on('click', function() {
        $('.history-column').toggle();
        const isVisible = $('.history-column:visible').length > 0;
        $(this).html(
            isVisible ? 
            '<i class="fas fa-eye-slash"></i> @lang("request.hide_history")' : 
            '<i class="fas fa-eye"></i> @lang("request.show_history")'
        );
    });

    // Print functionality
    $('#print-report').click(function() {
        window.print();
    });

    // Select all items
    $('#select-all-items').change(function() {
        $('.item-select').prop('checked', this.checked);
    });

    // Filter by status
    $('.status-header-select').change(function() {
        const status = $(this).val();
        $('.status-purchase-select').each(function() {
            const row = $(this).closest('tr');
            if (status === "" || $(this).val() === status) {
                row.show();
            } else {
                row.hide();
            }
        });
    });

    // Handle Draft IPR button
    $('#draft-ipr').on('click', function() {
        const selectedItems = [];
        let hasSelected = false;
        
        $('.item-select:checked').each(function() {
            hasSelected = true;
            const itemId = $(this).data('item-id');
            const qtyInput = $(`input.internal-req-qty[data-item-id="${itemId}"]`);
            const qtyValue = qtyInput.val();
            
            // Validate quantity
            if (!qtyValue || qtyValue <= 0) {
                toastr.error("@lang('request.invalid_quantity')");
                return false;
            }
            
            selectedItems.push({
                id: itemId,
                internal_req_qty: qtyValue
            });
        });
        
        if (!hasSelected) {
            toastr.error("@lang('request.no_items_selected')");
            return;
        }
        
        // Disable button during processing
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> @lang("request.processing")');
        
        // Send AJAX request
        $.ajax({
            url: "{{ route('request.save.draft') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                items: selectedItems
            },
            success: function(response) {
                btn.prop('disabled', false).html('<i class="fas fa-file-draft"></i> @lang("request.draft_ipr")');
                
                if (response.success) {
                    toastr.success(response.msg);
                    
                    // Make inputs readonly and uncheck items
                    selectedItems.forEach(item => {
                        const qtyInput = $(`input.internal-req-qty[data-item-id="${item.id}"]`);
                        qtyInput.attr('readonly', true);
                        $(`.item-select[data-item-id="${item.id}"]`).prop('checked', false);
                    });
                    
                    // Uncheck select all
                    $('#select-all-items').prop('checked', false);
                } else {
                    toastr.error(response.msg);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-file-draft"></i> @lang("request.draft_ipr")');
                toastr.error('@lang('messages.something_went_wrong')');
            }
        });
    });
    
    // Handle form submission
    $('#inf-report-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> @lang("request.saving")');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> @lang("messages.save")');
                
                if (response.success) {
                    toastr.success(response.msg);
                    
                    // Optional: Refresh data after save
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    toastr.error(response.msg);
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> @lang("messages.save")');
                toastr.error('@lang('messages.something_went_wrong')');
            }
        });
    });
});
</script>
@endsection