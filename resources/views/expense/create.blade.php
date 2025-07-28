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
	<input type="hidden" name="__rate" id="__rate" >
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
				<div class="col-md-4">
			    	<div class="form-group">
			            {!! Form::label('tax_id', __('product.applicable_tax') . ':' ) !!}
			            <div class="input-group">
			                <span class="input-group-addon">
			                    <i class="fa fa-info"></i>
			                </span>
			                {!! Form::select('tax_id', $taxes['tax_rates'], !empty($duplicate_expense) ? $duplicate_expense->tax_id : null, ['class' => 'form-control'], $taxes['attributes']); !!}

							<input type="hidden" name="tax_calculation_amount" id="tax_calculation_amount" 
							value="{{!empty($duplicate_expense) ? $duplicate_expense->final_total : 0 }}">
			            </div>
			        </div>
			    </div>
			    <div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('final_total', __('sale.total_amount') . ':*') !!}
						{!! Form::text('final_total',!empty($duplicate_expense) ? $duplicate_expense->final_total :  null, ['class' => 'form-control input_number', 'placeholder' => __('sale.total_amount'), 'required','id'=>'final_total']); !!}
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
$('#search_purchase').on('keyup', function() {
    // Get the search query
    var term = $(this).val();
    
    // Make AJAX request to fetch search results
    $.ajax({
        url: '/expenses/get_purchases', // Replace with your server endpoint
        method: 'GET',
        data: { location_id: $('#location_id').val(), term: term},
        success: function(response) {
            // Clear previous search results
            $('#product_list').empty();
            // Append new search results
            response.forEach(function(product) {
                var productItem = $('<div class="product-item">' + product.name + '</div>');
                
                // Add click event to product items
                productItem.click(function() {
                    // Handle click action (e.g., redirect to product page)
                    window.location.href = '/products/' + product.id;
                });
                
                $('#product_list').append(productItem);
            });
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
        }
    });
});

	$(document).ready( function(){
		console.log(moment_date_format + ' ' + moment_time_format);
		$('#expense_transaction_date').datetimepicker({
            format: moment_date_format + ' ' + moment_time_format,
            ignoreReadonly: true,
			defaultDate: new Date()
        });
	});
	
	__page_leave_confirmation('#add_expense_form');
	$(document).on('change', 'input#final_total, input.payment-amount', function() {
		calculateExpensePaymentDue();
	});

	function calculateExpensePaymentDue() {
		var final_total = __read_number($('input#final_total'));
		var payment_amount = __read_number($('input.payment-amount'));
		var payment_due = final_total - payment_amount;
		var symbol = $("#__symbol").val();
		$('#payment_due').text(__currency_trans_from_en(payment_due, true, false, null, false, symbol));
	}

	$(document).on('change', '#recur_interval_type', function() {
	    if ($(this).val() == 'months') {
	        $('.recur_repeat_on_div').removeClass('hide');
	    } else {
	        $('.recur_repeat_on_div').addClass('hide');
	    }
	});

	$('#is_purchase').on('ifChecked', function(event){
		$('#purchase_box').removeClass('hide');
	});
	$('#is_purchase').on('ifUnchecked', function(event){
		$('#purchase_box').addClass('hide');
	});

	$('#is_refund').on('ifChecked', function(event){
		$('#recur_expense_div').addClass('hide');
	});
	$('#is_refund').on('ifUnchecked', function(event){
		$('#recur_expense_div').removeClass('hide');
	});

	$(document).on('change', '.payment_types_dropdown, #location_id', function(e) {
	    var default_accounts = $('select#location_id').length ? 
	                $('select#location_id')
	                .find(':selected')
	                .data('default_payment_accounts') : [];
	    var payment_types_dropdown = $('.payment_types_dropdown');
	    var payment_type = payment_types_dropdown.val();
	    if (payment_type) {
	        var default_account = default_accounts && default_accounts[payment_type]['account'] ? 
	            default_accounts[payment_type]['account'] : '';
	        var payment_row = payment_types_dropdown.closest('.payment_row');
	        var row_index = payment_row.find('.payment_row_index').val();

	        var account_dropdown = payment_row.find('select#account_' + row_index);
	        if (account_dropdown.length && default_accounts) {
	            account_dropdown.val(default_account);
	            account_dropdown.change();
	        }
	    }
	    var url = "{{ route('getCurrency', ':id') }}";
	    url = url.replace(":id", $(this).val());
	    $.ajax({
	        url: url,
	        type: 'GET',
	        success: function(response) {
	            $("#__code").val(response.currency.code);
	            $("#__symbol").val(response.currency.symbol);
	            $("#__thousand").val(response.currency.thousand_separator);
	            $("#__decimal").val(response.currency.decimal_separator);
	            $("#__precision").val(response.currency.decimal_precision);
	            $("#__rate").val(response.currency.rate);
	            var paymentDue = $("#payment_due").text().split(" ");
	            paymentDue = paymentDue.length == 2 ? response.currency.symbol + " " + paymentDue[1] : $("#payment_due").text();
	            $("#payment_due").text(paymentDue);
				var html='';
				var accounts=response.accounts;
				
				Object.entries(accounts).forEach(([key, value]) => {
					html += `<option value="${key}">${value}</option>`;
				});
				$('#account_0').html(html);
	        },
	        error: function(response) {
	            console.log(response)
	        }
	    })
	});
  
</script>
@endsection