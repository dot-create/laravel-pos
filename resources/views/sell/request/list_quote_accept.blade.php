@extends('layouts.app')
@section('title', __('purchase.Draft List'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>
        List Accept Quote
    </h1>
</section>

<!-- Main content -->
<section class="content no-print">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('request_list_filter_customer_id',  __('request.customer') . ':') !!}
                {!! Form::select('request_list_filter_customer_id', $suppliers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('purchase_list_filter_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('purchase_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
            </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary', 'title' => __('request.all_requests')])

        <table class="table table-bordered table-striped ajax_view" id="quote_accept_table">
            <thead>
                <tr>
                    <th>@lang('messages.date')</th>
                    <th>@lang('contact.customer')</th>
                    <th>@lang('request.ref_no')</th>
                    <th>@lang('request.status')</th>
                    <th>@lang('request.purchase_status')</th>
                    <th>@lang('request.invoice_status')</th>
                    <th>@lang('messages.actions')</th>
                </tr>
            </thead>
        </table>
    @endcomponent

    <!-- Modals remain the same -->
    <div class="modal fade product_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

    <div class="modal fade payment_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

    <div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

    <div class="modal fade request_inf_report_modal" tabindex="-1" role="dialog" 
        aria-labelledby="requestInfReportModal"></div>


    @include('purchase.partials.update_purchase_status_modal')

</section>

<section id="receipt_section" class="print_section"></section>

@stop
@section('javascript')
<script src="{{ asset('js/purchase.js?v=' . $asset_v) }}"></script>
<script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
<script>
    // Initialize DataTable with new columns
    $(document).ready(function() {
        var quote_accept_table = $('#quote_accept_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/request/quote/accepted', // '{{ action("RequestController@acceptedQuote") }}',
                data: function(d) {
                    if($('#request_list_filter_customer_id').length) {
                        d.customer_id = $('#request_list_filter_customer_id').val();
                    }
                    if($('#purchase_list_filter_date_range').val()) {
                        d.start_date = $('#purchase_list_filter_date_range')
                            .data('daterangepicker')
                            .startDate.format('YYYY-MM-DD');
                        d.end_date = $('#purchase_list_filter_date_range')
                            .data('daterangepicker')
                            .endDate.format('YYYY-MM-DD');
                    }
                }
            },
            columns: [
                { data: 'date', name: 'date' },
                { data: 'contact', name: 'contact' },
                { data: 'ref_no', name: 'ref_no' },
                { data: 'availability_status', name: 'status' },
                { 
                    data: null,
                    render: function(data, type, row) {
                        var status = 'Requested';
                        var class_name = 'label-warning';
                        
                        // Check if all items have "Not Necessary IPR" status
                        if (row.items && row.items.length > 0) {
                            var allNotNecessary = true;
                            for (var i = 0; i < row.items.length; i++) {
                                if (row.items[i].status_purchase !== 'Not Necessary IPR') {
                                    allNotNecessary = false;
                                    break;
                                }
                            }
                            if (allNotNecessary) {
                                status = 'Not Necessary IPR';
                                class_name = 'label-success';
                            }
                        }
                        
                        return '<span class="label ' + class_name + '">' + status + '</span>';
                    },
                    orderable: false,
                    searchable: false
                },
                { 
                    data: null,
                    render: function(data, type, row) {
                        var status = 'None invoiced';
                        var class_name = 'label-default';
                        
                        // Check invoice status across items
                        if (row.items && row.items.length > 0) {
                            var allInvoiced = true;
                            var hasPartial = false;
                            
                            for (var i = 0; i < row.items.length; i++) {
                                if (row.items[i].status_invoice === 'None invoiced') {
                                    allInvoiced = false;
                                } else if (row.items[i].status_invoice === 'Partial Invoiced') {
                                    allInvoiced = false;
                                    hasPartial = true;
                                }
                            }
                            
                            if (allInvoiced) {
                                status = 'Invoiced';
                                class_name = 'label-success';
                            } else if (hasPartial) {
                                status = 'Partial Invoiced';
                                class_name = 'label-warning';
                            }
                        }
                        
                        return '<span class="label ' + class_name + '">' + status + '</span>';
                    },
                    orderable: false,
                    searchable: false
                },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
        
        // Date range filter
        $('#purchase_list_filter_date_range').daterangepicker(
            dateRangeSettings,
            function (start, end) {
                $('#purchase_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                quote_accept_table.ajax.reload();
            }
        );
        
        $('#purchase_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $('#purchase_list_filter_date_range').val('');
            quote_accept_table.ajax.reload();
        });
        
        // Customer filter
        $('#request_list_filter_customer_id').change(function() {
            quote_accept_table.ajax.reload();
        });

        // Handle Invoicing INF Report button
        // $(document).on('click', '.btn-invoicing-inf', function(e) {
        //     e.preventDefault();
        //     $('.request_inf_report_modal').remove();
        //     $('body').append('<div class="modal fade request_inf_report_modal" tabindex="-1" role="dialog"></div>');
        //     var container = '.request_inf_report_modal';
        //     $.ajax({
        //         url: $(this).attr('href'),
        //         dataType: 'html',
        //         success: function(response) {
        //             $(container).html(response).modal('show');
        //         },
        //         error: function(xhr) {
        //             toastr.error(__('messages.something_went_wrong'));
        //         }
        //     });
        // });

        // Handle Purchasing INF Report button
        // $(document).on('click', '.btn-purchasing-inf', function(e) {
        //     e.preventDefault();
        //     $('.request_inf_report_modal').remove();
        //     $('body').append('<div class="modal fade request_inf_report_modal" tabindex="-1" role="dialog"></div>');
        //     var container = '.request_inf_report_modal';
        //     $.ajax({
        //         url: $(this).attr('href'),
        //         dataType: 'html',
        //         success: function(response) {
        //             $(container).html(response).modal('show');
        //             // Initialize the toggles for this modal
        //             $('.status-purchase-select', container).each(function() {
        //                 toggleCsoFields($(this));
        //             });
        //         },
        //         error: function(xhr) {
        //             toastr.error(__('messages.something_went_wrong'));
        //         }
        //     });
        // });

        // Handle form submission for Purchasing INF Report
        $(document).on('submit', '#inf-report-form', function(e) {
            e.preventDefault();
            const form = $(this);
            const formData = form.serialize();
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        form.closest('.modal').modal('hide');
                        // Refresh table if needed
                        quote_accept_table.ajax.reload(null, false);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(__('messages.something_went_wrong'));
                }
            });
        });

    
        // Handle Invoicing INF Report button
        $(document).on('click', '.btn-invoicing-inf', function(e) {
            e.preventDefault();
            $('.request_inf_report_modal').modal('show').load($(this).attr('href'));
        });

        // Handle Purchasing INF Report button
        $(document).on('click', '.btn-purchasing-inf', function(e) {
            e.preventDefault();
            $('.request_inf_report_modal').modal('show').load($(this).attr('href'), function() {
                // Initialize status-based field visibility
                $('.status-purchase-select').each(function() {
                    toggleCsoFields($(this));
                });
            });
        });

        // Handle form submission for Purchasing INF Report
        $(document).on('submit', '#inf-report-form', function(e) {
            e.preventDefault();
            var form = $(this);
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.msg);
                        $('.request_inf_report_modal').modal('hide');
                        quote_accept_table.ajax.reload();
                    } else {
                        toastr.error(response.msg);
                    }
                }
            });
        });

        // Toggle CSO fields based on status
        $(document).on('change', '.status-purchase-select', function() {
            toggleCsoFields($(this));
        });
        

        function toggleCsoFields(select) {
            var row = select.closest('tr');
            var isRequested = select.val() === 'Requested';
            
            row.find('input[name*="cso_new_purchasing_req_no"]')
                .prop('disabled', !isRequested)
                .closest('td').toggle(isRequested);
                
            row.find('input[name*="new_approved_qty_internal_req"]')
                .prop('disabled', !isRequested)
                .closest('td').toggle(isRequested);
        }
    });
</script>
@endsection