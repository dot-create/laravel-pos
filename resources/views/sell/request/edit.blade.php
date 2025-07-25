@php
use App\Utils\ProductUtil;
@endphp
@extends('layouts.app')
@section('title', __('request.pending_request_items'))

@section('content')

@php
	$custom_labels = json_decode(session('business.custom_labels'), true);
@endphp
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>{{__('request.edit')}} </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="box  box-solid ">
        <div class="box-body">
            <div class="row">
            <form action="{{route('updateRequest',$request->id)}}" method="POST">
            @csrf
            <div class="col-sm-6">
                    <div class="form-group">
                        <label for="">{{__('request.customer')}}</label>
                        <input type="text" class="form-control" value="{{$request->contact->name}}" disabled>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="">{!! Form::label('ref_no', __('request.reference').':') !!}</label>
                        <input type="text" name="ref_no" class="form-control" value="{{$request->request_reference}}" disabled>
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
			<div class="col-sm-6">
				<div class="form-group">
					{!! Form::label('location_id', __('purchase.business_location').':*') !!}
					@show_tooltip(__('tooltip.purchase_location'))
					{!! Form::select('location_id', $business_locations, $request->business_location_id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required'], $bl_attributes); !!}
				</div>
			</div>
			<div class="col-sm-6">
				<div class="form-group">
					{!! Form::label('recieve_location_id','Foreign Business Location:*') !!}
					@show_tooltip(__('request.foreign_business_location'))
					{!! Form::select('recieve_location_id', $business_locations, $request->foreign_business_location_id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required'], $bl_attributes); !!}
				</div>
			</div>
            
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="">{{__('request.Description')}}</label>
                        <textarea name="description" id="" class="form-control" rows="3" cols="20"></textarea>
                    </div>
                </div>
                <!-- <div class="col-sm-6">
                    <div class="form-group">
                        <label for="">{{__('request.status')}}</label>
                        <select name="status" id="" class="form-control">
                            <option value="" selected disabled>Select status</option>
                            @foreach($orderStatuses as $status)
                                <option value="{{$status}}" {{$request->status==$status? 'selected':''}}>{{$status}}</option>
                            @endforeach
                        </select>
                    </div>
                </div> -->
                <div class="col-sm6">
                    <button type="submit" class="btn btn-success">Update Request</button>
                </div>
                </form>
            </div>
            <hr>
            <h2 >Request Items</h2>
            <div class="row">
                <div class="col-sm-12">
                    <table class="table table-bordered table-striped ajax_view" id="" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>@lang( 'product.product_name' )</th>
                                <th>@lang( 'product.weight' )</th>
                                <th>@lang( 'request.requested_quantity' )</th>
                                <th>@lang( 'request.avaliable_stock' )</th>
                                <th>@lang( 'request.status' )</th>
                                <th>@lang( 'request.action' )</th>
                                <!-- <th><i class="fa fa-trash" aria-hidden="true"></i></th> -->
                            </tr>
                        </thead>
                        <tfoot>
                            @php
                                $row_count=0;
                            @endphp
                            @foreach($items as $item)
                                @php
                                    $row_count=$row_count+1;
                                @endphp
                                    <tr @if(!empty($purchase_order_line)) data-purchase_order_id="{{$purchase_order_line->transaction_id}}" @endif @if(!empty($purchase_requisition_line)) data-purchase_requisition_id="{{$purchase_requisition_line->transaction_id}}" @endif>
                                        <td><span class="sr_number">{{$row_count}}</span></td>
                                        <td>
                                            
                                            {{ $item->product->name }} ({{$item->variation->sub_sku}})
                                            @if( $item->product->type == 'variable' )
                                                <br/>
                                                (<b>{{ $item->variation->product_variation->name }}</b> : {{ $item->variation->name }})
                                            @endif
                                        </td>
                                        <td>
                                        
                                            {{ $item->product->weight==null? 'N/A': $item->product->weight}}
                                        </td>
                                        <td>
                                            @if(!empty($purchase_order_line))
                                                {!! Form::hidden('purchases[' . $row_count . '][purchase_order_line_id]', $purchase_order_line->id ); !!}
                                            @endif
                                            @php
                                                $check_decimal = 'false';
                                                if($item->product->unit->allow_decimal == 0){
                                                    $check_decimal = 'true';
                                                }
                                                $quantity_value = !empty($purchase_order_line) ? $purchase_order_line->quantity : 1;

                                                $quantity_value = !empty($purchase_requisition_line) ? $purchase_requisition_line->quantity - $purchase_requisition_line->po_quantity_purchased : $quantity_value;
                                                $max_quantity = !empty($purchase_order_line) ? $purchase_order_line->quantity - $purchase_order_line->po_quantity_purchased : 0;

                                                $max_quantity = !empty($purchase_requisition_line) ? $purchase_requisition_line->quantity - $purchase_requisition_line->po_quantity_purchased : $max_quantity;

                                                $quantity_value = !empty($imported_data) ? $imported_data['quantity'] : $quantity_value;
                                            @endphp
                                            <span>{{$item->quantity}}</span>
                                            
                                        </td>
                                        <td>
                                        @if($item->product->enable_stock == 1)
                                                @php
                                                $productUtil= new ProductUtil();
                                                $getValues=$productUtil->getAvaliableQty($business_id,$item->variation_id,$item->request->business_location_id);
                                                $avaliabilityQty=$getValues['avaliabilityQty'];
                                                @endphp
                                                <small>{{$avaliabilityQty}}</small>
                                        @endif
                                        </td>
                                        <td>
                                            @switch($item->status)
                                                @case('Rejected')
                                                    <span class="label bg-red">{{$item->status}}</span>
                                                @break
                                                
                                                @default()
                                                    <span class="label bg-info">{{$item->status}}</span>
                                                @break
                                            @endswitch
                                        </td>
                                        <td>
                                            @switch($item->status)
                                                @case('Rejected')
                                                    <a href="{{route('request.item.edit',$item->id)}}" class="btn btn-primary">Edit</a>
                                                @break
                                                @case('Pending')
                                                    <a href="{{route('request.item.edit',$item->id)}}" class="btn btn-primary">Edit</a>
                                                    <a href="{{route('request.item.reject',$item->id)}}" class="btn btn-danger">Reject</a>
                                                @break
                                                @default()
                                                    
                                                @break
                                            @endswitch
                                        </td>
                                        <?php $row_count++ ;?>

                                        <!-- <td><i class="fa fa-times remove_purchase_entry_row text-danger" title="Remove" style="cursor:pointer;"></i></td> -->
                                    </tr>
                            @endforeach
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
    </div>
</section>
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
	@include('purchase.partials.keyboard_shortcuts')
@endsection
