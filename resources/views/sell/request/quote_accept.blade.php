@extends('layouts.app')
@section('title', __('request.accept_quote'))

@section('content')
<section class="content-header no-print">
    <h1>
        Accept Quote
    </h1>
</section>

<section class="content no-print">
    {!! Form::open(['url' => action('RequestController@accepteQuoteForm', [$request->id]), 'method' => 'post']) !!}
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">@lang('request.accept_quote')</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('contact_id', __('contact.customer') . ':') !!}
                                <p>{{ $request->contact->name ?? $request->contact->supplier_business_name }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('request_reference', __('request.ref_no') . ':') !!}
                                <p>{{ $request->request_reference }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('status', __('request.status') . ':') !!}
                                <p>{{ $request->status }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12" style="overflow-x: auto;">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>@lang('product.product_name')</th>
                                        <th>@lang('request.quantity')</th>
                                        <th>@lang('request.accepted_qty')</th>
                                        <th>@lang('request.order_date')</th>
                                        <th>@lang('request.customer_po_number')</th>
                                        <th>@lang('request.cso_purchasing_req')</th>
                                        <th>@lang('request.stock_on_hand_hs')</th>
                                        <th>@lang('request.approved_ipr_qty_hs')</th>
                                        <th>@lang('request.in_transit_qty_hs')</th>
                                        <th>@lang('request.committed_qty_hs')</th>
                                        <th>@lang('request.available_qty')</th>
                                        <th>@lang('request.qty_available_for_invoice')</th>
                                        <th>@lang('request.suggested_qty_to_request')</th>
                                        <th>@lang('request.invoiced_for_this_req')</th>
                                        <th>@lang('request.pending_invoice')</th>
                                        <th>@lang('request.committed_for_this_order')</th>
                                        <th>@lang('request.received_for_this_order')</th>
                                        <th>@lang('request.live_available_for_this_order')</th>
                                        <th>@lang('request.qty_to_generate_invoice')</th>
                                        <th>@lang('request.status_purchase')</th>
                                        <th>@lang('request.status_invoice')</th>
                                        <th>@lang('request.internal_req_qty')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $item)
                                    <tr data-item-id="{{ $item->id }}">
                                        <td>{{ $item->product->name }} ({{ optional($item->variation)->sub_sku }})</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>
                                            {!! Form::hidden('itemId[]', $item->id) !!}
                                            {!! Form::text('accepted_qty[]', $item->accepted_qty ?? $item->quantity, ['class' => 'form-control input-sm calculate-qty']) !!}
                                        </td>
                                        <td>{!! Form::text('order_date[]', $item->order_date ?? date('Y-m-d'), ['class' => 'form-control input-sm datepicker']) !!}</td>
                                        <td>{!! Form::text('po_number[]', $item->po_number, ['class' => 'form-control input-sm']) !!}</td>
                                        <td>{!! Form::text('cso_purchasing_req_no[]', $item->cso_purchasing_req_no, ['class' => 'form-control input-sm']) !!}</td>
                                        <td>
                                            {!! Form::text('stock_on_hand_hs[]', $item->stock_on_hand_hs, ['class' => 'form-control input-sm calculate-stock', 'readonly']) !!}
                                        </td>
                                        <td>
                                            {!! Form::text('approved_ipr_qty_hs[]', $item->approved_ipr_qty_hs, ['class' => 'form-control input-sm calculate-stock']) !!}
                                        </td>
                                        <td>
                                            {!! Form::text('in_transit_qty_hs[]', $item->in_transit_qty_hs, ['class' => 'form-control input-sm calculate-stock', 'readonly']) !!}
                                        </td>
                                        <td>
                                            {!! Form::text('committed_qty_hs[]', $item->committed_qty_hs, ['class' => 'form-control input-sm calculate-stock', 'readonly']) !!}
                                        </td>
                                        <td>
                                            <span class="available-qty">{{ number_format($item->available_qty, 2) }}</span>
                                            {!! Form::hidden('available_qty[]', $item->available_qty) !!}
                                        </td>
                                        <td>
                                            {!! Form::text('qty_available_for_invoice[]', $item->qty_available_for_invoice, ['class' => 'form-control input-sm', 'readonly']) !!}
                                        </td>
                                        <td>
                                            {!! Form::text('suggested_qty_to_request[]', $item->suggested_qty_to_request, ['class' => 'form-control input-sm', 'readonly']) !!}
                                        </td>
                                        <td>
                                            {!! Form::text('invoiced_for_this_req[]', $item->invoiced_for_this_req, ['class' => 'form-control input-sm calculate-invoice']) !!}
                                        </td>
                                        <td>
                                            {!! Form::text('pending_invoice[]', $item->pending_invoice, ['class' => 'form-control input-sm', 'readonly']) !!}
                                        </td>
                                        <td>
                                            {!! Form::text('committed_for_this_order[]', $item->committed_for_this_order, ['class' => 'form-control input-sm', 'readonly']) !!}
                                        </td>
                                        <td>
                                            {!! Form::text('received_for_this_order[]', $item->received_for_this_order, ['class' => 'form-control input-sm', 'readonly']) !!}
                                        </td>
                                        <td>
                                            <span class="live-available">{{ number_format($item->live_available_for_this_order, 2) }}</span>
                                            {!! Form::hidden('live_available_for_this_order[]', $item->live_available_for_this_order) !!}
                                        </td>
                                        <td>
                                            {!! Form::text('qty_to_generate_invoice[]', $item->qty_to_generate_invoice, ['class' => 'form-control input-sm']) !!}
                                        </td>
                                        <td>
                                            <span class="status-purchase {{ $item->status_purchase == 'Not Necessary IPR' ? 'label label-success' : 'label label-warning' }}">
                                                {{ $item->status_purchase }}
                                            </span>
                                            {!! Form::hidden('status_purchase[]', $item->status_purchase) !!}
                                        </td>
                                        <td>
                                            <span class="status-invoice 
                                                {{ $item->status_invoice == 'Invoiced' ? 'label label-success' : 
                                                   ($item->status_invoice == 'Partial Invoiced' ? 'label label-warning' : 'label label-default') }}">
                                                {{ $item->status_invoice }}
                                            </span>
                                            {!! Form::hidden('status_invoice[]', $item->status_invoice) !!}
                                        </td>
                                        <td>
                                            {!! Form::text('internal_req_qty[]', $item->internal_req_qty, ['class' => 'form-control input-sm']) !!}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('globale_note', __('request.global_note') . ':') !!}
                                {!! Form::textarea('globale_note', $request->request_note, ['class' => 'form-control', 'rows' => 3]) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">@lang('messages.submit')</button>
                    <a href="{{ route('request.list.quote.accept') }}" class="btn btn-default">@lang('messages.cancel')</a>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
</section>
@stop

@section('javascript')
<script>
    $(document).ready(function() {
        // Initialize datepickers
        $('.datepicker').datepicker({
            autoclose: true,
            format: 'yyyy-mm-dd',
            todayHighlight: true
        });
        
        // Calculate available quantity when stock values change
        $(document).on('change', '.calculate-stock', function() {
            var row = $(this).closest('tr');
            var stockOnHand = parseFloat(row.find('[name="stock_on_hand_hs[]"]').val()) || 0;
            var approvedIpr = parseFloat(row.find('[name="approved_ipr_qty_hs[]"]').val()) || 0;
            var inTransit = parseFloat(row.find('[name="in_transit_qty_hs[]"]').val()) || 0;
            var customerReq = parseFloat(row.find('td:eq(1)').text()) || 0;
            
            var availableQty = stockOnHand + approvedIpr + inTransit - customerReq;
            var acceptedQty = parseFloat(row.find('[name="accepted_qty[]"]').val()) || 0;
            
            // Update available quantity display
            row.find('.available-qty').text(availableQty.toFixed(2));
            row.find('[name="available_qty[]"]').val(availableQty.toFixed(2));
            
            // Update suggested quantity to request
            var suggestedQty = Math.max(0, acceptedQty - availableQty);
            row.find('[name="suggested_qty_to_request[]"]').val(suggestedQty.toFixed(2));
            
            // Update live available
            row.find('.live-available').text(availableQty.toFixed(2));
            row.find('[name="live_available_for_this_order[]"]').val(availableQty.toFixed(2));
            
            // Update status purchase
            if (availableQty >= 0) {
                row.find('.status-purchase').removeClass('label-warning').addClass('label-success')
                    .text('Not Necessary IPR');
                row.find('[name="status_purchase[]"]').val('Not Necessary IPR');
            } else {
                row.find('.status-purchase').removeClass('label-success').addClass('label-warning')
                    .text('Requested');
                row.find('[name="status_purchase[]"]').val('Requested');
            }
        });
        
        // Calculate invoice status when invoiced quantity changes
        $(document).on('change', '.calculate-invoice', function() {
            var row = $(this).closest('tr');
            var invoiced = parseFloat(row.find('[name="invoiced_for_this_req[]"]').val()) || 0;
            var acceptedQty = parseFloat(row.find('[name="accepted_qty[]"]').val()) || 0;
            
            // Update pending invoice
            var pending = Math.max(0, acceptedQty - invoiced);
            row.find('[name="pending_invoice[]"]').val(pending.toFixed(2));
            
            // Update status invoice
            var statusSpan = row.find('.status-invoice');
            if (invoiced >= acceptedQty) {
                statusSpan.removeClass('label-warning label-default').addClass('label-success')
                    .text('Invoiced');
                row.find('[name="status_invoice[]"]').val('Invoiced');
            } else if (invoiced > 0) {
                statusSpan.removeClass('label-success label-default').addClass('label-warning')
                    .text('Partial Invoiced');
                row.find('[name="status_invoice[]"]').val('Partial Invoiced');
            } else {
                statusSpan.removeClass('label-success label-warning').addClass('label-default')
                    .text('None invoiced');
                row.find('[name="status_invoice[]"]').val('None invoiced');
            }
        });
        
        // Initial calculation for all rows
        $('.calculate-stock').trigger('change');
        $('.calculate-invoice').trigger('change');
    });
</script>
@endsection