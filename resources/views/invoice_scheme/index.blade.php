@extends('layouts.app')
@section('title', __('invoice.invoice_settings'))

@section('content')

<section class="content-header">
    <h1>@lang('invoice.invoice_settings')
        <small>@lang('invoice.manage_your_invoices')</small>
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">@lang('invoice.invoice_schemes')</a></li>
              <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">@lang('invoice.invoice_layouts')</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="tab_1">
                <div class="row">
                    <div class="col-md-12">
                        <h4>@lang('invoice.all_your_invoice_schemes') 
                            <button type="button" class="btn btn-primary btn-modal pull-right" 
                                data-href="{{action('InvoiceSchemeController@create')}}" 
                                data-container=".invoice_modal">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </button>
                        </h4>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="_invoice_table">
                            <thead>
                                <tr>
                                    <th>@lang('invoice.name')</th>
                                    <th>@lang('invoice.status')</th>
                                    <th>@lang('invoice.prefix')</th>
                                    <th>@lang('invoice.start_number')</th>
                                    <th>@lang('invoice.invoice_count')</th>
                                    <th>@lang('invoice.end_number')</th>
                                    <th>@lang('invoice.start_date')</th>
                                    <th>@lang('invoice.expiry_date')</th>
                                    <th>@lang('invoice.invoice_key')</th>
                                    <th>@lang('invoice.total_digits')</th>
                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>@lang('invoice.name')</th>
                                    <th>@lang('invoice.status')</th>
                                    <th>@lang('invoice.prefix')</th>
                                    <th>@lang('invoice.start_number')</th>
                                    <th>@lang('invoice.invoice_count')</th>
                                    <th>@lang('invoice.end_number')</th>
                                    <th>@lang('invoice.start_date')</th>
                                    <th>@lang('invoice.expiry_date')</th>
                                    <th>@lang('invoice.invoice_key')</th>
                                    <th>@lang('invoice.total_digits')</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                        </div>
                    </div>
                </div>
              </div>
              <!-- /.tab-pane -->
              <div class="tab-pane" id="tab_2">
                <div class="row">
                    <div class="col-md-12">
                        <h4>@lang( 'invoice.all_your_invoice_layouts' ) <a class="btn btn-primary pull-right" href="{{action('InvoiceLayoutController@create')}}">
                                <i class="fa fa-plus"></i> @lang( 'messages.add' )</a></h4>
                    </div>
                    <div class="col-md-12">
                        @foreach( $invoice_layouts as $layout)
                        <div class="col-md-3">
                            <div class="icon-link">
                                <a href="{{action('InvoiceLayoutController@edit', [$layout->id])}}">
                                    <i class="fa fa-file-alt fa-4x"></i> 
                                    {{ $layout->name }}
                                </a>
                                @if( $layout->is_default )
                                    <span class="badge bg-green">@lang("barcode.default")</span>
                                @endif
                                @if($layout->locations->count())
                                    <span class="link-des">
                                    <b>@lang('invoice.used_in_locations'): </b><br>
                                    @foreach($layout->locations as $location)
                                        {{ $location->name }}
                                        @if (!$loop->last)
                                            ,
                                        @endif
                                        &nbsp;
                                    @endforeach
                                    </span>
                                @endif
                            </div>
                        </div>
                        @if( $loop->iteration % 4 == 0 )
                                    <div class="clearfix"></div>
                                @endif
                        @endforeach
                    </div>
                </div>
                <br>
              </div>
              <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
          </div>
          <!-- nav-tabs-custom -->
        </div>
    </div>
	
    <div class="modal fade invoice_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade invoice_edit_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection

@section('javascript')
<script>
$(document).ready(function() {
    // Initialize datatable with localized strings
    var invoiceTable = $('#_invoice_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ action('InvoiceSchemeController@index') }}",
        language: {
            url: "{{ asset('js/lang/'.config('app.locale').'.json') }}"
        },
        columns: [
            { data: 'name', name: 'name' },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'prefix', name: 'prefix' },
            { data: 'start_number', name: 'start_number' },
            { data: 'invoice_count', name: 'invoice_count' },
            { data: 'end_number', name: 'end_number' },
            { data: 'start_date', name: 'start_date' },
            { data: 'expiration_date', name: 'expiration_date' },
            { data: 'invoicing_key', name: 'invoice_key' },
            { data: 'total_digits', name: 'total_digits' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        initComplete: function() {
            // Add footer search functionality
            this.api().columns().every(function() {
                var column = this;
                if (column.footer()) {
                    var input = document.createElement("input");
                    input.className = "form-control input-sm";
                    $(input).appendTo($(column.footer()).empty())
                    .on('keyup change', function() {
                        column.search(this.value).draw();
                    });
                }
            });
        }
    });
    
    // Handle form submissions
    $(document).off('submit', 'form#invoice_scheme_add_form');
    $(document).on('submit', 'form#invoice_scheme_add_form, form#invoice_scheme_edit_form', function(e) {
        e.preventDefault();
        var form = $(this);
        $.ajax({
            method: 'POST',
            url: form.attr('action'),
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('.modal').modal('hide');
                    toastr.success(response.msg);
                    invoiceTable.ajax.reload();
                } else {
                    toastr.error(response.msg);
                }
            },
            error: function(xhr) {
                __handleValidationErrors(xhr, form);
            }
        });
    });
    
    // Handle validation errors
    function __handleValidationErrors(xhr, form) {
        var errors = xhr.responseJSON.errors;
        form.find('.help-block').remove();
        form.find('.has-error').removeClass('has-error');
        
        $.each(errors, function(key, value) {
            var element = form.find('[name="' + key + '"]');
            element.closest('.form-group').addClass('has-error');
            element.after('<span class="help-block">' + value + '</span>');
        });
    }
});
</script>
@endsection

