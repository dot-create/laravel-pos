// index.blade.php
@extends('layouts.app')
@section('title', __('expense.expenses'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('expense.expenses')</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
                @if(auth()->user()->can('all_expense.access'))
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('location_id',  __('purchase.business_location') . ':') !!}
                            {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                        </div>
                    </div>

                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('expense_for', __('expense.expense_for').':') !!}
                            {!! Form::select('expense_for', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('expense_contact_filter',  __('contact.contact') . ':') !!}
                            {!! Form::select('expense_contact_filter', $contacts, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                        </div>
                    </div>
                @endif
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('expense_category_id',__('expense.expense_category').':') !!}
                        {!! Form::select('expense_category_id', $categories, null, ['placeholder' =>
                        __('report.all'), 'class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'expense_category_id']); !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('expense_sub_category_id_filter',__('product.sub_category').':') !!}
                        {!! Form::select('expense_sub_category_id_filter', $sub_categories, null, ['placeholder' =>
                        __('report.all'), 'class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'expense_sub_category_id_filter']); !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('expense_date_range', __('report.date_range') . ':') !!}
                        {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'expense_date_range', 'readonly']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('expense_payment_status',  __('purchase.payment_status') . ':') !!}
                        {!! Form::select('expense_payment_status', ['paid' => __('lang_v1.paid'), 'due' => __('lang_v1.due'), 'partial' => __('lang_v1.partial')], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('expense.all_expenses')])
                @can('expense.add')
                    @slot('tool')
                        <div class="box-tools">
                            <a class="btn btn-block btn-primary" href="{{action('ExpenseController@create')}}">
                            <i class="fa fa-plus"></i> @lang('messages.add')</a>
                        </div>
                    @endslot
                @endcan
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="expense_table">
                        <thead>
                            <tr>
                                <th>@lang('messages.action')</th>
                                <th>@lang('messages.date')</th>
                                <th>@lang('purchase.ref_no')</th>
                                <th>@lang('lang_v1.recur_details')</th>
                                <th>@lang('expense.expense_category')</th>
                                <th>@lang('product.sub_category')</th>
                                <th>@lang('business.location')</th>
                                <th>@lang('sale.payment_status')</th>
                                <th>@lang('product.tax')</th>
                                <th>@lang('sale.total_amount')</th>
                                <th>@lang('purchase.payment_due')
                                <th>@lang('expense.expense_for')</th>
                                <th>@lang('contact.contact')</th>
                                <th>@lang('expense.expense_note')</th>
                                <th>@lang('lang_v1.added_by')</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr class="bg-gray font-17 text-center footer-total">
                                <td colspan="7"><strong>@lang('sale.total'):</strong></td>
                                <td class="footer_payment_status_count"></td>
                                <td></td>
                                <td class="footer_expense_total"></td>
                                <td class="footer_total_due"></td>
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>

</section>
<!-- /.content -->
<!-- /.content -->
<div class="modal fade payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>
@stop
@section('javascript')
 <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
@endsection


// create.blade.php
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


// edit.blade.php
@extends('layouts.app')
@section('title', __('expense.edit_expense'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('expense.edit_expense')</h1>
	  <input type="hidden" name="__rate" id="__rate" >

</section>

<!-- Main content -->
<section class="content">
  {!! Form::open(['url' => action('ExpenseController@update', [$expense->id]), 'method' => 'PUT', 'id' => 'add_expense_form', 'files' => true ]) !!}
  <div class="box box-solid">
    <div class="box-body">
      <div class="row">
        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('location_id', __('purchase.business_location').':*') !!}
            {!! Form::select('location_id', $business_locations, $expense->location_id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']); !!}
          </div>
        </div>
        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('expense_category_id', __('expense.expense_category').':') !!}
            {!! Form::select('expense_category_id', $expense_categories, $expense->expense_category_id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
          </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('expense_sub_category_id', __('product.sub_category')  . ':') !!}
                  {!! Form::select('expense_sub_category_id', $sub_categories, $expense->expense_sub_category_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); !!}
            </div>
        </div>
        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('ref_no', __('purchase.ref_no').':*') !!}
            {!! Form::text('ref_no', $expense->ref_no, ['class' => 'form-control', 'required']); !!}
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
              {!! Form::text('transaction_date', @format_datetime($expense->transaction_date), ['class' => 'form-control', 'readonly', 'required', 'id' => 'expense_transaction_date']); !!}
            </div>
          </div>
        </div>
        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('expense_for', __('expense.expense_for').':') !!} @show_tooltip(__('tooltip.expense_for'))
            {!! Form::select('expense_for', $users, $expense->expense_for, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
          </div>
        </div>
        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('contact_id', __('lang_v1.expense_for_contact').':') !!} 
            {!! Form::select('contact_id', $contacts, $expense->contact_id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('document', __('purchase.attach_document') . ':') !!}
                {!! Form::file('document', ['id' => 'upload_document', 'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types')))]); !!}
                <p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)])
                @includeIf('components.document_help_text')</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('tax_id', __('product.applicable_tax') . ':' ) !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-info"></i>
                    </span>
                    {!! Form::select('tax_id', $taxes['tax_rates'], $expense->tax_id, ['class' => 'form-control'], $taxes['attributes']); !!}

              <input type="hidden" name="tax_calculation_amount" id="tax_calculation_amount" 
              value="0">
                </div>
            </div>
        </div>
        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('final_total', __('sale.total_amount') . ':*') !!}
            {{-- {!! Form::text('final_total', str_replace(",","", $expense->final_total / $currency_details->rate), ['class' => 'form-control input_number', 'placeholder' => __('sale.total_amount'), 'required']); !!} --}}
            {!! Form::text('final_total', str_replace(",","", $expense->final_total), ['class' => 'form-control input_number', 'placeholder' => __('sale.total_amount'), 'required']); !!}
          </div>
        </div>
         <div class="col-md-4 col-sm-6">
					<br>
					      <label>
		              {!! Form::checkbox('is_purchase', 1, !empty($expense->is_purchase==1) ? true :false, ['class' => 'input-icheck', 'id' => 'is_purchase']); !!} @lang('Is Purchase')?
		            </label>@show_tooltip(__('Is Purchase Expense'))
				</div>
        <div class="clearfix"></div>
        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('additional_notes', __('expense.expense_note') . ':') !!}
                {!! Form::textarea('additional_notes', $expense->additional_notes, ['class' => 'form-control', 'rows' => 3]); !!}
          </div>
        </div>
       
      </div>
    </div>
  </div> <!--box end-->

  <div class="box box-primary {{($expense->is_purchase==1)?'':'hide'}}" id="purchase_box">
                
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
							<tbody id="purchase_rows">
                @foreach($purchases as $key=>$purchase)
                  @php
                      $foriegn_currency=$currency_details->symbol;
                      $final_total=$purchase->transaction->final_total;
                      if ($currency_details->symbol=="HNL") {
                          $foriegn_currency='$';
                          $final_total=number_format($purchase->transaction->final_total/$currency_details->rate,2);
                      }
                      $tax_amount=0;
                      if($expense->tax_id==5){
                          $tax_amount=($purchase->total/100)*15;
                      }
                      elseif($expense->tax_id==7){
                          $tax_amount=($purchase->total/100)*18;
                      }
                  @endphp
                  <tr>
                    <td class="sr_number">
                    {{$key+1}}</td>
                    <td><input type="hidden" name="purchases[]" value="{{$purchase->purchase_id}}">
                    {{$purchase->transaction->contact->name}}</td>
                    <td>{{$purchase->transaction->ref_no}}</td>
                      <td >{{$currency_details->symbol}}   <input type="number" step="0.01" name="sub_total[]" class="sub_total" value="{{$purchase->total}}"></td>
                    <td class="tax_total" data-tax-total="{{$purchase->total+$tax_amount}}">{{$currency_details->symbol}} {{$purchase->total+$tax_amount}}</td>
                    <td> {{$foriegn_currency}} {{$final_total}}</td>
                    <td> <button type="button" class="btn btn-danger btn-sm remove_purchase_entry_row"><i class="fas fa-trash"></i> </td>
                  </tr>
                @endforeach
              </tbody>
						</table>
					</div>
					<hr>
					<div class="pull-right col-md-5">
						<table class="pull-right col-md-12">
							<tbody><tr>
								<th class="col-md-7 text-right">Total Purchases:</th>
								<td class="col-md-5 text-left">
									<span id="total_quantity" >{{count($purchases)}}</span>
								</td>
							</tr>
							<tr>
								<th class="col-md-7 text-right">Net Total Amount:</th>
								<td class="col-md-5 text-left">
									<span id="total_subtotal">{{$currency_details->symbol}}  {{ $expense->final_total}}</span>
									<!-- This is total before purchase tax-->
								</td>
							</tr>
						</tbody></table>
					</div>
	
					<input type="hidden" id="row_count" value="{{count($purchases)}}">
				</div>
			</div>
		</div>
		<!-- /.box-body -->
	</div>

  @include('expense.recur_expense_form_part')
  <div class="col-sm-12 text-center">
    <button type="submit" class="btn btn-primary btn-big">@lang('messages.update')</button>
  </div>

{!! Form::close() !!}
</section>
@stop
@section('javascript')
<script src="{{ asset('js/expense.js?v=' . $asset_v) }}"></script>

<script type="text/javascript">
  $('#__symbol').val('{{$currency_details->symbol}}');
  
  $('#is_purchase').on('ifChecked', function(event){
		$('#purchase_box').removeClass('hide');
	});
	$('#is_purchase').on('ifUnchecked', function(event){
		$('#purchase_box').addClass('hide');
	});

  __page_leave_confirmation('#add_expense_form');
</script>
@endsection


// show.blade.php
@extends('layouts.app')
@section('title', 'Purchase Details')

@section('content')
  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <h2 class="page-header">
          Purchase Details
          <small class="pull-right"><b>Date:</b> {{ date( 'd/m/Y', strtotime( $purchase->transaction_date ) ) }}</small>
        </h2>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-4">
        <b>Reference No:</b> #{{ $purchase->ref_no }}<br>
        <b>Location:</b> {{ $purchase->location->name }}<br>
        <b>Status:</b> {{ ucfirst( $purchase->status ) }}<br>
        <b>Payment Status:</b> {{ ucfirst( $purchase->payment_status ) }}<br>
      </div>
      <div class="col-sm-4">
        <b>Supplier:</b> {{ $purchase->contact->name }}<br>
        <b>Business:</b> {{ $purchase->contact->supplier_business_name }}<br>
      </div>
    </div>
    <br>
    <div class="row">
      <div class="col-xs-12">
        <div class="table-responsive">
          <table class="table bg-gray">
            <tr class="bg-green">
              <th>#</th>
              <th>Product</th>
              <th>Quantity</th>
              <th>Unit Cost Price (Before Tax)</th>
              <th>Subtotal (Before Tax)</th>
              <th>Tax</th>
              <th>Unit Cost Price (After Tax)</th>
              <th>Unit Selling Price</th>
              <th>Subtotal</th>
            </tr>
            @php 
              $total_before_tax = 0.00;
            @endphp
            @foreach($purchase->purchase_lines as $purchase_line)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                  {{ $purchase_line->product->name }}
                   @if( $purchase_line->product->type == 'variable')
                    - {{ $purchase_line->variations->product_variation->name}}
                    - {{ $purchase_line->variations->name}}
                   @endif
                </td>
                <td>{{ $purchase_line->quantity }}</td>
                <td><span class="display_currency" data-currency_symbol="true">{{ $purchase_line->purchase_price }}</span></td>
                <td><span class="display_currency" data-currency_symbol="true">{{ $purchase_line->quantity * $purchase_line->purchase_price }}</span></td>
                <td><span class="display_currency" data-currency_symbol="true">{{ $purchase_line->item_tax }} </span> @if($purchase_line->tax_id) ( {{ $taxes[$purchase_line->tax_id]}} ) @endif</td>
                <td><span class="display_currency" data-currency_symbol="true">{{ $purchase_line->purchase_price_inc_tax }}</span></td>
                <td><span class="display_currency" data-currency_symbol="true">{{ $purchase_line->variations->default_sell_price }}</span></td>
                <td><span class="display_currency" data-currency_symbol="true">{{ $purchase_line->purchase_price_inc_tax * $purchase_line->quantity }}</span></td>
              </tr>
              @php 
                $total_before_tax += ($purchase_line->quantity * $purchase_line->purchase_price);
              @endphp
            @endforeach
          </table>
        </div>
      </div>
    </div>
    <br>
    <div class="row">
      <div class="col-xs-6">
        <p><b>Shipping details:</b></p>
        <p class="well well-sm no-shadow bg-gray" style="border-radius: 0px;">
         {{ $purchase->shipping_details }}
        </p>
        <p><b>Notes:</b></p>
        <p class="well well-sm no-shadow bg-gray" style="border-radius: 0px;">
         {{ $purchase->additional_notes }}
        </p>
      </div>
      <div class="col-xs-6">
        <div class="table-responsive">
          <table class="table bg-gray">
            <tr>
              <th>Total Before Tax: </th>
              <td></td>
              <td><span class="display_currency pull-right">{{ $total_before_tax }}</span></td>
            </tr>
            <tr>
              <th>Total After Tax: </th>
              <td></td>
              <td><span class="display_currency pull-right">{{ $total_before_tax }}</span></td>
            </tr>
            <tr>
              <th>Purchase Tax:</th>
              <td><b>(+)</b></td>
              <td><span class="display_currency pull-right">{{ $purchase->tax_amount }}</span></td>
            </tr>
            <tr>
              <th>Discount:</th>
              <td><b>(-)</b></td>
              <td><span class="display_currency pull-right">{{ $purchase->discount_amount }}</span></td>
            </tr>
            @if( !empty( $purchase->shipping_charges ) )
              <tr>
                <th>Additional Shipping charges:</th>
                <td><b>(+)</b></td>
                <td><span class="display_currency pull-right" >{{ $purchase->shipping_charges }}</span></td>
              </tr>
            @endif
            <tr>
              <th>Purchase Total:</th>
              <td></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true" >{{ $purchase->final_total }}</span></td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </section>
  <!-- /.content -->
@endsection


// add_expense_modal.blade.php
<div class="modal-dialog" role="document">
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
                        {!! Form::select('expense_category_id', $expense_categories, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
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
                        {!! Form::select('expense_for', $users, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
                    </div>
                </div>                
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('expense_tax_id', __('product.applicable_tax') . ':' ) !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-info"></i>
                            </span>
                            {!! Form::select('tax_id', $taxes['tax_rates'], null, ['class' => 'form-control', 'id'=>'expense_tax_id'], $taxes['attributes']); !!}

                            <input type="hidden" name="tax_calculation_amount" id="tax_calculation_amount" 
                            value="0">
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-sm-6">
                    <div class="form-group">
                        {!! Form::label('expense_final_total', __('sale.total_amount') . ':*') !!}
                        {!! Form::text('final_total', null, ['class' => 'form-control input_number', 'placeholder' => __('sale.total_amount'), 'required', 'id' => 'expense_final_total']); !!}
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        {!! Form::label('expense_additional_notes', __('expense.expense_note') . ':') !!}
                                {!! Form::textarea('additional_notes', null, ['class' => 'form-control', 'rows' => 3, 'id' => 'expense_additional_notes']); !!}
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



// recur_expense_form_part.blade.php
<div class="box box-solid @if(!empty($expense->type) && $expense->type == 'expense_refund') hide @endif" id="recur_expense_div">
	<div class="box-body">
		<div class="row">
			<div class="col-md-4 col-sm-6">
				<br>
				<label>
	              {!! Form::checkbox('is_recurring', 1, !empty($expense->is_recurring) == 1, ['class' => 'input-icheck', 'id' => 'is_recurring']); !!} @lang('lang_v1.is_recurring')?
	            </label>@show_tooltip(__('lang_v1.recurring_expense_help'))
			</div>
			<div class="col-md-4 col-sm-6">
		        <div class="form-group">
		        	{!! Form::label('recur_interval', __('lang_v1.recur_interval') . ':*' ) !!}
		        	<div class="input-group">
		               {!! Form::number('recur_interval', !empty($expense->recur_interval) ? $expense->recur_interval : null, ['class' => 'form-control', 'style' => 'width: 50%;']); !!}
		               
		                {!! Form::select('recur_interval_type', ['days' => __('lang_v1.days'), 'months' => __('lang_v1.months'), 'years' => __('lang_v1.years')], !empty($expense->recur_interval_type) ? $expense->recur_interval_type : 'days', ['class' => 'form-control', 'style' => 'width: 50%;', 'id' => 'recur_interval_type']); !!}
		                
		            </div>
		        </div>
		    </div>

		    <div class="col-md-4 col-sm-6">
		        <div class="form-group">
		        	{!! Form::label('recur_repetitions', __('lang_v1.no_of_repetitions') . ':' ) !!}
		        	{!! Form::number('recur_repetitions', !empty($expense->recur_repetitions) ? $expense->recur_repetitions : null, ['class' => 'form-control']); !!}
			        <p class="help-block">@lang('lang_v1.recur_expense_repetition_help')</p>
		        </div>
		    </div>
		    @php
		    	$repetitions = [];
		    	for ($i=1; $i <= 30; $i++) { 
		    		$repetitions[$i] = str_ordinal($i);
		        }
		    @endphp
		    <div class="recur_repeat_on_div col-md-4 @if(empty($expense->recur_interval_type)) hide @elseif(!empty($expense->recur_interval_type) && $expense->recur_interval_type != 'months') hide @endif">
		        <div class="form-group">
		        	{!! Form::label('subscription_repeat_on', __('lang_v1.repeat_on') . ':' ) !!}
		        	{!! Form::select('subscription_repeat_on', $repetitions, !empty($expense->subscription_repeat_on) ? $expense->subscription_repeat_on : null, ['class' => 'form-control', 'placeholder' => __('messages.please_select')]); !!}
		        </div>
		    </div>
		</div>
	</div>
</div>



// Controler
<?php

namespace App\Http\Controllers;

use App\Account;

use App\AccountTransaction;
use App\BusinessLocation;
use App\ExpenseCategory;
use App\TaxRate;
use App\Transaction;
use App\ExpensePurchase;
use App\User;
use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use App\Contact;
use App\Currency;
use App\Utils\CashRegisterUtil;

class ExpenseController extends Controller
{
    /**
    * Constructor
    *
    * @param TransactionUtil $transactionUtil
    * @return void
    */
    public function __construct(TransactionUtil $transactionUtil, ModuleUtil $moduleUtil, CashRegisterUtil $cashRegisterUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->dummyPaymentLine = ['method' => 'cash', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
        'is_return' => 0, 'transaction_no' => ''];
        $this->cashRegisterUtil = $cashRegisterUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('all_expense.access') && !auth()->user()->can('view_own_expense')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $expenses = Transaction::leftJoin('expense_categories AS ec', 'transactions.expense_category_id', '=', 'ec.id')
                            ->leftJoin('expense_categories AS esc', 'transactions.expense_sub_category_id', '=', 'esc.id')
                            ->join(
                                'business_locations AS bl',
                                'transactions.location_id',
                                '=',
                                'bl.id'
                            )
                            ->leftJoin('tax_rates as tr', 'transactions.tax_id', '=', 'tr.id')
                            ->leftJoin('users AS U', 'transactions.expense_for', '=', 'U.id')
                            ->leftJoin('users AS usr', 'transactions.created_by', '=', 'usr.id')
                            ->leftJoin('contacts AS c', 'transactions.contact_id', '=', 'c.id')
                            ->leftJoin(
                                'transaction_payments AS TP',
                                'transactions.id',
                                '=',
                                'TP.transaction_id'
                            )
                            ->where('transactions.business_id', $business_id)
                            ->whereIn('transactions.type', ['expense', 'expense_refund'])
                            ->select(
                                'transactions.id',
                                'transactions.document',
                                'transaction_date',
                                'transactions.created_at',
                                'ref_no',
                                'ec.name as category',
                                'esc.name as sub_category',
                                'payment_status',
                                'additional_notes',
                                'final_total',
                                'transactions.is_recurring',
                                'transactions.recur_interval',
                                'transactions.recur_interval_type',
                                'transactions.recur_repetitions',
                                'transactions.subscription_repeat_on',
                                'bl.name as location_name',
                                'bl.id as location_id',
                                'bl.currency_id as currency_id',
                                DB::raw("CONCAT(COALESCE(U.surname, ''),' ',COALESCE(U.first_name, ''),' ',COALESCE(U.last_name,'')) as expense_for"),
                                DB::raw("CONCAT(tr.name ,' (', tr.amount ,' )') as tax"),
                                DB::raw('SUM(TP.amount) as amount_paid'),
                                DB::raw("CONCAT(COALESCE(usr.surname, ''),' ',COALESCE(usr.first_name, ''),' ',COALESCE(usr.last_name,'')) as added_by"),
                                'transactions.recur_parent_id',
                                DB::raw("CONCAT(COALESCE(c.name, ''), ' - ', COALESCE(c.supplier_business_name, ''), '(', COALESCE(c.contact_id, ''), ')') as contact_name"),
                                'transactions.type'
                            )
                            ->with(['recurring_parent'])
                            ->groupBy('transactions.id');

            //Add condition for expense for,used in sales representative expense report & list of expense
            if (request()->has('expense_for')) {
                $expense_for = request()->get('expense_for');
                if (!empty($expense_for)) {
                    $expenses->where('transactions.expense_for', $expense_for);
                }
            }

            if (request()->has('contact_id')) {
                $contact_id = request()->get('contact_id');
                if (!empty($contact_id)) {
                    $expenses->where('transactions.contact_id', $contact_id);
                }
            }

            //Add condition for location,used in sales representative expense report & list of expense
            if (request()->has('location_id')) {
                $location_id = request()->get('location_id');
                if (!empty($location_id)) {
                    $expenses->where('transactions.location_id', $location_id);
                }
            }

            //Add condition for expense category, used in list of expense,
            if (request()->has('expense_category_id')) {
                $expense_category_id = request()->get('expense_category_id');
                if (!empty($expense_category_id)) {
                    $expenses->where('transactions.expense_category_id', $expense_category_id);
                }
            }

            //Add condition for expense sub category, used in list of expense,
            if (request()->has('expense_sub_category_id')) {
                $expense_sub_category_id = request()->get('expense_sub_category_id');
                if (!empty($expense_sub_category_id)) {
                    $expenses->where('transactions.expense_sub_category_id', $expense_sub_category_id);
                }
            }

            //Add condition for start and end date filter, uses in sales representative expense report & list of expense
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $expenses->whereDate('transaction_date', '>=', $start)
                        ->whereDate('transaction_date', '<=', $end);
            }

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $expenses->whereIn('transactions.location_id', $permitted_locations);
            }

            //Add condition for payment status for the list of expense
            if (request()->has('payment_status')) {
                $payment_status = request()->get('payment_status');
                if (!empty($payment_status)) {
                    $expenses->where('transactions.payment_status', $payment_status);
                }
            }

            $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
            if (!$is_admin && !auth()->user()->can('all_expense.access')) {
                $user_id = auth()->user()->id;
                $expenses->where(function ($query) use ($user_id) {
                        $query->where('transactions.created_by', $user_id)
                        ->orWhere('transactions.expense_for', $user_id);
                    });
            }
            
            return Datatables::of($expenses)
                ->addColumn(
                    'action',
                    '<div class="btn-group">
                        <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                            data-toggle="dropdown" aria-expanded="false"> @lang("messages.actions")<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                </span>
                        </button>
                    <ul class="dropdown-menu dropdown-menu-left" role="menu">
                    @if(auth()->user()->can("expense.edit"))
                        <li><a href="{{action(\'ExpenseController@edit\', [$id])}}"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</a></li>
                        <li><a href="{{ action(\'ExpenseController@create\', ["d" => $id]) }}"><i class="fa fa-copy"></i> Duplicate Expense </a></li>
                    @endif
                    @if($document)
                        <li><a href="{{ url(\'uploads/documents/\' . $document)}}" 
                        download=""><i class="fa fa-download" aria-hidden="true"></i> @lang("purchase.download_document")</a></li>
                        @if(isFileImage($document))
                            <li><a href="#" data-href="{{ url(\'uploads/documents/\' . $document)}}" class="view_uploaded_document"><i class="fas fa-file-image" aria-hidden="true"></i>@lang("lang_v1.view_document")</a></li>
                        @endif
                    @endif
                    @if(auth()->user()->can("expense.delete"))
                        <li>
                        <a href="#" data-href="{{action(\'ExpenseController@destroy\', [$id])}}" class="delete_expense"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</a></li>
                    @endif
                    <li class="divider"></li> 
                    @if($payment_status != "paid")
                        <li><a href="{{action("TransactionPaymentController@addPayment", [$id])}}" class="add_payment_modal"><i class="fas fa-money-bill-alt" aria-hidden="true"></i> @lang("purchase.add_payment")</a></li>
                    @endif
                    <li><a href="{{action("TransactionPaymentController@show", [$id])}}" class="view_payment_modal"><i class="fas fa-money-bill-alt" aria-hidden="true" ></i> @lang("purchase.view_payments")</a></li>
                    </ul></div>'
                )
                ->removeColumn('id')
                ->editColumn( 'final_total', function($row) {
                    // return json_encode($row->created_at);
                    $currency = Currency::where("id", $row->currency_id)->first();
                    $html = '<span class="display_currency final-total" data-currency_symbol="true" data-currency="'.$currency->symbol.'" data-orig-value="';
                    if($row->type=="expense_refund"){
                        $html .= -1 * $row->final_total;
                    }else{
                        $html .= $row->final_total;
                    } 
                    $html .= '">';
                    if($row->type=="expense_refund") {
                        $html .= "-";
                    }
                    $dateToCheck = $row->created_at;
                    $compareDate = "2023-09-07";
                    // $final_total =  strtotime($dateToCheck) > strtotime($compareDate) ? number_format($row->final_total / $currency->rate, 2) : number_format($row->final_total, 2);
                    // $final_total =  number_format($row->final_total / $currency->rate, 2); CBY Bilal

                    $final_total =  strtotime($dateToCheck) > strtotime($compareDate) ? number_format($row->final_total , 2) : number_format($row->final_total, 2);
                    $final_total =  number_format($row->final_total , 2);
                    
                    $html .= $currency->symbol . " " . $final_total ."</span>";
                    return $html;
                })
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->editColumn(
                    'payment_status',
                    '<a href="{{ action("TransactionPaymentController@show", [$id])}}" class="view_payment_modal payment-status" data-orig-value="{{$payment_status}}" data-status-name="{{__(\'lang_v1.\' . $payment_status)}}"><span class="label @payment_status($payment_status)">{{__(\'lang_v1.\' . $payment_status)}}
                        </span></a>'
                )
                ->addColumn('payment_due', function ($row) {
                    $currency = Currency::where("id", $row->currency_id)->first();
                    $dateToCheck = $row->created_at;
                    $compareDate = "2023-09-07";
                    $due =  strtotime($dateToCheck) > strtotime($compareDate) ? ($row->final_total) - $row->amount_paid : $row->final_total - $row->amount_paid;
                    // $due =  strtotime($dateToCheck) > strtotime($compareDate) ? ($row->final_total / $currency->rate) - $row->amount_paid : $row->final_total - $row->amount_paid; CBY Bilal
                    // $due = ($row->final_total / $currency->rate) - $row->amount_paid;
                    if ($row->type == 'expense_refund') {
                        $due = -1 * $due;
                    }
                    return '<span class="display_currency payment_due" data-currency_symbol="true" data-orig-value="' . $due . '" data-currency="'.$currency->symbol.'">' . $currency->symbol . " " . number_format($due, 2) . '</span>';
                })
                ->addColumn('recur_details', function($row){
                    $details = '<small>';
                    if ($row->is_recurring == 1) {
                        $type = $row->recur_interval == 1 ? Str::singular(__('lang_v1.' . $row->recur_interval_type)) : __('lang_v1.' . $row->recur_interval_type);
                        $recur_interval = $row->recur_interval . $type;
                        
                        $details .= __('lang_v1.recur_interval') . ': ' . $recur_interval; 
                        if (!empty($row->recur_repetitions)) {
                            $details .= ', ' .__('lang_v1.no_of_repetitions') . ': ' . $row->recur_repetitions; 
                        }
                        if ($row->recur_interval_type == 'months' && !empty($row->subscription_repeat_on)) {
                            $details .= '<br><small class="text-muted">' . 
                            __('lang_v1.repeat_on') . ': ' . str_ordinal($row->subscription_repeat_on) ;
                        }
                    } elseif (!empty($row->recur_parent_id)) {
                        $details .= __('lang_v1.recurred_from') . ': ' . $row->recurring_parent->ref_no;
                    }
                    $details .= '</small>';
                    return $details;
                })
                ->editColumn('ref_no', function($row){
                    $ref_no = $row->ref_no;
                    if (!empty($row->is_recurring)) {
                        $ref_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.recurring_expense') .'"><i class="fas fa-recycle"></i></small>';
                    }

                    if (!empty($row->recur_parent_id)) {
                        $ref_no .= ' &nbsp;<small class="label bg-info label-round no-print" title="' . __('lang_v1.generated_recurring_expense') .'"><i class="fas fa-recycle"></i></small>';
                    }

                    if ($row->type == 'expense_refund') {
                        $ref_no .= ' &nbsp;<small class="label bg-gray">' . __('lang_v1.refund') . '</small>';
                    }

                    return $ref_no;
                })
                ->rawColumns(['final_total', 'action', 'payment_status', 'payment_due', 'ref_no', 'recur_details'])
                ->make(true);
        }

        $business_id = request()->session()->get('user.business_id');

        $categories = ExpenseCategory::where('business_id', $business_id)
                            ->whereNull('parent_id')
                            ->pluck('name', 'id');

        $users = User::forDropdown($business_id, false, true, true);

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        $contacts = Contact::contactDropdown($business_id, false, false);

        $sub_categories = ExpenseCategory::where('business_id', $business_id)
                        ->whereNotNull('parent_id')
                        ->pluck('name', 'id')
                        ->toArray();

        return view('expense.index')
            ->with(compact('categories', 'business_locations', 'users', 'contacts', 'sub_categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('expense.add')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        
        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action('ExpenseController@index'));
        }

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $expense_categories = ExpenseCategory::where('business_id', $business_id)
                                ->whereNull('parent_id')
                                ->pluck('name', 'id');

        $users = User::forDropdown($business_id, true, true);

        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);
        
        $payment_line = $this->dummyPaymentLine;

        $payment_types = $this->transactionUtil->payment_types(null, false, $business_id);

        $contacts = Contact::contactDropdown($business_id, false, false);

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false, true);
        }

        //Duplicate Expense
        $duplicate_expense = null;
        $sub_categories=[];
        if (!empty(request()->input('d'))) {
            $duplicate_expense = Transaction::where('business_id', $business_id)->find(request()->input('d'));
            $duplicate_expense->ref_no .= ' (copy)';
            $sub_categories = ExpenseCategory::where('business_id', $business_id)
                        ->where('parent_id', $duplicate_expense->expense_category_id)
                        ->select(['name', 'id'])
                        ->pluck('name', 'id');
        }
        // dd($expense_categories,$sub_categories);

        if (request()->ajax()) {
            return view('expense.add_expense_modal')
                ->with(compact('expense_categories', 'business_locations', 'users', 'taxes', 'payment_line', 'payment_types', 'accounts', 'bl_attributes', 'contacts'));
        }

        return view('expense.create')
            ->with(compact('duplicate_expense','sub_categories','expense_categories', 'business_locations', 'users', 'taxes', 'payment_line', 'payment_types', 'accounts', 'bl_attributes', 'contacts', 'tax_types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('expense.add')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // dd($request->all());
            $business_id = $request->session()->get('user.business_id');
            //Check if subscribed or not
            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse(action('ExpenseController@index'));
            }
            //Validate document size
            $request->validate([
                'document' => 'file|max:'. (config('constants.document_size_limit') / 1000)
            ]);
            $business_location = BusinessLocation::where('id', $request->location_id)->first();
            $currency_details = Currency::where('id', $business_location->currency_id)->first();
            // dd($request->sub_total);
            $request['exchange_rate'] = $currency_details->rate;
            
            $user_id = $request->session()->get('user.id');
            DB::beginTransaction();
            $expense = $this->transactionUtil->createExpense($request, $business_id, $user_id);
            // dd($expense);
            if(isset($request->is_purchase) && $request->is_purchase==1){
                $expense->update(['is_purchase'=>1]);

                foreach ($request->purchases as $key => $purchaseId) {
                    ExpensePurchase::create([
                        "expense_id" => $expense->id,
                        "purchase_id" => $purchaseId,
                        "total" => $request->sub_total[$key] ,
                    ]);
                }
                
            }
            if (request()->ajax()) {
                $payments = !empty($request->input('payment')) ? $request->input('payment') : [];
                $sellPayment = $this->cashRegisterUtil->addSellPayments($expense, $payments);
            }
            // return $sellPayment;
            $this->transactionUtil->activityLog($expense, 'added');

            DB::commit();

            $output = ['success' => 1,
                            'msg' => __('expense.expense_add_success')
                        ];
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        if (request()->ajax()) {
            return $output;
        }

        return redirect('expenses')->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('expense.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action('ExpenseController@index'));
        }

        $business_locations = BusinessLocation::forDropdown($business_id);

        $expense_categories = ExpenseCategory::where('business_id', $business_id)
                                ->whereNull('parent_id')
                                ->pluck('name', 'id');
        $expense = Transaction::where('business_id', $business_id)
                                ->where('id', $id)
                                ->first();
        $purchases = ExpensePurchase::with('transaction.contact')->where('expense_id',$id)->get();
        $business_location = BusinessLocation::where('id', $expense->location_id)->first();
        $currency_details = Currency::where('id', $business_location->currency_id)->first();
        $users = User::forDropdown($business_id, true, true);

        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

        $contacts = Contact::contactDropdown($business_id, false, false);

        //Sub-category
        $sub_categories = [];

        if (!empty($expense->expense_category_id)) {
            $sub_categories = ExpenseCategory::where('business_id', $business_id)
                        ->where('parent_id', $expense->expense_category_id)
                        ->pluck('name', 'id')
                        ->toArray();
        }
        
        return view('expense.edit')
            ->with(compact('expense', 'expense_categories', 'business_locations', 'users', 'taxes', 'contacts', 'sub_categories','currency_details','purchases'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('expense.edit')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            //Validate document size
            $request->validate([
                'document' => 'file|max:'. (config('constants.document_size_limit') / 1000)
            ]);
            
            $business_location = BusinessLocation::where('id', $request->location_id)->first();
            $currency_details = Currency::where('id', $business_location->currency_id)->first();
            $request['exchange_rate'] = $currency_details->rate;
            $business_id = $request->session()->get('user.business_id');
            
            //Check if subscribed or not
            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse(action('ExpenseController@index'));
            }

            $expense = $this->transactionUtil->updateExpense($request, $id, $business_id);
            // dd( $request->sub_total);
            if(isset($request->is_purchase) && $request->is_purchase==1){
                $expense->update(['is_purchase'=>1]);
                ExpensePurchase::whereNotIn('purchase_id',$request->purchases)->where('expense_id',$id)->delete();

                foreach ($request->purchases as $key => $purchaseId) {
                    $isExist = ExpensePurchase::where([
                        "expense_id" => $id,
                        "purchase_id" => $purchaseId
                    ])->first();
                    if($isExist!=null){
                        ExpensePurchase::where([
                            "expense_id" => $id,
                            "purchase_id" => $purchaseId,
                        ])->update([
                            "total" => $request->sub_total[$key] ,
                        ]);
                    }else{
                        ExpensePurchase::create([
                            "expense_id" => $id,
                            "purchase_id" => $purchaseId,
                            "total" => $request->sub_total[$key] ,
                        ]);
                    }
                   
                }
            }else{
                $expense->update(['is_purchase'=>0]);
                ExpensePurchase::where([
                    "expense_id" => $expense->id,
                ])->delete();
            }
            $this->transactionUtil->activityLog($expense, 'edited');

            $output = ['success' => 1,
                            'msg' => __('expense.expense_update_success')
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        return redirect('expenses')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('expense.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $expense = Transaction::where('business_id', $business_id)
                                        ->where(function($q) {
                                            $q->where('type', 'expense')
                                                ->orWhere('type', 'expense_refund');
                                        })
                                        ->where('id', $id)
                                        ->first();
                $expense->delete();

                //Delete account transactions
                AccountTransaction::where('transaction_id', $expense->id)->delete();

                $output = ['success' => true,
                            'msg' => __("expense.expense_delete_success")
                            ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
            }

            return $output;
        }
    }
    
    public function getCurrency($id) {
        $business_location = BusinessLocation::where('id', $id)->first();
        if($business_location) {
            $currency = Currency::where('id', $business_location->currency_id)->first();
            
            $business_id = request()->session()->get('user.business_id');
            $accounts = Account::forDropdown($business_id,true,false,true,$currency->id);
            return response()->json([
                'success' => true,
                'currency' => $currency,
                'accounts' => $accounts,
            ]);
        } else {
             return response()->json([
                'success' => false
            ]);
        }
    }

    public function getPurchases()
    {
      
            $term = request()->term;
            $location_id = request()->location_id;


            if (empty($term)) {
                return json_encode([]);
            }
            if (empty($location_id)) {
                return json_encode([]);
            }
            $select=[
                "id",
                "ref_no as text",
                "contact_id",
                "location_id",
                "total_before_tax",
                "tax_amount",
                "final_total",
            ];
            // $purchases= Transaction::with('contact')->select($select)->where(['type'=>'purchase','status'=>'received','location_id'=>$location_id])
            $purchases= Transaction::with('contact')->select($select)->where(['type'=>'purchase','location_id'=>$location_id])
            ->where('ref_no','LIKE','%'.$term.'%')->get();

            return $purchases;
            
            return json_encode($result);
    }
}


// Modal
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Transaction;
use App\BusinessLocation;
use App\Currency;

class ExpensePurchase extends Model
{
    
    // protected $guarded = ['id'];
    protected $guarded=[];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    public $timestamps = false;


    public function transaction()
    {
        return $this->belongsTo(\App\Transaction::class, 'purchase_id');
    }

  
}
