<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action('ProductRackController@update', [$rack->id]), 'method' => 'PUT', 'id' => 'product_rack_form']) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            <h4 class="modal-title">@lang('product_racks.edit_rack')</h4>
        </div>
        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('location_id', __('product_racks.business_location') . ':*') !!}
                {!! Form::select('location_id', $locations, $rack->location_id, [
                    'class' => 'form-control', 
                    'required',
                    'id' => 'location_id'
                ]) !!}
            </div>
            <div class="form-group">
                {!! Form::label('product_id', __('product_racks.product') . ':*') !!}
                {!! Form::select('product_id', $products, $rack->product_id, ['class' => 'form-control', 'required']) !!}
            </div>
            <div class="form-group">
                {!! Form::label('storage_location_id', __('product_racks.location') . ':*') !!}
                {!! Form::select('storage_location_id', $storageLocations, $rack->storage_location_id, [
                    'class' => 'form-control', 
                    'required',
                    'placeholder' => __('messages.please_select')
                ]) !!}
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>