@extends('layouts.app')
@section('title', __('request.add_request'))

@section('content')

@php
	$custom_labels = json_decode(session('business.custom_labels'), true);
@endphp
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('request.add_request') <i class="fa fa-keyboard-o hover-q text-muted" aria-hidden="true" data-container="body" data-toggle="popover" data-placement="bottom" data-content="@include('purchase.partials.keyboard_shortcuts_details')" data-html="true" data-trigger="hover" data-original-title="" title=""></i></h1>
</section>

<!-- Main content -->
<section class="content">

	<!-- Page level currency setting -->
	<input type="hidden" id="p_code" value="{{$currency_details->code}}">
	<input type="hidden" id="p_symbol" value="{{$currency_details->symbol}}">
	<input type="hidden" id="p_thousand" value="{{$currency_details->thousand_separator}}">
	<input type="hidden" id="p_decimal" value="{{$currency_details->decimal_separator}}">

	@include('layouts.partials.error')

	{!! Form::open(['url' => action('PurchaseController@storeRequest'), 'method' => 'post', 'id' => 'add_purchase_form', 'files' => true ]) !!}
	@component('components.widget', ['class' => 'box-primary'])
		<div class="row">
			<div class="@if(!empty($default_purchase_status)) col-sm-4 @else col-sm-3 @endif">
				<div class="form-group">
					{!! Form::label('supplier_id', __('contact.customer') . ':*') !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-user"></i>
						</span>
						{!! Form::select('contact_id', [], null, ['class' => 'form-control requestPage', 'placeholder' => __('messages.please_select'), 'required', 'id' => 'supplier_id']); !!}
						<span class="input-group-btn">
							<button type="button" class="btn btn-default bg-white btn-flat add_new_supplier" data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
						</span>
					</div>
				</div>
				<strong>
					@lang('business.address'):
				</strong>
				<div id="supplier_address_div"></div>
			</div>
			<div class="col-sm-4">
				<div class="form-group">
					{!! Form::label('ref_no', __('request.reference').':') !!}
					{!! Form::text('ref_no', null, ['class' => 'form-control','required'=>true]); !!}
				</div>
			</div>
	
			@if(count($business_locations) == 1)
				@php 
					$default_location = current(array_keys($business_locations->toArray()));
					$search_disable = false; 
				@endphp
			@else
				@php $default_location = null;
				$search_disable = true;
				@endphp
			@endif
			<div class="col-sm-3">
				<div class="form-group">
					{!! Form::label('location_id', __('purchase.business_location').':*') !!}
					@show_tooltip(__('tooltip.purchase_location'))
					{!! Form::select('location_id', $business_locations, $default_location, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required'], $bl_attributes); !!}
				</div>
			</div>

			<div class="col-sm-3">
				<div class="form-group">
					{!! Form::label('contact_person_id', __('request.contact_person').':') !!}
					{!! Form::select('contact_person_id', [], null, ['class' => 'form-control select2', 'id' => 'contact_person_id', 'placeholder' => __('messages.please_select')]) !!}
				</div>
			</div>

			<div class="col-sm-3">
				<div class="form-group">
					{!! Form::label('recieve_location_id','Foreign Business Location:*') !!}
					@show_tooltip(__('request.foreign_business_location'))
					{!! Form::select('recieve_location_id', $business_locations, $default_location, ['class' => 'form-control select2 ', 'placeholder' => __('messages.please_select'), 'required'], $bl_attributes); !!}
				</div>
				<span id="location_error" style="color: red; display: none;">
					Foreign Business Location must be different from Business Location.
				</span>
			</div>


	@endcomponent

	@component('components.widget', ['class' => 'box-primary'])
		<div class="row">
			<!-- <div class="col-sm-2 text-center">
				<button type="button" class="btn btn-primary btn-flat" data-toggle="modal" data-target="#import_purchase_products_modal">@lang('product.import_products')</button>
			</div> -->
			<div class="col-sm-8">
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-search"></i>
						</span>
						{!! Form::text('search_product', null, ['class' => 'form-control mousetrap', 'id' => 'search_product', 'placeholder' => __('lang_v1.search_product_placeholder'), 'disabled' => $search_disable]); !!}
					</div>
				</div>
			</div>
			<div class="col-sm-2">
				<div class="form-group">
					<button tabindex="-1" type="button" class="btn btn-link btn-modal"data-href="{{action('ProductController@quickAdd')}}" 
            	data-container=".quick_add_product_modal"><i class="fa fa-plus"></i> @lang( 'product.add_new_product' ) </button>
				</div>
			</div>
		</div>
		@php
			$hide_tax = '';
			if( session()->get('business.enable_inline_tax') == 0){
				$hide_tax = 'hide';
			}
		@endphp
		<div class="row">
			<div class="col-sm-12">
				<div class="table-responsive">
					<table class="table table-condensed table-bordered table-th-info text-center table-striped" id="request_entry_table">
						<thead>
							<tr>
								<th>#</th>
								<th>@lang( 'product.product_name' )</th>
								<th>@lang( 'product.weight' )</th>
								<th>@lang( 'purchase.quantity' )</th>
								<th>@lang( 'purchase.unit_cost' )</th>
								<th>@lang( 'purchase.margin' )</th>
								<th>@lang( 'purchase.SellingPrice(Excluding_Tax)' )</th>
								<th class="{{$hide_tax}}">@lang( 'purchase.SubTotal(Including_Tax)' )</th>
								<th>@lang( 'request.current_stock' )</th>
								<th>@lang( 'request.avaliable_qty' )</th>
								<th>@lang( 'request.intrasit' )</th>
								<th>@lang( 'request.status' )</th>
								<th><i class="fa fa-trash" aria-hidden="true"></i></th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
				<hr/>
				<div class="pull-right col-md-5">
					<table class="pull-right col-md-12">
						<tr>
							<th class="col-md-7 text-right">@lang( 'lang_v1.total_items' ):</th>
							<td class="col-md-5 text-left">
								<span id="total_quantity" class="display_currency" data-currency_symbol="false"></span>
							</td>
						</tr>
						<tr class="hide">
							<th class="col-md-7 text-right">@lang( 'purchase.total_before_tax' ):</th>
							<td class="col-md-5 text-left">
								<span id="total_st_before_tax" class="display_currency"></span>
								<input type="hidden" id="st_before_tax_input" value=0>
							</td>
						</tr>
						<tr>
							<th class="col-md-7 text-right">@lang( 'purchase.net_total_amount' ):</th>
							<td class="col-md-5 text-left">
								<span id="total_subtotal" class="display_currency"></span>
								<!-- This is total before purchase tax-->
								<input type="hidden" id="total_subtotal_input" value=0  name="total_before_tax">
							</td>
						</tr>
					</table>
				</div>

				<input type="hidden" id="row_count" value="0">
			</div>
		</div>
        <div class="row">
            <div class="col-sm-12">
                <button type="button" id="submit_purchase_form" class="btn btn-primary pull-right btn-flat">@lang('messages.save')</button>
            </div>
		</div>
	@endcomponent
{!! Form::close() !!}
</section>
<!-- quick product modal -->
<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>
<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
	@include('contact.create', ['quick_add' => true])
</div>

@include('purchase.partials.import_purchase_products_modal')
<!-- /.content -->
@endsection

@section('javascript')
	<script src="{{ asset('js/purchase.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
	<script type="text/javascript">
		$(document).ready( function(){
      		__page_leave_confirmation('#add_purchase_form');
      		$('.paid_on').datetimepicker({
                format: moment_date_format + ' ' + moment_time_format,
                ignoreReadonly: true,
            });
    	});
    	$(document).on('change', '.payment_types_dropdown, #location_id', function(e) {
		    var default_accounts = $('select#location_id').length ? 
		                $('select#location_id')
		                .find(':selected')
		                .data('default_payment_accounts') : [];
		    var payment_types_dropdown = $('.payment_types_dropdown');
		    var payment_type = payment_types_dropdown.val();
		    var payment_row = payment_types_dropdown.closest('.payment_row');
	        var row_index = payment_row.find('.payment_row_index').val();

	        var account_dropdown = payment_row.find('select#account_' + row_index);
		    if (payment_type && payment_type != 'advance') {
		        var default_account = default_accounts && default_accounts[payment_type]['account'] ? 
		            default_accounts[payment_type]['account'] : '';
		        if (account_dropdown.length && default_accounts) {
		            account_dropdown.val(default_account);
		            account_dropdown.change();
		        }
		    }

		    if (payment_type == 'advance') {
		        if (account_dropdown) {
		            account_dropdown.prop('disabled', true);
		            account_dropdown.closest('.form-group').addClass('hide');
		        }
		    } else {
		        if (account_dropdown) {
		            account_dropdown.prop('disabled', false); 
		            account_dropdown.closest('.form-group').removeClass('hide');
		        }    
		    }
		});
	</script>
	<script>
	$(document).ready(function () {
    function checkLocationConflict() {
        let businessLocation = $('#location_id').val();
        let foreignBusinessLocation = $('#recieve_location_id').val();
        if (businessLocation && foreignBusinessLocation && businessLocation === foreignBusinessLocation) {
            $('#location_error').show();
        } else {
            $('#location_error').hide();
        }
    }

    $('#location_id, #recieve_location_id').on('change', checkLocationConflict);
});
function get_purchase_entry_row(product_id, variation_id,requestPage=true) {
    if (product_id) {
        var row_count = $('#row_count').val();
        var location_id = $('#location_id').val();
        var supplier_id = $('#supplier_id').val();
        var data = { 
            product_id: product_id, 
            row_count: row_count, 
            variation_id: variation_id,
            location_id: location_id,
            supplier_id: supplier_id,
            requestPage: requestPage
        };

        if ($('#is_purchase_order').length) {
            data.is_purchase_order = true;
        }
        $.ajax({
            method: 'POST',
            url: '/purchases/get_purchase_entry_row',
            dataType: 'html',
            data: data,
            success: function(result) {
                $("input#p_symbol").remove();
                append_purchase_lines(result, row_count,false,requestPage);
            },
        });
    }
}
</script>

<script>
$(document).ready(function () {
    $('#location_id').on('change', function () {
        let locationId = $(this).val();
        let $contactDropdown = $('#contact_person_id');

        $contactDropdown.empty().append(`<option value="">Loading...</option>`);

        if (locationId) {
            $.ajax({
                url: '/contact-persons-by-location/' + locationId,
                method: 'GET',
                success: function (data) {
					console.log(data);
                    $contactDropdown.empty().append('<option value="">{{ __("messages.please_select") }}</option>');
                    data.forEach(function (person) {
                        $contactDropdown.append(`<option value="${person.id}">${person.representative_name}</option>`);
                    });
                },
                error: function () {
                    $contactDropdown.empty().append('<option value="">{{ __("messages.something_went_wrong") }}</option>');
                }
            });
        } else {
            $contactDropdown.empty().append('<option value="">{{ __("messages.please_select") }}</option>');
        }
    });
});
</script>


	@include('purchase.partials.keyboard_shortcuts')
@endsection
