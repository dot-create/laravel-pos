@extends('layouts.app')
@section('title', __('request.pending_request_items'))

@section('content')

@php
	$custom_labels = json_decode(session('business.custom_labels'), true);
@endphp
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('request.Ready_To_Draft_List') <i class="fa fa-keyboard-o hover-q text-muted" aria-hidden="true" data-container="body" data-toggle="popover" data-placement="bottom" data-content="@include('purchase.partials.keyboard_shortcuts_details')" data-html="true" data-trigger="hover" data-original-title="" title=""></i></h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="box  box-solid ">
        <div class="box-body">
            <div class="row">
            <div class="col-sm-6">
                    <div class="form-group">
                        <label for="">{{__('request.customer')}}</label>
                        <input type="text" class="form-control" value="{{$request->contact->name}}" disabled>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="">{!! Form::label('ref_no', __('request.reference').':') !!}</label>
                        <input type="text" class="form-control" value="{{$request->request_reference}}" disabled>
                    </div>
                </div>
            </div>
            <hr>
            <form action="{{route('request.item.draft.update',$request->id)}}" method="POST">
                @csrf
                <h2 >Request Items</h2>
                <div class="row">
                    <div class="col-sm-12" style="overflow-x:scroll">
                        <table class="table table-bordered table-striped ajax_view" id="" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>@lang( 'product.product_name' )</th>
                                    <th>@lang( 'product.weight' )</th>
                                    <th>@lang( 'request.requested_quantity')</th>
                                    <th>@lang( 'request.avaliable_status' )</th>
                                    <th>@lang( 'request.current_stock' )</th>
                                    <th>@lang( 'request.avaliable_quantity' )</th>
                                    <th>Sell price wot / Selling price (Exc Tax)</th>
                                    <th>Product Selling tax type </th>
                                    <th>@lang( 'purchase.discount' )</th>
                                    <th>Tax</th>
                                    <th>@lang( 'request.subTotal' )</th>
                                    <th>@lang( 'request.status' )</th>
                                    <!-- <th><i class="fa fa-trash" aria-hidden="true"></i></th> -->
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $row_count=0;
                                    $total = 0;
                                @endphp
                                @foreach($items->where('status','!=','Rejected') as $item)
                                    @php
                                        $row_count++;
                                        $quantity=0;
                                        $freight=0;
                                        $ecom_fee_percentage_supplier=0;
                                        $formula_price_supplier=0;
                                        $unit_price_supplier=0;
                                        if($item->is_best_supplier1==1){
                                            $quantity=$item->quantity_supplier1;
                                            $freight=$item->freight_supplier1;
                                            $ecom_fee_percentage_supplier=$item->ecom_fee_percentage_supplier1;
                                            $formula_price_supplier=$item->formula_price_supplier1;
                                            $unit_price_supplier=$item->unit_price_supplier1;
                                        }else if($item->is_best_supplier2==1){
                                            $quantity=$item->quantity_supplier2;
                                            $freight=$item->freight_supplier2;
                                            $ecom_fee_percentage_supplier=$item->ecom_fee_percentage_supplier2;
                                            $formula_price_supplier=$item->formula_price_supplier2;
                                            $unit_price_supplier=$item->unit_price_supplier2;
                                        }
                                        else if($item->is_best_supplier3==1){
                                            $quantity=$item->quantity_supplier3;
                                            $freight=$item->freight_supplier3;
                                            $ecom_fee_percentage_supplier=$item->ecom_fee_percentage_supplier3;
                                            $formula_price_supplier=$item->formula_price_supplier3;
                                            $unit_price_supplier=$item->unit_price_supplier3;
                                        }
                                        else{
                                            $quantity=$item->quantity_supplier4;
                                            $freight=$item->freight_supplier4;
                                            $ecom_fee_percentage_supplier=$item->ecom_fee_percentage_supplier4;
                                            $formula_price_supplier=$item->formula_price_supplier4;
                                            $unit_price_supplier=$item->unit_price_supplier4;
                                        }
                                        $subtotal = $item->quantity * (float) ($item->sell_price_wot?? $item->variation->default_sell_price);
                                        $total += $subtotal;
                                    @endphp
                                    <input type="hidden" name="itemId[]" value="{{$item->id}}">
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
                                            
                                            {{ $item->weight_unit==null? 'N/A': $item->product->weight}}
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
                                            <input type="number" class="item-quantity" data-id="{{$row_count}}" value="{{$item->quantity}}" name="quantity[]" required>
                                            
                                        </td>
                                        <td>
                                            @php
                                            $remainQty=0;
                                            $getValues = $productUtil->getAvaliableQty($business_id,$item->variation_id,$request->business_location_id);
                                            $avaliabilityQty=$getValues['avaliabilityQty'];
                                            $currentStock=$getValues['stockOnHand'];
                                            $intransitQuantity=$getValues['intransitQuantity'];
                                            $stockOnHand=$currentStock;
                                            $status="Not Available";
                                            $remainingQty=$item->quantity;
                                            if($avaliabilityQty >= $item->quantity){
                                                $status="Avaliable";
                                            }
                                            else if($avaliabilityQty != 0 && $avaliabilityQty > 0){
                                                $status="Partial Avalaible";
                                                if($avaliabilityQty > $item->quantity){
                                                    $remainingQty= $avaliabilityQty - $item->quantity;
                                                }
                                                else{
                                                    $remainingQty= $item->quantity - $avaliabilityQty;
                                                }
                                            }
                                            else{
                                                $remainingQty=$item->quantity - $avaliabilityQty;
                                            }
                                            @endphp
                                            {{$status}}
                                        </td>
                                        <td>
                                            {{$currentStock}}
                                        </td>
                                        <td>
                                           
                                                {{$avaliabilityQty}}
                                            
                                            
                                        </td>
                                        <td>
                                            @php 
                                                $is_exl_tax= $item->product->tax_type== "exclusive"? true :false;
                                                $sell_price_inc_tax = $item->variation->default_sell_price * $currency_exchange_rate->rate;
                                                $sell_price_wot=$item->sell_price_wot;
                                                if(!$item->sell_price_wot){
                                                    $sell_price_wot=number_format($item->variation->default_sell_price, 2);
                                                }
                                            @endphp
                                            
                                            <input type="text" class="sell_price_wot base_unit_selling_price" id="unit-price-{{$row_count}}" value="{{$sell_price_wot}}"
                                             name="sell_price_wot[]" data-id="{{$row_count}}" required inputmode="decimal" pattern="^\d*\.?\d*$" oninput="this.value=this.value.replace(/[^0-9.]/g,'')">
                                        </td>
                                        <td>
                                            {{$is_exl_tax==true? 'Exclusive Tax' : 'Inclusive Tax'}}
                                        </td>
                                        <td>
                                            <input type="text" class="form-control item-discount" data-id="{{$row_count}}" name="discount[]">
                                            <select class="form-control discount-type" data-id="{{$row_count}}" name="discount_type[]" readonly>
                                                <option value="fixed">Fixed</option>
                                                <option value="percentage">Percentage</option>
                                            </select>
                                        </td>
                                        <td>
                                        <select name="tax_rate[]" class="form-control tax-rate" data-id="{{$row_count}}" style="width:75px">
                                            <option value="0" selected>0%</option>
                                            <option value="15">15%</option>
                                            <option value="18">18%</option>
                                        </select>
                                        </td>
                                        <input type="hidden" name="updated_subtotal[]" id="updated-subtotal-{{$row_count}}" value="{{$subtotal}}">
                                        <td class="subtotal-value" id="subtotal-{{$row_count}}" data-sub="{{$subtotal}}" data-discount-price="{{$subtotal}}" data-base-unit="{{ $item->sell_price_wot ?? $item->variation->default_sell_price }}">
                                            
                                            {{ number_format($subtotal, 2) }}
                                        </td>
                                        <td id="status-{{$row_count}}" data-sub="{{$item->status}}">
                                            @switch($item->status)
                                                @case('Rejected')
                                                    <span class="label bg-red">{{$item->status}}</span>
                                                @break
                                                @default()
                                                    <span class="label bg-info">{{$item->status}}</span>
                                                @break
                                            @endswitch
                                        </td>
                                        <!-- <td id="subtotal-{{$row_count}}">{{ number_format($subtotal, 2) }}</td> -->
                                        <?php $row_count++ ;?>
                                    </tr>
                                    @if($item->best_supplier_name != "No Best Supplier")
                                    <tr >
                                        <td colspan="12">
                                            <p>
                                                <b>Supplier</b>: <span>{{$item->best_supplier_name}}</span>
                                                <b>Freight</b>: <span>{{$freight}}</span>
                                                <b>Ecommerce Fees Percentage</b>: <span>{{$ecom_fee_percentage_supplier}}</span>
                                                <b style="color:green;font-weight:bold">Quantity</b>: <span style="color:green;font-weight:bold">{{$quantity}}</span>
                                                <b style="color:green;font-weight:bold">Price</b>: <span style="color:green;font-weight:bold">{{$formula_price_supplier}}</span>
                                                <b>Total Price</b>: <span>{{$item->total_price}}</span>
                                                <b>Delivery Time</b>: <span>{{$item->delivery_time}}</span>
                                            </p>
                                        </td>
                                    </tr>
                                    @endif
                                    <tr >
                                        <td colspan="7">
                                            <p>
                                                <b style="color:red">Item Note</b>: <span  ><input class="form-control" name="item_notes[]" type="text" value="{{$item->item_notes}}"></span>
                                                <br>
                                                <b style="color:red">Supply ref</b>: <input class="form-control" name="supply_ref[]" type="text" value="{{$item->supply_ref}}">
                                            </p>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @component('components.widget', ['class' => 'box-primary'])
                    
                    <div class="row">
                        <div class="col-md-3">
                            <label>@lang('purchase.discount_amount')</label>
                            <input type="text" id="overall_discount" class="form-control" value="0" name="overall_discount">
                        </div>
                        <div class="col-md-3">
                            <label>@lang('purchase.discount_type')</label>
                            <select id="overall_discount_type" class="form-control" name="overall_discount_type">
                                <option value="fixed">Fixed</option>
                                <option value="percentage">Percentage</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <b>@lang('purchase.discount'):</b> (-) <span id="discount_calculated_amount">0</span>
                        </div>
                        <div class="col-md-3">
                            <input type="hidden" name="final_totalInput" value="{{number_format($total, 2)}}" id="final_total_input">
                            <b>@lang('request.final_total'):</b> <span id="final_total">{{ number_format($total, 2) }}</span>
                        </div>
                    </div>
                @endcomponent
                <button type="submit" class="btn btn-success">Save as DRAFT</button>
            </form>
        </div>
    </div>
