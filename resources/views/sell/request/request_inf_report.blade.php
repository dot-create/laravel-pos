<div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">@lang('request.Inf Report') - {{ $request->request_reference }}</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="inf-report-form" action="{{ route('request.update.inf.report') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>@lang('request.requisition_number')</th>
                                        <th>@lang('contact.customer')</th>
                                        <th>@lang('product.sku')</th>
                                        <th>@lang('product.product_name')</th>
                                        <th>@lang('request.accepted_qty')</th>
                                        <th>@lang('request.order_date')</th>
                                        <th>@lang('request.customer_po_number')</th>
                                        <th>@lang('request.ipr_qty')</th>
                                        <th>@lang('request.stock_on_hand_hs')</th>
                                        <th>@lang('request.approved_ipr_qty_hs')</th>
                                        <th>@lang('request.in_transit_qty_hs')</th>
                                        <th>@lang('request.committed_qty_hs')</th>
                                        <th>@lang('request.available_qty')</th>
                                        <th>@lang('request.available_for_invoice')</th>
                                        <th>@lang('request.suggested_qty')</th>
                                        <th>@lang('request.cso_req_number')</th>
                                        <th>@lang('request.new_approved_qty')</th>
                                        <th>@lang('request.invoiced_qty')</th>
                                        <th>@lang('request.pending_invoice')</th>
                                        <th>@lang('request.committed_for_order')</th>
                                        <th>@lang('request.received_for_order')</th>
                                        <th>@lang('request.live_available')</th>
                                        <th>@lang('request.qty_to_generate_invoice')</th>
                                        <th>@lang('request.status_purchase')</th>
                                        <th>@lang('request.status_invoice')</th>
                                        <th>@lang('request.internal_req_qty')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($request->items as $item)
                                        <tr>
                                            <td>{{ $request->request_reference }}</td>
                                            <td>{{ $request->contact->name }}</td>
                                            <td>{{ $item->product->sku ?? $item->variation->sub_sku }}</td>
                                            <td>{{ $item->product->name ?? '' }}</td>
                                            <td>{{ $item->accepted_qty }}</td>
                                            <td>{{ $request->created_at->format('Y-m-d') }}</td>
                                            <td>{{ $item->po_number }}</td>
                                            <td>{{ $item->ipr_qty }}</td>
                                            <td>{{ $item->stock_on_hand_hs }}</td>
                                            <td>{{ $item->approved_ipr_qty_hs }}</td>
                                            <td>{{ $item->in_transit_qty_hs }}</td>
                                            <td>{{ $item->committed_qty_hs }}</td>
                                            <td>{{ $item->available_qty }}</td>
                                            <td>{{ $item->available_for_invoice }}</td>
                                            <td>{{ $item->suggested_qty }}</td>
                                            <td class="cso-fields" style="display: none;">
                                                <input type="text" name="items[{{ $item->id }}][cso_new_purchasing_req_no]" 
                                                    value="{{ $item->cso_new_purchasing_req_no }}" class="form-control input-sm">
                                            </td>
                                            <td class="cso-fields" style="display: none;">
                                                <input type="number" step="0.0001" name="items[{{ $item->id }}][new_approved_qty_internal_req]" 
                                                    value="{{ $item->new_approved_qty_internal_req }}" class="form-control input-sm">
                                            </td>
                                            <td>{{ $item->invoiced_qty }}</td>
                                            <td>{{ $item->pending_invoice_qty }}</td>
                                            <td>{{ $item->committed_for_order }}</td>
                                            <td>{{ $item->received_for_order }}</td>
                                            <td>{{ $item->live_available_for_order }}</td>
                                            <td>{{ $item->qty_to_generate_invoice }}</td>
                                            <td>
                                                <select name="items[{{ $item->id }}][status_purchase]" 
                                                    class="form-control input-sm status-purchase-select" 
                                                    data-item-id="{{ $item->id }}">
                                                    <option value="Not Necessary IPR" {{ $item->status_purchase == 'Not Necessary IPR' ? 'selected' : '' }}>
                                                        Not Necessary IPR
                                                    </option>
                                                    <option value="Requested" {{ $item->status_purchase == 'Requested' ? 'selected' : '' }}>
                                                        Requested
                                                    </option>
                                                </select>
                                            </td>
                                            <td>
                                                <span class="label 
                                                    {{ $item->status_invoice == 'Invoiced' ? 'label-success' : 
                                                       ($item->status_invoice == 'Partial Invoiced' ? 'label-warning' : 'label-default') }}">
                                                    {{ $item->status_invoice }}
                                                </span>
                                            </td>
                                            <td class="cso-fields" style="display: none;">
                                                <input type="number" step="0.0001" name="items[{{ $item->id }}][internal_req_qty]" 
                                                    value="{{ $item->internal_req_qty }}" class="form-control input-sm">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
             <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle status change
    $('.status-purchase-select').change(function() {
        const itemId = $(this).data('item-id');
        const status = $(this).val();
        const row = $(this).closest('tr');
        
        if (status === 'Requested') {
            row.find('.cso-fields').show();
        } else {
            row.find('.cso-fields').hide();
        }
    });
    
    // Initialize visibility
    $('.status-purchase-select').each(function() {
        const status = $(this).val();
        const row = $(this).closest('tr');
        
        if (status === 'Requested') {
            row.find('.cso-fields').show();
        }
    });
});
</script>