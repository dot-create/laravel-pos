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
  __page_leave_confirmation('#add_expense_form');
</script>
@endsection