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
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>@lang('request.requisition_number')</th>
                                <th>@lang('contact.customer')</th>
                                <th>@lang('product.sku')</th>
                                <th>@lang('product.description')</th>
                                <th>@lang('request.accepted_qty')</th>
                                <th>@lang('request.customer_po_number')</th>
                                <th>@lang('request.c_po_date')</th>
                                <th>@lang('request.ipr_qty')</th>
                                <th>@lang('request.stock_on_hand_hs')</th>
                                <th>@lang('request.approved_ipr_qty_hs')</th>
                                <th>@lang('request.in_transit_qty_hs')</th>
                                <th>@lang('request.committed_qty_hs')</th>
                                <th>@lang('request.available_qty')</th>
                                <th>@lang('request.suggested_qty_to_request')</th>
                                <th>@lang('request.cso_new_purchasing_req')</th>
                                <th>@lang('request.internal_req_qty')</th>
                                <th>@lang('request.received_for_order')</th>
                                <th>@lang('request.status')</th>
                                <th>@lang('request.committed_for_order')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($request->items as $item)
                                <tr>
                                    <td>{{ $request->request_reference }}</td>
                                    <td>{{ $request->contact->name }}</td>
                                    <td>{{ $item->product->sku ?? $item->variation->sub_sku }}</td>
                                    <td>{{ $item->product->name }} {{ $item->variation->name }}</td>
                                    <td>{{ $item->accepted_qty }}</td>
                                    <td>{{ $item->po_number }}</td>
                                    <td>{{ $request->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <input type="number" 
                                            name="items[{{ $item->id }}][internal_req_qty]" 
                                            value="{{ $item->internal_req_qty }}" 
                                            class="form-control input-sm">
                                    </td>
                                    <td>{{ $item->stock_on_hand_hs }}</td>
                                    <td>{{ $item->approved_ipr_qty_hs }}</td>
                                    <td>{{ $item->in_transit_qty_hs }}</td>
                                    <td>{{ $item->committed_qty_hs }}</td>
                                    <td>{{ $item->available_qty }}</td>
                                    <td>{{ $item->suggested_qty }}</td>
                                    <td>
                                        <input type="text" 
                                            name="items[{{ $item->id }}][cso_new_purchasing_req_no]" 
                                            value="{{ $item->cso_new_purchasing_req_no }}" 
                                            class="form-control input-sm">
                                    </td>
                                    <td>
                                        <input type="number" 
                                            name="items[{{ $item->id }}][new_approved_qty_internal_req]" 
                                            value="{{ $item->new_approved_qty_internal_req }}" 
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
                <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle status changes to show/hide fields
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
            row.find('input[name*="new_approved_qty_internal_req"]').closest('td').hide();
            row.find('input[name*="internal_req_qty"]').closest('td').hide();
        }
    }
    
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
                toastr.error(__('messages.something_went_wrong'));
            }
        });
    });
});
</script>