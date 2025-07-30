@extends('layouts.app')
@section('title', __('expense.add_expense'))

@section('content')
    @php
        $url = Request::url();
        $expense = false;
        if(str_contains($url, 'expenses')) {
            $expense = true;
        }
    @endphp
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('expense.add_expense')</h1>
    <input type="hidden" name="__symbol" id="__symbol" value="{{ session('currency')['symbol'] ?? '$' }}">
    <input type="hidden" name="__rate" id="__rate" value="{{ session('currency')['rate'] ?? 1 }}">
</section>

<!-- Main content -->
<section class="content">
@php
  $form_class = empty($duplicate_product) ? 'create' : '';
@endphp
    {!! Form::open(['url' => action('ExpenseController@store'), 'method' => 'post', 'id' => 'add_expense_form', 'files' => true ]) !!}
    @if($expense)
        <input type="hidden" name="from" value="expense">
    @else
         <input type="hidden" name="from" value="others">
    @endif
    <div class="box box-solid">
        <div class="box-body">
            <div class="row">

                @if(count($business_locations) == 1)
                    @php 
                        $default_location = current(array_keys($business_locations->toArray())) 
                    @endphp
                @else
                    @php $default_location = null; @endphp
                @endif
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('location_id', __('purchase.business_location').':*') !!}
                        {!! Form::select('location_id', $business_locations, !empty($duplicate_expense) ? $duplicate_expense->location_id : $default_location, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required'], $bl_attributes); !!}
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('expense_category_id', __('expense.expense_category').':') !!}
                        {!! Form::select('expense_category_id', $expense_categories,  !empty($duplicate_expense) ? $duplicate_expense->expense_category_id : null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('expense_sub_category_id', __('product.sub_category') . ':') !!}
                          {!! Form::select('expense_sub_category_id', $sub_categories, !empty($duplicate_expense) ? $duplicate_expense->expense_sub_category_id : null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); !!}
                      </div>
                </div>
                
                <!-- Added: Tax Calculation Fields -->
                <div class="clearfix"></div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('amount_before_tax', __('Value without Tax') . ':*') !!}
                        {!! Form::text('amount_before_tax', !empty($duplicate_expense) ? $duplicate_expense->amount_before_tax : null, ['class' => 'form-control input_number', 'placeholder' => __('Enter amount before tax'), 'required', 'id' => 'amount_before_tax']); !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('tax_id', __('Applicable Tax') . ':' ) !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-info"></i>
                            </span>
                            {!! Form::select('tax_id', $taxes['tax_rates'], !empty($duplicate_expense) ? $duplicate_expense->tax_id : null, ['class' => 'form-control', 'id'=>'tax_id', 'placeholder' => __('messages.please_select')], $taxes['attributes']); !!}
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('tax_type', __('Expense Tax Type') . ':*') !!}
                        {!! Form::select('tax_type', ['percentage' => 'Percentage', 'fixed' => 'Fixed Amount'], !empty($duplicate_expense) ? $duplicate_expense->tax_type : 'percentage', ['class' => 'form-control', 'id' => 'tax_type']); !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('tax_value', __('Expense Tax Value') . ':*') !!}
                        <div class="input-group">
                            {!! Form::text('tax_value', !empty($duplicate_expense) ? $duplicate_expense->tax_value : null, ['class' => 'form-control input_number', 'id' => 'tax_value', 'readonly']); !!}
                            <span class="input-group-addon" id="tax_value_indicator">%</span>
                        </div>
                    </div>
                </div>
                <!-- End Added Fields -->

                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('ref_no', __('purchase.ref_no').':') !!}
                        {!! Form::text('ref_no', !empty($duplicate_expense) ? $duplicate_expense->ref_no : null, ['class' => 'form-control']); !!}
                        <p class="help-block">
                            @lang('lang_v1.leave_empty_to_autogenerate')
                        </p>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('transaction_date', __('messages.date') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            {!! Form::text('transaction_date', !empty($duplicate_expense) ? date('d-m-Y h:i',strtotime($duplicate_expense->transaction_date)) : @format_datetime('now'), ['class' => 'form-control', 'readonly', 'required', 'id' => 'expense_transaction_date']); !!}
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('expense_for', __('expense.expense_for').':') !!} @show_tooltip(__('tooltip.expense_for'))
                        {!! Form::select('expense_for', $users,  !empty($duplicate_expense) ? $duplicate_expense->expense_for :null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('contact_id', __('lang_v1.expense_for_contact').':') !!} 
                        {!! Form::select('contact_id', $contacts,!empty($duplicate_expense) ? $duplicate_expense->contact_id : null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('document', __('purchase.attach_document') . ':') !!}
                        {!! Form::file('document', ['id' => 'upload_document', 'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types')))]); !!}
                        <small><p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)])
                        @includeIf('components.document_help_text')</p></small>
                    </div>
                </div>
                
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('final_total', __('Total Amount') . ':*') !!}
                        {!! Form::text('final_total',!empty($duplicate_expense) ? $duplicate_expense->final_total :  null, ['class' => 'form-control input_number', 'placeholder' => __('Total amount will be calculated'), 'readonly','id'=>'final_total']); !!}
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('additional_notes', __('expense.expense_note') . ':') !!}
                                {!! Form::textarea('additional_notes',!empty($duplicate_expense) ? $duplicate_expense->additional_notes :  null, ['class' => 'form-control', 'rows' => 3]); !!}
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <br>
                    <label>
                      {!! Form::checkbox('is_refund', 1, !empty($duplicate_expense) ? $duplicate_expense->is_refund :false, ['class' => 'input-icheck', 'id' => 'is_refund']); !!} @lang('lang_v1.is_refund')?
                    </label>@show_tooltip(__('lang_v1.is_refund_help'))
                </div>
                <div class="col-md-4 col-sm-6">
                    <br>
                    <label>
                      {!! Form::checkbox('is_purchase', 1, !empty($duplicate_expense) ? $duplicate_expense->is_purchase :false, ['class' => 'input-icheck', 'id' => 'is_purchase']); !!} @lang('Is Purchase')?
                    </label>@show_tooltip(__('Is Purchase Expense'))
                </div>
            </div>
        </div>
    </div> <!--box end-->
    <div class="box box-primary hide" id="purchase_box">
                
        <div class="box-body">
            <div class="row">
                <div class="col-sm-2 text-center">
                    {{-- <button type="button" class="btn btn-primary btn-flat" data-toggle="modal" data-target="#import_purchase_products_modal">Import Products</button> --}}
                </div>
                <div class="col-sm-8">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-search"></i>
                            </span>
                                {!! Form::text('search_purchase', null, ['class' => 'form-control mousetrap', 'id' => 'search_purchase', 'placeholder' => __('Search Purchase Ref')]); !!}
                        </div>
                    </div>
                </div>
                <div class="col-sm-2">
                    {{-- <div class="form-group">
                        <button tabindex="-1" type="button" class="btn btn-link btn-modal" data-href="https://webpos.uni-linkos.com/products/quick_add" data-container=".quick_add_product_modal"><i class="fa fa-plus"></i> Add new product </button>
                    </div> --}}
                </div>
            </div>
                    <div class="row">
                <div class="col-sm-12">
                    <div class="table-responsive">
                        <table class="table table-condensed table-bordered table-th-green text-center table-striped" id="purchase_entry_table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Supplier</th>
                                    <th>Purchase Ref</th>
                                    <th>Puchase Expense (W/O Tax)</th>
                                    <th>Puchase Expense (Inc Tax)</th>
                                    <th>Main Currency Total</th>
                                    <th><i class="fa fa-trash" aria-hidden="true"></i></th>
                                </tr>
                            </thead>
                            <tbody id="purchase_rows"></tbody>
                        </table>
                    </div>
                    <hr>
                    <div class="pull-right col-md-5">
                        <table class="pull-right col-md-12">
                            <tbody><tr>
                                <th class="col-md-7 text-right">Total Purchases:</th>
                                <td class="col-md-5 text-left">
                                    <span id="total_quantity" >0.0000</span>
                                </td>
                            </tr>
                            {{-- <tr class="hide">
                                <th class="col-md-7 text-right">Total Before Tax:</th>
                                <td class="col-md-5 text-left">
                                    <span id="total_st_before_tax" class="display_currency">$ 0.0000</span>
                                    <input type="hidden" id="st_before_tax_input" value="0.0000">
                                </td>
                            </tr> --}}
                            <tr>
                                <th class="col-md-7 text-right">Net Total Amount:</th>
                                <td class="col-md-5 text-left">
                                    <span id="total_subtotal" class="display_currency">$ 0.0000</span>
                                    <!-- This is total before purchase tax-->
                                </td>
                            </tr>
                        </tbody></table>
                    </div>
    
                    <input type="hidden" id="row_count" value="0">
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>

    @include('expense.recur_expense_form_part')
    @component('components.widget', ['class' => 'box-solid', 'id' => "payment_rows_div", 'title' => __('purchase.add_payment')])
    <div class="payment_row">
        @include('sale_pos.partials.payment_row_form', ['row_index' => 0, 'show_date' => true])
        <hr>
        <div class="row">
            <div class="col-sm-12">
                <div class="pull-right">
                    <strong>@lang('purchase.payment_due'):</strong>
                    <span id="payment_due">{{@num_format(!empty($duplicate_expense) ? $duplicate_expense->final_total :0)}}</span>
                </div>
            </div>
        </div>
    </div>
    @endcomponent
    <div class="col-sm-12 text-center">
        <button type="submit" class="btn btn-primary btn-big">@lang('messages.save')</button>
    </div>
{!! Form::close() !!}
</section>
@endsection
@section('javascript')
<script src="{{ asset('js/expense.js?v=' . $asset_v) }}"></script>

