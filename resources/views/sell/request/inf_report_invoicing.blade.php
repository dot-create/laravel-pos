<!-- resources/views/sell/request/inf_report_invoicing.blade.php -->
<div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">@lang('request.invoicing_inf_report') - {{ $request->request_reference }}</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
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
                            <th rowspan="2">@lang('request.stock_on_hand_hs')</th>
                            <th rowspan="2">@lang('request.approved_ipr_qty_hs')</th>
                            <th rowspan="2">@lang('request.in_transit_qty_hs')</th>
                            <th rowspan="2">@lang('request.committed_qty_hs')</th>
                            <th colspan="3" class="text-center">@lang('request.invoicing_info')</th>
                            <th rowspan="2">@lang('request.invoiced_for_req')</th>
                            <th rowspan="2">@lang('request.status')</th>
                        </tr>
                        <tr>
                            <th>@lang('request.pending_invoice')</th>
                            <th>@lang('request.qty_available_for_invoice')</th>
                            <th>@lang('request.live_available_to_invoice')</th>
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
                                <td>{{ $item->internal_req_qty }}</td>
                                <td>{{ $item->stock_on_hand_hs }}</td>
                                <td>{{ $item->approved_ipr_qty_hs }}</td>
                                <td>{{ $item->in_transit_qty_hs }}</td>
                                <td>{{ $item->committed_qty_hs }}</td>
                                <td>{{ $item->pending_invoice }}</td>
                                <td>{{ $item->available_for_invoice }}</td>
                                <td>{{ $item->available_qty }}</td>
                                <td>{{ $item->invoiced_qty }}</td>
                                <td>
                                    @if($item->status_invoice == 'None Invoiced')
                                        <span class="label label-default">@lang('request.none_invoiced')</span>
                                    @elseif($item->status_invoice == 'Partial Invoiced')
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
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div>
</div>