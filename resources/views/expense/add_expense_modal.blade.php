<div class="modal fade" id="expense_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width: 800px; width: 100%;">
        <div class="modal-content">
            {!! Form::open(['url' => action('ExpenseController@store'), 'method' => 'post', 'id' => 'add_expense_modal_form', 'files' => true ]) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang( 'expense.add_expense' )</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    @if(count($business_locations) == 1)
                    @php
                    $default_location = current(array_keys($business_locations->toArray()))
                    @endphp
                    @else
                    @php $default_location = request()->input('location_id'); @endphp
                    @endif
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::label('expense_location_id', __('purchase.business_location').':*') !!}
                            {!! Form::select('location_id', $business_locations, $default_location, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required', 'id' => 'expense_location_id'], $bl_attributes); !!}
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::label('expense_category_id', __('expense.expense_category').':') !!}
                            {!! Form::select('expense_category_id', $expense_categories, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'id' => 'expense_category_id']); !!}
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::label('expense_sub_category_id', __('product.sub_category') . ':') !!}
                            {!! Form::select('expense_sub_category_id', $sub_categories, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'id' => 'expense_sub_category_id']); !!}
                        </div>
                    </div>
                    <div class="clearfix"></div>

                    {{-- Enhanced Tax Calculation Section --}}
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::label('expense_amount_before_tax', __('Value without Tax') . ':*') !!}
                            {!! Form::text('amount_before_tax', null, ['class' => 'form-control input_number', 'placeholder' => __('Enter amount before tax'), 'required', 'id' => 'expense_amount_before_tax']); !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('expense_tax_id', __('Applicable Tax') . ':' ) !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-info"></i>
                                </span>
                                {!! Form::select('tax_id', $taxes['tax_rates'], null, ['class' => 'form-control', 'id'=>'expense_tax_id', 'placeholder' => __('messages.please_select')], $taxes['attributes']); !!}
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::label('expense_tax_type', __('Expense Tax Type') . ':*') !!}
                            {!! Form::select('tax_type', ['percentage' => 'Percentage', 'fixed' => 'Fixed Amount'], 'percentage', ['class' => 'form-control', 'id' => 'expense_tax_type']); !!}
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::label('expense_tax_value', __('Expense Tax Value') . ':*') !!}
                            <div class="input-group">
                                {!! Form::text('tax_value', null, ['class' => 'form-control input_number', 'id' => 'expense_tax_value', 'readonly']); !!}
                                <span class="input-group-addon" id="tax_value_indicator">%</span>
                            </div>
                        </div>
                    </div>

                    <div class="clearfix"></div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::label('expense_ref_no', __('purchase.ref_no').':') !!}
                            {!! Form::text('ref_no', null, ['class' => 'form-control', 'id' => 'expense_ref_no']); !!}
                            <p class="help-block">
                                @lang('lang_v1.leave_empty_to_autogenerate')
                            </p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::label('expense_transaction_date', __('messages.date') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                                {!! Form::text('transaction_date', @format_datetime('now'), ['class' => 'form-control', 'readonly', 'required', 'id' => 'expense_transaction_date']); !!}
                            </div>
                        </div>
                    </div>

                    <div class="clearfix"></div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::label('expense_for', __('expense.expense_for').':') !!} @show_tooltip(__('tooltip.expense_for'))
                            {!! Form::select('expense_for', $users, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'id' => 'expense_for']); !!}
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::label('contact_id', __('lang_v1.expense_for_contact').':') !!}
                            {!! Form::select('contact_id', $contacts, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'id' => 'contact_id']); !!}
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::label('expense_final_total', __('Total Amount') . ':*') !!}
                            {!! Form::text('final_total', null, ['class' => 'form-control input_number', 'placeholder' => __('Total amount will be calculated'), 'readonly', 'id' => 'expense_final_total']); !!}
                        </div>
                    </div>

                    <div class="clearfix"></div>
                    <div class="col-sm-12">
                        <div class="form-group">
                            {!! Form::label('expense_additional_notes', __('expense.expense_note') . ':') !!}
                            {!! Form::textarea('additional_notes', null, ['class' => 'form-control', 'rows' => 3, 'id' => 'expense_additional_notes']); !!}
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <br>
                        <label>
                          {!! Form::checkbox('is_refund', 1, false, ['class' => 'input-icheck', 'id' => 'is_refund']); !!} @lang('lang_v1.is_refund')?
                        </label>@show_tooltip(__('lang_v1.is_refund_help'))
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <br>
                        <label>
                          {!! Form::checkbox('is_purchase', 1, false, ['class' => 'input-icheck', 'id' => 'is_purchase']); !!} @lang('Is Purchase')?
                        </label>@show_tooltip(__('Is Purchase Expense'))
                    </div>
                </div>

                <div class="box box-primary hide" id="purchase_box_modal">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-2 text-center"></div>
                            <div class="col-sm-8">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-search"></i>
                                        </span>
                                        {!! Form::text('search_purchase', null, ['class' => 'form-control', 'id' => 'search_purchase_modal', 'placeholder' => __('Search Purchase Ref')]); !!}
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-2"></div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="table-responsive">
                                    <table class="table table-condensed table-bordered table-th-green text-center table-striped" id="purchase_entry_table_modal">
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
                                        <tbody id="purchase_rows_modal"></tbody>
                                    </table>
                                </div>
                                <hr>
                                <div class="pull-right col-md-5">
                                    <table class="pull-right col-md-12">
                                        <tbody>
                                            <tr>
                                                <th class="col-md-7 text-right">Total Purchases:</th>
                                                <td class="col-md-5 text-left">
                                                    <span id="total_quantity_modal">0.0000</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="col-md-7 text-right">Net Total Amount:</th>
                                                <td class="col-md-5 text-left">
                                                    <span id="total_subtotal_modal" class="display_currency">$ 0.0000</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <input type="hidden" id="row_count_modal" value="0">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="payment_row">
                    <h4>@lang('purchase.add_payment'):</h4>
                    @include('sale_pos.partials.payment_row_form', ['row_index' => 0, 'show_date' => true])
                    <hr>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="pull-right">
                                <strong>@lang('purchase.payment_due'):</strong>
                                <span id="expense_payment_due">{{@num_format(0)}}</span>
                            </div>
                        </div>
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
</div>

