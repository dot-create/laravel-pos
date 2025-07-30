<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('PurchaseController@updateReceivedQty', [$purchase->id]), 'method' => 'post', 'id' => "edit-received-form"]) !!}

    <div class="modal-header">
      <h4 class="modal-title">@lang('Edit Received Quantity')</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>

    <div class="modal-body">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>#</th>
            <th>@lang('product.product_name')</th>
            <th>@lang('purchase.purchase_quantity')</th>
            @if(session('business.enable_lot_number'))
              <th>@lang('lang_v1.lot_number')</th>
            @endif
            <th>@lang('purchase.received_quantity')</th>
          </tr>
        </thead>
        <tbody>
          @foreach($purchase->purchase_lines as $line)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $line->product->name }} ({{ $line->variations->sub_sku }})</td>
            <td>{{ @format_quantity($line->quantity) }}</td>
            @if(session('business.enable_lot_number'))
              <td>
                {!! Form::text('lines['.$line->id.'][lot_number]', $line->lot_number, ['class' => 'form-control']) !!}
              </td>
            @endif
            <td>
              {!! Form::number('lines['.$line->id.'][received_quantity]', $line->received_quantity, ['class' => 'form-control', 'step' => 'any']) !!}
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
    </div>

    {!! Form::close() !!}
  </div>
</div>