<script type="text/javascript">
    // Enhanced tax calculation system
    function calculateEnhancedTax() {
        var amountBeforeTax = parseFloat($('#amount_before_tax').val()) || 0;
        var taxType = $('#tax_type').val();
        var taxRateElement = $('#tax_id');
        var taxRate = parseFloat(taxRateElement.find('option:selected').data('rate')) || 0;
        var manualTaxValue = parseFloat($('#tax_value').val()) || 0;

        var taxValue = 0;
        var finalTotal = amountBeforeTax;

        if (amountBeforeTax > 0) {
            if (taxType === 'percentage' && taxRate > 0) {
                // Calculate tax based on selected tax rate percentage
                taxValue = (amountBeforeTax * taxRate) / 100;
                $('#tax_value').val(taxValue.toFixed(2));
                finalTotal = amountBeforeTax + taxValue;
                $('#tax_value_indicator').text($('#__symbol').val() || '$');
            } else if (taxType === 'fixed') {
                // Use manually entered fixed tax amount
                taxValue = manualTaxValue;
                finalTotal = amountBeforeTax + taxValue;
                $('#tax_value_indicator').text($('#__symbol').val() || '$');
            }
        }

        $('#final_total').val(finalTotal.toFixed(2));
        updatePaymentDue();
    }

    // Tax type change handler
    $(document).on('change', '#tax_type', function() {
        var taxType = $(this).val();
        var taxValueField = $('#tax_value');
        var indicator = $('#tax_value_indicator');

        if (taxType === 'percentage') {
            taxValueField.prop('readonly', true);
            indicator.text('%');
        } else {
            taxValueField.prop('readonly', false);
            indicator.text($('#__symbol').val() || '$');
            taxValueField.focus();
        }
        calculateEnhancedTax();
    });

    // Amount before tax change
    $(document).on('input', '#amount_before_tax', function() {
        calculateEnhancedTax();
    });

    // Tax selection change
    $(document).on('change', '#tax_id', function() {
        calculateEnhancedTax();
    });

    // Manual tax value input (for fixed type)
    $(document).on('input', '#tax_value', function() {
        if ($('#tax_type').val() === 'fixed') {
            calculateEnhancedTax();
        }
    });

    // Enhanced payment due calculation
    function updatePaymentDue() {
        var finalTotal = parseFloat($('#final_total').val()) || 0;
        var paymentAmount = parseFloat($('.payment-amount').val()) || 0;
        var paymentDue = finalTotal - paymentAmount;
        var symbol = $('#__symbol').val() || '$';

        $('#payment_due').text(symbol + ' ' + paymentDue.toFixed(2));

        // Update currency display if function exists
        if (typeof __currency_convert_recursively === 'function') {
            __currency_convert_recursively($('#payment_due').parent());
        }
    }

    // Payment amount change handler
    $(document).on('input change', '.payment-amount', function() {
        updatePaymentDue();
    });

    // Enhanced currency handling on location change
    $(document).on('change', '#location_id', function() {
        var locationId = $(this).val();
        if (locationId) {
            $.ajax({
                url: '/get-currency/' + locationId,
                type: 'GET',
                success: function(response) {
					if (response.success) {
						// Update currency symbols and rates
                        $('#__symbol').val(response.currency.symbol);
                        $('#__code').val(response.currency.code);
                        $('#__rate').val(response.currency.rate);
                        $('#__thousand').val(response.currency.thousand_separator);
                        $('#__decimal').val(response.currency.decimal_separator);
                        $('#__precision').val(response.currency.decimal_precision);
						
                        // Update account dropdown
                        if (response.accounts) {
                            var accountSelect = $('#account_0');
                            accountSelect.empty();
                            $.each(response.accounts, function(key, value) {
                                accountSelect.append('<option value="' + key + '">' + value + '</option>');
                            });
                        }
						
                        // Recalculate with new currency
                        calculateEnhancedTax();
						
                        // Update display currency
                        $('.display_currency').each(function() {
                            $(this).data('currency', response.currency.symbol);
                        });

                        // Show currency change notification
                        if (typeof toastr !== 'undefined') {
                            toastr.info('Currency updated to ' + response.currency.code);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Currency update failed:', error);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to update currency');
                    }
                }
            });
        }
    });

    // Purchase expense handling
    $(document).on('ifChecked', '#is_purchase', function() {
        $('#purchase_box').removeClass('hide');
        initializePurchaseSearch();
    });

    $(document).on('ifUnchecked', '#is_purchase', function() {
        $('#purchase_box').addClass('hide');
        clearPurchaseData();
    });

    // Enhanced purchase search
    function initializePurchaseSearch() {
        $('#search_purchase').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: '/expenses/get_purchases',
                    dataType: 'json',
                    data: {
                        term: request.term,
                        location_id: $('#location_id').val()
                    },
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                label: item.text + ' - ' + item.contact.name,
                                value: item.text,
                                id: item.id,
                                contact: item.contact,
                                total_before_tax: item.total_before_tax,
                                final_total: item.final_total
                            };
                        }));
                    }
                });
            },
            select: function(event, ui) {
                addPurchaseRow(ui.item);
                $(this).val('');
                return false;
            },
            minLength: 2
        });
    }

    // Add purchase row to table
    function addPurchaseRow(purchase) {
        var rowCount = parseInt($('#row_count').val()) + 1;
        var currency = $('#__symbol').val() || '$';

        var row = '<tr>' +
            '<td class="sr_number">' + rowCount + '</td>' +
            '<td><input type="hidden" name="purchases[]" value="' + purchase.id + '">' + purchase.contact.name + '</td>' +
            '<td>' + purchase.value + '</td>' +
            '<td>' + currency + ' <input type="number" step="0.01" name="sub_total[]" class="sub_total" value="' + purchase.total_before_tax + '"></td>' +
            '<td class="tax_total" data-tax-total="' + purchase.final_total + '">' + currency + ' ' + purchase.final_total + '</td>' +
            '<td>' + currency + ' ' + purchase.final_total + '</td>' +
            '<td><button type="button" class="btn btn-danger btn-sm remove_purchase_entry_row"><i class="fas fa-trash"></i></button></td>' +
            '</tr>';

        $('#purchase_rows').append(row);
        $('#row_count').val(rowCount);
        updatePurchaseTotals();
    }

    // Remove purchase row
    $(document).on('click', '.remove_purchase_entry_row', function() {
        $(this).closest('tr').remove();
        updatePurchaseTotals();
        updateRowNumbers();
    });

    // Update purchase totals
    function updatePurchaseTotals() {
        var totalQuantity = $('#purchase_rows tr').length;
        var totalAmount = 0;

        $('.sub_total').each(function() {
            totalAmount += parseFloat($(this).val()) || 0;
        });

        $('#total_quantity').text(totalQuantity);
        $('#total_subtotal').text($('#__symbol').val() + ' ' + totalAmount.toFixed(2));

        // Update main expense total if this is a purchase expense
        if ($('#is_purchase').is(':checked')) {
            $('#final_total').val(totalAmount.toFixed(2));
            updatePaymentDue();
        }
    }

    // Update row numbers after deletion
    function updateRowNumbers() {
        $('#purchase_rows tr').each(function(index) {
            $(this).find('.sr_number').text(index + 1);
        });
        $('#row_count').val($('#purchase_rows tr').length);
    }

    // Clear purchase data
    function clearPurchaseData() {
        $('#purchase_rows').empty();
        $('#row_count').val(0);
        $('#total_quantity').text('0');
        $('#total_subtotal').text($('#__symbol').val() + ' 0.00');
    }

    // Form validation enhancement
    $(document).on('submit', '#add_expense_form', function(e) {
        var amountBeforeTax = parseFloat($('#amount_before_tax').val()) || 0;
        var finalTotal = parseFloat($('#final_total').val()) || 0;

        if (amountBeforeTax <= 0) {
            e.preventDefault();
            if (typeof toastr !== 'undefined') {
                toastr.error('Please enter a valid amount before tax');
            } else {
                alert('Please enter a valid amount before tax');
            }
            $('#amount_before_tax').focus();
            return false;
        }

        if (finalTotal <= 0) {
            e.preventDefault();
            if (typeof toastr !== 'undefined') {
                toastr.error('Total amount must be greater than zero');
            } else {
                alert('Total amount must be greater than zero');
            }
            return false;
        }
    });

    // Initialize on page load
    $(document).ready(function() {
        // Set initial currency symbol
        var initialSymbol = "{{ session('currency')['symbol'] ?? '$' }}";
        $('#__symbol').val(initialSymbol);
        
        // Initialize tax type
        var initialTaxType = $('#tax_type').val();
        if (initialTaxType === 'fixed') {
            $('#tax_value').prop('readonly', false);
            $('#tax_value_indicator').text(initialSymbol);
        }

        // Auto-calculate if values exist
        if ($('#amount_before_tax').val()) {
            calculateEnhancedTax();
        }

        // Enhanced duplicate expense handling
        if (window.location.search.includes('d=')) {
            setTimeout(function() {
                // Update currency for duplicated expense
                $('#location_id').trigger('change');
                // Reset dates to current
                var currentDate = moment().format(moment_date_format + ' ' + moment_time_format);
                $('#expense_transaction_date').val(currentDate);
            }, 1000);
        }

        // Initialize datetime picker
        $('#expense_transaction_date').datetimepicker({
            format: moment_date_format + ' ' + moment_time_format,
            ignoreReadonly: true,
            defaultDate: new Date()
        });
        
        // Page leave confirmation
        __page_leave_confirmation('#add_expense_form');
    });

    // Helper function for currency formatting
    function formatCurrency(amount, symbol) {
        symbol = symbol || $('#__symbol').val() || '$';
        return symbol + ' ' + parseFloat(amount).toFixed(2);
    }

    // Helper function for number parsing
    function parseAmount(value) {
        if (typeof value === 'string') {
            return parseFloat(value.replace(/[^0-9.-]/g, '')) || 0;
        }
        return parseFloat(value) || 0;
    }
</script>
@endsection