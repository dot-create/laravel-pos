<div class="modal-dialog" role="document">
  <div class="modal-content">
    {!! Form::open(['url' => action('InvoiceSchemeController@update', [$invoice->id]), 'method' => 'put', 'id' => 'invoice_scheme_add_form']) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
      <h4 class="modal-title">@lang('invoice.edit_invoice')</h4>
    </div>

    <div class="modal-body">
      <div class="row">
        <div class="col-sm-12">
          <div class="option-div-group">
            <div class="col-sm-6">
              <div class="form-group">
                <div class="option-div {{ $invoice->scheme_type == 'blank' ? 'active' : '' }}">
                  <h4>FORMAT: <br>XXXX <i class="fa fa-check-circle pull-right icon"></i></h4>
                  {!! Form::radio('scheme_type', 'blank', $invoice->scheme_type == 'blank', ['class' => 'scheme-type-radio', 'id' => 'scheme_blank']) !!}
                  <label for="scheme_blank">Blank Format</label>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <div class="option-div {{ $invoice->scheme_type == 'year' ? 'active' : '' }}">
                  <h4>FORMAT: <br>{{ date('Y') }}{{ config('constants.invoice_scheme_separator') }}XXXX <i class="fa fa-check-circle pull-right icon"></i></h4>
                  {!! Form::radio('scheme_type', 'year', $invoice->scheme_type == 'year', ['class' => 'scheme-type-radio', 'id' => 'scheme_year']) !!}
                  <label for="scheme_year">Year Format</label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-sm-12">
          <div class="form-group">
            <label>@lang('invoice.preview'):</label>
            <div id="preview_format" class="alert alert-info">@lang('invoice.not_selected')</div>
          </div>
        </div>

        <div class="col-sm-12">
          <div class="form-group">
            {!! Form::label('name', __('invoice.name') . ':*') !!}
            {!! Form::text('name', $invoice->name, ['class' => 'form-control', 'required', 'placeholder' => __('invoice.name')]) !!}
          </div>
        </div>

        <!-- Status -->
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('status', __('invoice.status') . ':*') !!}
            {!! Form::select('status', ['active' => __('invoice.active'), 'inactive' => __('invoice.inactive')], $invoice->status, ['class' => 'form-control', 'required']) !!}
          </div>
        </div>

        <!-- Default Checkbox -->
        <div class="col-sm-6">
          <div class="form-group">
            <br>
            <div class="checkbox">
              <label>
                {!! Form::checkbox('is_default', 1, $invoice->is_default); !!} @lang('barcode.set_as_default')
              </label>
            </div>
          </div>
        </div>

        <!-- Prefix -->
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('prefix', __('invoice.prefix') . ':') !!}
            {!! Form::text('prefix', $invoice->prefix, ['class' => 'form-control', 'placeholder' => __('invoice.prefix')]) !!}
          </div>
        </div>

        <!-- Total Digits -->
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('total_digits', __('invoice.total_digits') . ':*') !!}
            {!! Form::select('total_digits', ['4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9','10'=>'10'], $invoice->total_digits, ['class' => 'form-control', 'required']) !!}
          </div>
        </div>

        <!-- Start Number -->
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('start_number', __('invoice.start_number') . ':*') !!}
            {!! Form::number('start_number', $invoice->start_number, ['class' => 'form-control', 'required', 'min' => 0, 'id' => 'start_number']) !!}
          </div>
        </div>

        <!-- Invoice Count -->
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('invoice_count', __('invoice.invoice_count') . ':*') !!}
            {!! Form::number('invoice_count', $invoice->invoice_count, ['class' => 'form-control', 'required', 'min' => 1, 'id' => 'invoice_count']) !!}
            <small class="help-block">Maximum number of invoices allowed under this scheme</small>
          </div>
        </div>

        <!-- End Number -->
        <div class="col-sm-12">
          <div class="form-group">
            {!! Form::label('end_number', __('invoice.end_number') . ':') !!}
            <div class="input-group">
              {!! Form::number('end_number', $invoice->end_number, ['class' => 'form-control', 'readonly' => true, 'id' => 'end_number']) !!}
              <span class="input-group-addon" title="@lang('invoice.calculate_end')">
                <i class="fa fa-info-circle"></i>
              </span>
            </div>
            <small class="help-block">Automatically calculated: Start Number + Invoice Count - 1</small>
          </div>
        </div>

        <!-- Start Date -->
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('start_date', __('invoice.start_date') . ':') !!}
            {!! Form::date('start_date', $invoice->start_date, ['class' => 'form-control datepicker']) !!}
            <small class="help-block">Invoice dates cannot be before this date</small>
          </div>
        </div>

        <!-- Expiration Date -->
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('expiration_date', __('invoice.expiry_date') . ':') !!}
            {!! Form::date('expiration_date', $invoice->expiration_date, ['class' => 'form-control datepicker']) !!}
            <small class="help-block">Invoice dates cannot be after this date</small>
          </div>
        </div>

        <!-- Invoicing Key -->
        <div class="col-sm-12">
          <div class="form-group">
            {!! Form::label('invoicing_key', __('invoice.invoice_key') . ' (CAI):') !!}
            {!! Form::text('invoicing_key', $invoice->invoicing_key, ['class' => 'form-control', 'placeholder' => __('invoice.invoice_key_placeholder'), 'maxlength' => 255]) !!}
            <small class="help-block">Alphanumeric characters and symbols allowed</small>
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
    function calculateEndNumber() {
        const start = parseInt($('#start_number').val()) || 0;
        const count = parseInt($('#invoice_count').val()) || 0;
        if (start >= 0 && count > 0) {
            $('#end_number').val(start + count - 1);
        } else {
            $('#end_number').val('');
        }
        updatePreview();
    }

    function updatePreview() {
        const schemeType = $('input[name="scheme_type"]:checked').val();
        const prefix = $('#prefix').val() || '';
        const totalDigits = $('#total_digits').val() || 4;
        const startNumber = $('#start_number').val() || '0';
        let preview = '';

        if (schemeType === 'year') {
            const year = new Date().getFullYear();
            const separator = '{{ config("constants.invoice_scheme_separator", "-") }}';
            preview = prefix + year + separator + startNumber.padStart(totalDigits, '0');
        } else if (schemeType === 'blank') {
            preview = prefix + startNumber.padStart(totalDigits, '0');
        } else {
            preview = '@lang("invoice.not_selected")';
        }

        $('#preview_format').text(preview);
    }

    $('#start_number, #invoice_count').on('input', calculateEndNumber);
    $('.scheme-type-radio').on('change', updatePreview);
    $('#prefix, #total_digits, #start_number').on('input change', updatePreview);

    calculateEndNumber();
    updatePreview();

    $('#invoice_scheme_add_form').on('submit', function(e) {
        const startDate = new Date($('#start_date').val());
        const endDate = new Date($('#expiration_date').val());

        if ($('#start_date').val() && $('#expiration_date').val() && startDate >= endDate) {
            e.preventDefault();
            alert('Expiration date must be after start date');
            return false;
        }

        if (!$('input[name="scheme_type"]:checked').val()) {
            e.preventDefault();
            alert('Please select a scheme type');
            return false;
        }
    });

    if ($.fn.datepicker) {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });
    }
});
</script>
