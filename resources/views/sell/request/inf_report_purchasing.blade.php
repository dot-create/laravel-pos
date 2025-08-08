<!-- resources/views/sell/request/inf_report_purchasing.blade.php -->
<div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">@lang('request.purchasing_inf_report') - {{ $request->request_reference }}</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="inf-report-form" action="{{ route('request.update.inf.report') }}" method="POST">
            @csrf
            <input type="hidden" name="request_id" value="{{ $request->id }}">
            <div class="modal-body">
                <!-- Header Information Table -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>@lang('request.header_information')</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
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

                <!-- Stock History Section -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>@lang('request.stock_history')</h5>
                        <button type="button" class="btn btn-sm btn-info" id="toggle-history-columns">
                            @lang('request.show_hide_history')
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
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
                    <p>@lang('request.internal_req_qty_note')</p>
                </div>

                <!-- Main Items Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="3%">@lang('request.select')</th>
                                <th>@lang('request.suggested_qty_to_request')</th>
                                <th>@lang('request.internal_req_qty')</th>
                                <th>@lang('request.cso_new_purchasing_req')</th>
                                <th>@lang('request.received_for_order')</th>
                                <th>
                                    @lang('request.status')
                                    <select class="form-control input-sm status-header-select">
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
                                <td>
                                    <input type="number" 
                                        name="items[{{ $item->id }}][suggested_qty]" 
                                        value="{{ $item->suggested_qty }}" 
                                        class="form-control input-sm" readonly>
                                </td>
                                <td>
                                    <input type="number" 
                                        name="items[{{ $item->id }}][internal_req_qty]" 
                                        value="{{ $item->suggested_qty }}" 
                                        class="form-control input-sm internal-req-qty"
                                        @if($item->draft_saved) readonly @endif
                                        data-item-id="{{ $item->id }}">
                                </td>
                                <td>
                                    <input type="text" 
                                        name="items[{{ $item->id }}][cso_new_purchasing_req_no]" 
                                        value="{{ $item->cso_new_purchasing_req_no }}" 
                                        class="form-control input-sm">
                                </td>
                                <td>
                                    <input type="number" 
                                        name="items[{{ $item->id }}][received_qty]" 
                                        value="{{ $item->received_qty }}" 
                                        class="form-control input-sm">
                                </td>
                                <td>
                                    <select name="items[{{ $item->id }}][status_purchase]" 
                                            class="form-control input-sm status-purchase-select">
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
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-info" id="draft-ipr">
                    @lang('request.draft_ipr')
                </button>
                <button type="submit" class="btn btn-primary">
                    @lang('messages.save')
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    @lang('messages.close')
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Toggle history columns
    $('#toggle-history-columns').on('click', function() {
        $('.history-column').toggle();
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

    // Handle status changes
    $('.status-purchase-select').each(function() {
        toggleStatusFields($(this));
    }).change(function() {
        toggleStatusFields($(this));
    });
    
    function toggleStatusFields(select) {
        const row = select.closest('tr');
        const status = select.val();
        
        // Show all fields by default
        row.find('input, select').closest('td').show();
        
        // Hide fields based on status
        if (status === 'Not Necessary IPR' || status === 'Not Started') {
            row.find('input[name*="cso_new_purchasing_req_no"]').closest('td').hide();
            row.find('input[name*="internal_req_qty"]').closest('td').hide();
        }
    }
    
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
            
            // Make input readonly immediately
            qtyInput.attr('readonly', true);
        });
        
        if (!hasSelected) {
            toastr.error("@lang('request.no_items_selected')");
            return;
        }
        
        // Send AJAX request
        $.ajax({
            url: "{{ route('request.save.draft') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                items: selectedItems
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.msg);
                    // Uncheck all checkboxes
                    $('.item-select:checked').prop('checked', false);
                } else {
                    toastr.error(response.msg);
                }
            },
            error: function(xhr) {
                toastr.error('@lang('messages.something_went_wrong')');
            }
        });
    });
    
    // Handle form submission
    $('#inf-report-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success(response.msg);
                    $('.modal').modal('hide');
                } else {
                    toastr.error(response.msg);
                }
            },
            error: function(xhr) {
                toastr.error('@lang('messages.something_went_wrong')');
            }
        });
    });
});
</script>