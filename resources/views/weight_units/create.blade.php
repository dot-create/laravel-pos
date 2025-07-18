<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open(['url' => action('WeightUnitController@store'), 'method' => 'post', 'id' => 'weight_unit_add_form' ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">@lang( 'weight_units.add_unit' )</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="form-group col-sm-12">
                    {!! Form::label('code', __( 'weight_units.code' ) . ':*') !!}
                    {!! Form::text('code', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'weight_units.code' )]) !!}
                </div>

                <div class="form-group col-sm-12">
                    {!! Form::label('unit_name', __( 'weight_units.unit_name' ) . ':*') !!}
                    {!! Form::text('unit_name', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'weight_units.unit_name' )]) !!}
                </div>

                <div class="form-group col-sm-12">
                    {!! Form::label('equivalent_to_lb', __( 'weight_units.equivalent_to_lb' ) . ':*') !!}
                    {!! Form::number('equivalent_to_lb', null, ['class' => 'form-control input_number', 'required', 'step' => 'any', 'placeholder' => __( 'weight_units.equivalent_to_lb' )]) !!}
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