<script>
    $(document).ready(function() {
        // Initialize iCheck for checkboxes
        $('input[type="checkbox"].input-icheck').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%'
        });

        // Initialize datetime picker for modal
        $('#expense_transaction_date').datetimepicker({
            format: moment_date_format + ' ' + moment_time_format,
            ignoreReadonly: true,
            defaultDate: new Date()
        });

        // Toggle purchase section
        $('#is_purchase').on('ifChecked', function() {
            $('#purchase_box_modal').removeClass('hide');
            initializePurchaseSearchModal();
        });
        $('#is_purchase').on('ifUnchecked', function() {
            $('#purchase_box_modal').addClass('hide');
            clearPurchaseDataModal();
        });

        // Enhanced tax calculation system
        function calculateEnhancedTax() {
            var amountBeforeTax = parseFloat($('#expense_amount_before_tax').val()) || 0;
            var taxType = $('#expense_tax_type').val();
            var taxRateElement = $('#expense_tax_id');
            var taxRate = parseFloat(taxRateElement.find('option:selected').data('rate')) || 0;
            var manualTaxValue = parseFloat($('#expense_tax_value').val()) || 0;

            var taxValue = 0;
            var finalTotal = amountBeforeTax;

            if (amountBeforeTax > 0) {
                if (taxType === 'percentage' && taxRate > 0) {
                    // Calculate tax based on selected tax rate percentage
                    taxValue = (amountBeforeTax * taxRate) / 100;
                    $('#expense_tax_value').val(taxValue.toFixed(2));
                    finalTotal = amountBeforeTax + taxValue;
                    $('#tax_value_indicator').text($('#__symbol').val() || '$');
                } else if (taxType === 'fixed') {
                    // Use manually entered fixed tax amount
                    taxValue = manualTaxValue;
                    finalTotal = amountBeforeTax + taxValue;
                    $('#tax_value_indicator').text($('#__symbol').val() || '$');
                }
            }

            $('#expense_final_total').val(finalTotal.toFixed(2));
            updatePaymentDue();
        }

        // Tax type change handler
        $(document).on('change', '#expense_tax_type', function() {
            var taxType = $(this).val();
            var taxValueField = $('#expense_tax_value');
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
        $(document).on('input', '#expense_amount_before_tax', function() {
            calculateEnhancedTax();
        });

        // Tax selection change
        $(document).on('change', '#expense_tax_id', function() {
            calculateEnhancedTax();
        });

        // Manual tax value input (for fixed type)
        $(document).on('input', '#expense_tax_value', function() {
            if ($('#expense_tax_type').val() === 'fixed') {
                calculateEnhancedTax();
            }
        });

        // Enhanced payment due calculation
        function updatePaymentDue() {
            var finalTotal = parseFloat($('#expense_final_total').val()) || 0;
            var paymentAmount = parseFloat($('.payment-amount').val()) || 0;
            var paymentDue = finalTotal - paymentAmount;
            var symbol = $('#__symbol').val() || '$';

            $('#expense_payment_due').text(symbol + ' ' + paymentDue.toFixed(2));

            // Update currency display if function exists
            if (typeof __currency_convert_recursively === 'function') {
                __currency_convert_recursively($('#expense_payment_due').parent());
            }
        }

        // Payment amount change handler
        $(document).on('input change', '.payment-amount', function() {
            updatePaymentDue();
        });

        // Enhanced currency handling on location change
        $(document).on('change', '#expense_location_id', function() {
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

        // Enhanced purchase search for modal
        function initializePurchaseSearchModal() {
            $('#search_purchase_modal').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '/expenses/get_purchases',
                        dataType: 'json',
                        data: {
                            term: request.term,
                            location_id: $('#expense_location_id').val()
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
                    addPurchaseRowModal(ui.item);
                    $(this).val('');
                    return false;
                },
                minLength: 2
            });
        }

        // Add purchase row to modal table
        function addPurchaseRowModal(purchase) {
            var rowCount = parseInt($('#row_count_modal').val()) + 1;
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

            $('#purchase_rows_modal').append(row);
            $('#row_count_modal').val(rowCount);
            updatePurchaseTotalsModal();
        }

        // Remove purchase row in modal
        $(document).on('click', '.remove_purchase_entry_row', function() {
            $(this).closest('tr').remove();
            updatePurchaseTotalsModal();
            updateRowNumbersModal();
        });

        // Update purchase totals in modal
        function updatePurchaseTotalsModal() {
            var totalQuantity = $('#purchase_rows_modal tr').length;
            var totalAmount = 0;

            $('.sub_total').each(function() {
                totalAmount += parseFloat($(this).val()) || 0;
            });

            $('#total_quantity_modal').text(totalQuantity);
            $('#total_subtotal_modal').text($('#__symbol').val() + ' ' + totalAmount.toFixed(2));

            // Update main expense total if this is a purchase expense
            if ($('#is_purchase').is(':checked')) {
                $('#expense_final_total').val(totalAmount.toFixed(2));
                updatePaymentDue();
            }
        }

        // Update row numbers in modal after deletion
        function updateRowNumbersModal() {
            $('#purchase_rows_modal tr').each(function(index) {
                $(this).find('.sr_number').text(index + 1);
            });
            $('#row_count_modal').val($('#purchase_rows_modal tr').length);
        }

        // Clear purchase data in modal
        function clearPurchaseDataModal() {
            $('#purchase_rows_modal').empty();
            $('#row_count_modal').val(0);
            $('#total_quantity_modal').text('0');
            $('#total_subtotal_modal').text($('#__symbol').val() + ' 0.00');
        }

        // Form validation enhancement
        $(document).on('submit', '#add_expense_modal_form', function(e) {
            var amountBeforeTax = parseFloat($('#expense_amount_before_tax').val()) || 0;
            var finalTotal = parseFloat($('#expense_final_total').val()) || 0;

            if (amountBeforeTax <= 0) {
                e.preventDefault();
                if (typeof toastr !== 'undefined') {
                    toastr.error('Please enter a valid amount before tax');
                } else {
                    alert('Please enter a valid amount before tax');
                }
                $('#expense_amount_before_tax').focus();
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

        // Initialize tax type on page load
        var initialTaxType = $('#expense_tax_type').val();
        if (initialTaxType === 'fixed') {
            $('#expense_tax_value').prop('readonly', false);
            $('#tax_value_indicator').text($('#__symbol').val() || '$');
        }

        // Auto-calculate on form load if values exist
        if ($('#expense_amount_before_tax').val()) {
            calculateEnhancedTax();
        }

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

        $('.paid_on').datetimepicker({
            format: moment_date_format + ' ' + moment_time_format,
            ignoreReadonly: true
        });
    });
</script>