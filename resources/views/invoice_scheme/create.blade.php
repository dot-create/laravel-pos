<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('InvoiceSchemeController@store'), 'method' => 'post', 'id' => 'invoice_scheme_add_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'invoice.add_invoice' )</h4>
    </div>

    <div class="modal-body">
      <div class="row">
        <div class="option-div-group">
          <div class="col-sm-4">
            <div class="form-group">
              <div class="option-div">
                <h4>FORMAT: <br>XXXX <i class="fa fa-check-circle pull-right icon"></i></h4>
                {!! Form::radio('scheme_type', 'blank'); !!}
              </div>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              <div class="option-div">
                <h4>FORMAT: <br>{{ date('Y') }}{{config('constants.invoice_scheme_separator')}}XXXX <i class="fa fa-check-circle pull-right icon"></i></h4>
                {!! Form::radio('scheme_type', 'year'); !!}
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-sm-4">
          <div class="form-group">
            <label>@lang('invoice.preview'):</label>
            <div id="preview_format">@lang('invoice.not_selected')</div>
          </div>
        </div>
        
        <div class="col-sm-12">
          <div class="form-group">
            {!! Form::label('name', __( 'invoice.name' ) . ':*') !!}
            {!! Form::text('name', null, [
                'class' => 'form-control', 
                'required', 
                'placeholder' => __( 'invoice.name' )
            ]); !!}
          </div>
        </div>
        
        <!-- Status -->
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('status', __( 'invoice.status' ) . ':*') !!}
            {!! Form::select('status', [
                'active' => __('invoice.active'), 
                'inactive' => __('invoice.inactive')
            ], null, [
                'class' => 'form-control', 
                'required'
            ]); !!}
          </div>
        </div>
        
        <!-- Max Invoice Count -->
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('invoice_count', __( 'invoice.invoice_count' ) . ':*') !!}
            {!! Form::number('invoice_count', null, [
                'class' => 'form-control', 
                'required', 
                'min' => 1, 
                'id' => 'invoice_count'
            ]); !!}
          </div>
        </div>
        
        <!-- Start Number -->
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('start_number', __( 'invoice.start_number' ) . ':*') !!}
            {!! Form::number('start_number', null, [
                'class' => 'form-control', 
                'required', 
                'min' => 0, 
                'id' => 'start_number'
            ]); !!}
          </div>
        </div>
        
        <!-- End Number -->
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('end_number', __( 'invoice.end_number' ) . ':') !!}
            <div class="input-group">
              {!! Form::number('end_number', null, [
                  'class' => 'form-control', 
                  'readonly' => true, 
                  'id' => 'end_number'
              ]); !!}
              <span class="input-group-addon" title="@lang('invoice.calculate_end')">
                <i class="fa fa-info-circle"></i>
              </span>
            </div>
          </div>
        </div>
        
        <!-- Start Date -->
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('start_date', __( 'invoice.start_date' ) . ':') !!}
            {!! Form::date('start_date', null, [
                'class' => 'form-control datepicker'
            ]); !!}
          </div>
        </div>
        
        <!-- Expiry Date -->
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('expiration_date', __( 'invoice.expiry_date' ) . ':') !!}
            {!! Form::date('expiration_date', null, [
                'class' => 'form-control datepicker'
            ]); !!}
          </div>
        </div>
        
        <!-- Invoice Key -->
        <div class="col-sm-12">
          <div class="form-group">
            {!! Form::label('invoicing_key', __( 'invoice.invoice_key' ) . ':') !!}
            {!! Form::text('invoicing_key', null, [
                'class' => 'form-control', 
                'placeholder' => __('invoice.invoice_key_placeholder')
            ]); !!}
          </div>
        </div>
        
        <!-- Existing fields (prefix, total_digits, etc) -->
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('prefix', __( 'invoice.prefix' ) . ':') !!}
            {!! Form::text('prefix', null, [
                'class' => 'form-control', 
                'placeholder' => __( 'invoice.prefix' )
            ]); !!}
          </div>
        </div>
        
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('total_digits', __( 'invoice.total_digits' ) . ':') !!}
            {!! Form::select('total_digits', [
                '4' => '4', '5' => '5', '6' => '6', '7' => '7', 
                '8' => '8', '9'=>'9', '10' => '10'
            ], null, [
                'class' => 'form-control', 
                'required'
            ]); !!}
          </div>
        </div>

        <div class="col-sm-6">
          <div class="form-group">
            <br>
            <div class="checkbox">
              <label>
                {!! Form::checkbox('is_default', 1); !!} @lang('barcode.set_as_default')</label>
            </div>
          </div>
        </div>
        
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
    </div>

    {!! Form::close() !!}
  </div>
</div>


<script>
$(document).ready(function() {
    // Calculate end number
    function calculateEndNumber() {
        const start = parseInt($('#start_number').val()) || 0;
        const maxCount = parseInt($('#invoice_count').val()) || 0;
        $('#end_number').val(start + maxCount - 1);
    }

    $('#start_number, #invoice_count').on('input', calculateEndNumber);
    calculateEndNumber();
    
    // Preview update
    $('.scheme-type-radio').change(function() {
        if ($(this).val() === 'year') {
            $('#preview_format').text("{{ date('Y') }}{{ config('constants.invoice_scheme_separator') }}XXXX");
        } else {
            $('#preview_format').text("XXXX");
        }
    }).trigger('change');
});
</script>
