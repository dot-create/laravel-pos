<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action('ShippingWayController@store'), 'method' => 'post', 'id' => 'shipping_way_add_form' ]) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            <h4 class="modal-title">@lang( 'shipping_ways.add_shipping_way' )</h4>
        </div>

        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('code', __( 'shipping_ways.code' ) . ':*') !!}
                {!! Form::text('code', null, ['class' => 'form-control', 'required']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('shipping_method', __( 'shipping_ways.shipping_method' ) . ':*') !!}
                {!! Form::select('shipping_method_select', [
                '' => __('messages.please_select'),
                'FedEx' => 'FedEx',
                'UPS' => 'UPS',
                'DHL' => 'DHL',
                'Aramex' => 'Aramex',
                'Other' => __('messages.other')
                ], null, ['class' => 'form-control', 'id' => 'shipping_method_select', 'required']) !!}

                <div id="custom_shipping_method_container" class="mt-2" style="display: none;">
                    {!! Form::text('shipping_method', null, ['class' => 'form-control', 'placeholder' => __('shipping_ways.enter_custom_method')]) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('freight_rate', __( 'shipping_ways.freight_rate' ) . ':*') !!}
                {!! Form::number('freight_rate', null, ['class' => 'form-control input_number', 'required', 'step' => 'any']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('type', __( 'shipping_ways.type' ) . ':*') !!}
                {!! Form::select('type_select', [
                '' => __('messages.please_select'),
                'Air' => 'Air',
                'Sea' => 'Sea',
                'Land' => 'Land',
                'Express' => 'Express',
                'Other' => __('messages.other')
                ], null, ['class' => 'form-control', 'id' => 'type_select', 'required']) !!}

                <div id="custom_type_container" class="mt-2" style="display: none;">
                    {!! Form::text('type', null, ['class' => 'form-control mt-2', 'placeholder' => __('shipping_ways.enter_custom_type')]) !!}
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>