</section>

<!-- /.content -->
@endsection

@section('javascript')
	<!-- <script src="{{ asset('js/purchase.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script> -->
	<!-- <script type="text/javascript">
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
	</script> -->
    <script>
    function updateSubtotal(rowId) {
        const quantity = parseInt($(`.item-quantity[data-id="${rowId}"]`).val()) || 1;
        const baseUnitPrice =  parseFloat($(`.sell_price_wot[data-id="${rowId}"]`).val()) || 0;
        const discount = parseFloat($(`.item-discount[data-id="${rowId}"]`).val()) || 0;
        const discountType = $(`.discount-type[data-id="${rowId}"]`).val();
        const taxRate = parseFloat($(`.tax-rate[data-id="${rowId}"]`).val()) || 0;

        // Apply discount
        let discountedUnitPrice = baseUnitPrice;
        if (discountType === 'percentage') {
            discountedUnitPrice = baseUnitPrice - (baseUnitPrice * (discount / 100));
        } else if (discountType === 'fixed') {
            discountedUnitPrice = baseUnitPrice - discount;
        }

        // Prevent negative price
        if (discountedUnitPrice < 0) discountedUnitPrice = 0;

        // Subtotal before tax
        let subtotalBeforeTax = discountedUnitPrice * quantity;

        // Apply tax
        let taxAmount = (subtotalBeforeTax * taxRate) / 100;
        let updatedSubtotal = subtotalBeforeTax + taxAmount;

        // Update UI
        $('#updated-subtotal-' + rowId).val(updatedSubtotal.toFixed(2));
        $('#subtotal-' + rowId)
            .text(updatedSubtotal.toFixed(2))
            .attr('data-sub', updatedSubtotal.toFixed(2));
    }

    $(document).on('input change', '.item-discount, .discount-type, .tax-rate , #overall_discount , #overall_discount_type, .sell_price_wot,.item-quantity', function () {
        const rowId = $(this).data('id');
        updateSubtotal(rowId);
        recalculateFinalTotal()
        getGloabelDiscount();
    });
    $('.tax-rate').change(function () {
        let rowId = $(this).data('id');
        let taxRate = parseFloat($(this).val()) || 0;
        
        // Get quantity from the Blade table
        let quantity = parseFloat($('#quantity-' + rowId).text()) || 1;

        // Get the unit price after discount from data attribute (set in discount logic)
        let discountedUnitPrice = parseFloat($('#subtotal-' + rowId).attr('data-unit-price')) || 0;

        // Calculate tax amount for all items
        let taxAmount = ((discountedUnitPrice * taxRate) / 100) * quantity;

        // Calculate updated subtotal including tax
        let updatedSubtotal = (discountedUnitPrice * quantity) + taxAmount;

        // Update the hidden input and UI
        $('#updated-subtotal-' + rowId).val(updatedSubtotal.toFixed(2));
        $('#subtotal-' + rowId)
            .text(updatedSubtotal.toFixed(2))
            .attr('data-sub', updatedSubtotal.toFixed(2));
        recalculateFinalTotal();
        getGloabelDiscount();
    });
    function getGloabelDiscount(){
        let discount = parseFloat($('#overall_discount').val());
        let discountType = $(`#overall_discount_type`).val();
        let finaltotal=document.getElementById('final_total').innerText;
        finaltotal=finaltotal.replace(/,/g, '');
        let totalAfterDiscount = parseFloat(finaltotal);
        let discountPrice= parseFloat(discount);
        if (discountType === 'percentage') {
            discountPrice = parseFloat(finaltotal * (discount / 100));
            totalAfterDiscount = parseFloat(finaltotal - (finaltotal * (discount / 100)));
        } else if (discountType === 'fixed') {
            totalAfterDiscount = parseFloat(finaltotal - discount);
        }
        console.log('totalAfterDiscount',totalAfterDiscount);
        document.getElementById('final_total').innerText = totalAfterDiscount.toFixed(2);
        document.getElementById('discount_calculated_amount').innerText = discountPrice.toFixed(2);
        document.getElementById('final_total_input').value = totalAfterDiscount.toFixed(2);
    }
    function recalculateFinalTotal() {
        console.log('recalculatefinal total');
        let newTotal = 0;

        document.querySelectorAll('.subtotal-value').forEach(el => {
            let value = el.innerText;
            let cleaned = value.replace(/,/g, ''); // "2983.90"
            value = parseFloat(cleaned); // 2983.90
            if (!isNaN(value)) {
                newTotal += value;
            }
        });
        console.log('new value',newTotal);
        // Update grand total in UI
        document.getElementById('final_total').innerText = newTotal.toFixed(2);

        // Update hidden input value for form submission
        document.getElementById('final_total_input').value = newTotal.toFixed(2);
    }
</script>
	@include('purchase.partials.keyboard_shortcuts')
@endsection
