@extends('layouts.app')
@section('title', __('request.seller_form_to_edit_create_customer_quotation'))

@section('content')

@php
	$custom_labels = json_decode(session('business.custom_labels'), true);
@endphp
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('request.seller_form_to_edit_create_customer_quotation') <i class="fa fa-keyboard-o hover-q text-muted" aria-hidden="true" data-container="body" data-toggle="popover" data-placement="bottom" data-content="@include('purchase.partials.keyboard_shortcuts_details')" data-html="true" data-trigger="hover" data-original-title="" title=""></i></h1>
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
            <form action="{{route('request.item.updateDraftquote',$request->id)}}" method="POST">
                @csrf
                <h2 >Request Items</h2>
                <div class="row">
                    <div class="col-sm-12"  style="overflow-x:scroll">
                            <table class="table table-responsive table-bordered table-striped ajax_view" id="" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>@lang( 'product.product_name' )</th>
                                        <th>@lang( 'product.sku' )</th>
                                        <th>@lang( 'request.avaliable_stock' )</th>
                                        <th>@lang( 'request.current_stock' )</th>
                                        <th>@lang( 'request.required_quantity' )</th>
                                        <!-- <th>@lang( 'request.selling_price' )</th> -->
                                        <th>Sell price wot / Selling price (Exc Tax)</th>
                                        <th>Product Selling tax type </th>
                                        <th>@lang( 'request.discount' )</th>
                                        <th>Subtotal (After Discount)</th>
                                        <th>@lang( 'request.tax' )</th>
                                        <th>Final Subtotal (After Tax)</th>
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
                                            $quantity = is_numeric($item->quantity) ? $item->quantity : 0;
                                            $sellPrice = is_numeric($item->sell_price_wot) 
                                                ? $item->sell_price_wot 
                                                : (is_numeric($item->variation->default_sell_price) ? $item->variation->default_sell_price : 0);

                                            $subtotal = is_numeric($item->subtotal_wd) 
                                                ? $item->subtotal_wd 
                                                : $quantity * $sellPrice;

                                            $total += $subtotal;

                                            // Later when displaying the total:
                                            // echo number_format($total, 2);
                                        @endphp
                                        <input type="hidden" name="itemId[]" value="{{$item->id}}">
                                        @php
                                        $getValues = $productUtil->getAvaliableQty($business_id,$item->variation_id,$request->business_location_id);
                                        $avaliabilityQty=$getValues['avaliabilityQty'];
                                        $currentStock=$getValues['stockOnHand'];
                                        $intransitQuantity=$getValues['intransitQuantity'];
                                        @endphp
                                        <tr >
                                            <td><span class="sr_number">{{$row_count}}</span></td>
                                            <td>
                                                
                                                {{ $item->product->name }} ({{$item->variation->sub_sku}})
                                                @if( $item->product->type == 'variable' )
                                                    <br/>
                                                    (<b>{{ $item->variation->product_variation->name }}</b> : {{ $item->variation->name }})
                                                @endif
                                            </td>
                                            <td>
                                                {{ $item->product->sku }} ({{$item->variation->sub_sku}})
                                            </td>
                                            <td>
                                                <span>{{$avaliabilityQty}}</span>
                                            </td>
                                            <td>                                      
                                                {{$currentStock}}
                                            </td>
                                            <td class="quantity-{{$row_count}}">
                                            <input type="number" class="item-quantity" data-id="{{$row_count}}" value="{{$item->quantity}}" name="quantity[]" required>
                                                
                                            </td>
                                            <!-- <td>
                                                
                                                <span>{{$item->suggested_sell_price_USD_wot}}</span>
                                                
                                            </td> -->
                                            <td>
                                            @php 
                                                $is_exl_tax= $item->product->tax_type== "exclusive"? true :false;
                                                $sell_price_inc_tax = $item->variation->default_sell_price * $currency_exchange_rate->rate;
                                                $sell_price_wot=$item->sell_price_wot;
                                                if(!$item->sell_price_wot){
                                                    $sell_price_wot=number_format($item->variation->default_sell_price, 2);
                                                }
                                            @endphp
                                            
                                            <input type="text" class="sell_price_wot" value="{{$sell_price_wot}}" name="sell_price_wot[]" data-id="{{$row_count}}" required
                                            inputmode="decimal" pattern="^\d*\.?\d*$" oninput="this.value=this.value.replace(/[^0-9.]/g,'')">
                                        </td>
                                        <td>
                                            {{$is_exl_tax==true? 'Exclusive Tax' : 'Inclusive Tax'}}
                                        </td>
                                            <td>
                                                    <input type="string" class="item-discount" data-id="{{$row_count}}" value="{{$item->discount}}" name="discount[]">
                                                    <select class="form-control discount-type" data-id="{{$row_count}}" name="discount_type[]">
                                                        <option value="fixed" {{$item->discount_type=="fixed"? "selected":""}}>Fixed</option>
                                                        <option value="percentage" {{$item->discount_type=="percentage"? "selected":""}}>Percentage</option>
                                                    </select>
                                            </td>
                                            <input type="hidden" name="updated_subtotal[]" id="updated-subtotal-{{$row_count}}" value=" {{ number_format($item->subtotal_wd_tax, 2) }}">
                                            
                                            <td  id="subtotal-{{$row_count}}" data-sub="{{ number_format($item->subtotal_wd_tax, 2) }}" data-discount-price="{{ number_format($item->subtotal_wd_tax, 2) }}" data-base-unit="{{ $item->sell_price_wot ?? $item->variation->default_sell_price }}">
                                                @if($item->subtotal_wd)
                                                    {{ number_format($item->subtotal_wd, 2) }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>
                                                <select name="tax_rate[]" class="form-control tax-rate" data-id="{{$row_count}}" style="width:75px">
                                                    <option value="0" {{$item->tax==0? 'selected' :''}}>0%</option>
                                                    <option value="15" {{$item->tax==15? 'selected' :''}}>15%</option>
                                                    <option value="18" {{$item->tax==18? 'selected' :''}}>18%</option>
                                                </select>
                                            </td>
                                            
                                            <td class="subtotal-value subtotal-wt-{{$row_count}}">
                                                {{ number_format($item->subtotal_wd_tax, 2) }}
                                            </td>
                                            <td>
                                                @php
                                                    $variationdetail=$item->variation->variation_location_details->where('location_id',$item->request->business_location_id)->pluck('qty_available');
                                                    $qty=$variationdetail[0]?? 0;
                                                    $status="Not Available";
                                                    $remainingQty=$item->quantity;
                                                    if($qty >= $item->quantity){
                                                        $status="Avaliable";
                                                    }
                                                    else if($qty != 0 && $qty > 0){
                                                        $status="Partial Avalaible";
                                                        $remainingQty= $qty - $item->quantity;
                                                    }
                                                @endphp
                                                <span>{{$status}}</span>
                                            </td>
                                            <!-- <td id="subtotal-{{$row_count}}">{{ number_format($subtotal, 2) }}</td> -->
                                            <?php $row_count++ ;?>
                                        </tr>
                                        <tr >
                                            @if($item->best_supplier_name != "No Best Supplier")
                                                <td colspan="12">
                                                    <p>
                                                        <b>Supplier</b>: <span>{{$item->best_supplier_name}}</span>
                                                        <b style="color:green;font-weight:bold">Quantity</b>: <span style="color:green;font-weight:bold">{{$quantity}}</span>
                                                        <b>Freight</b>: <span>{{$freight}}</span>
                                                        <b>Ecommerce Fees Percentage</b>: <span>{{$ecom_fee_percentage_supplier}}</span>
                                                        <b style="color:green;font-weight:bold">Price</b>: <span style="color:green;font-weight:bold">{{$formula_price_supplier}}</span>
                                                        <b>Total Price</b>: <span >{{$item->total_price}}</span>
                                                        <b>Delivery Time</b>: <span>{{$item->delivery_time}}</span>
                                                    </p>
                                                </td>
                                            @endif
                                            <tr >
                                                <td colspan="12">
                                                    <p>
                                                        <b style="color:red">Sell Line Note</b>: <span  ><textarea class="form-control"  rows="3" cols="40"  name="sell_line_note[]" type="text">{{$item->sell_line_note}}</textarea></span>
                                                        <b style="color:red">Item Note</b>: <span  ><textarea class="form-control"  rows="3" cols="40"  name="item_notes[]" type="text">{{$item->item_notes}}</textarea></span>
                                                        <br>
                                                        <b style="color:red">Supply ref</b>: <input class="form-control" name="supply_ref[]" type="text" value="{{$item->supply_ref}}">
                                                    </p>
                                                </td>
                                            </tr>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        
                    </div>
                </div>
                @component('components.widget', ['class' => 'box-primary'])
                
                    <div class="row">
                        <div class="col-md-2">
                            <label>@lang('purchase.discount_amount')</label>
                            <input type="text" id="overall_discount" class="form-control" value="{{$request->discount?? 0}}" name="overall_discount" >
                        </div>
                        <div class="col-md-2">
                            <label>@lang('purchase.discount_type')</label>
                            <select id="overall_discount_type" class="form-control" name="overall_discount_type" >
                                <option value="fixed" {{$request->discount_type=="fixed"? 'selected' :''}}>Fixed</option>
                                <option value="percentage" {{$request->discount_type=="percentage"? 'selected' :''}}>Percentage</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <b>@lang('purchase.discount'):</b> (-) <span id="discount_calculated_amount">{{$request->discount?? 0}}</span>
                        </div>
                        <div class="col-md-2">
                            <label>@lang('purchase.tax')</label>
                            <select name="overall_tax" id="overall_tax" class="form-control" style="width:75px">
                                <option value="0" {{$request->tax=="0"? 'selected' : ''}}>0%</option>
                                <option value="15" {{$request->tax=="15"? 'selected' : ''}}>15%</option>
                                <option value="18" {{$request->tax=="18"? 'selected' : ''}}>18%</option>
                            </select>
                        </div>
                        <div class="col-md-2" >
                          
                            <input type="hidden" name="final_totalInput" value="{{number_format($finalTotal, 2)}}" id="final_total_input">
                            <b>@lang('request.final_total'):</b> <span id="final_total">{{number_format($finalTotal, 2)}}</span>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-12">
                        <b style="color:red">Request Note</b>: <span  >
                            <textarea name="globale_note" class="form-control" rows="10" >{{$request->request_note}}</textarea>
                        </div>
                    </div>
                @endcomponent
                <button type="submit" class="btn btn-success">Save Quote</button>
            </form>
        </div>
    </div>
</section>

<!-- /.content -->
@endsection

@section('javascript')
    <script>
    // $(document).on('input change', '.item-discount, .discount-type, #overall_discount, #overall_discount_type', function() {
    //     let total = 0;
    //     debugger;
    //     $('.item-discount').each(function() {
    //         let id = $(this).data('id');
    //         let subTotal="{{$subtotal}}";
    //         let discount = parseFloat($(this).val()) || 0;
    //         let discountType = $('.discount-type[data-id="' + id + '"]').val();
    //         let price = parseFloat($('#subtotal-' + id).data('sub')) || 0;
            
    //         let discountedPrice = discountType === 'percentage' ? price - (price * discount / 100) : price - discount;
    //         $('#subtotal-' + id).text(discountedPrice.toFixed(2));
    //         total += discountedPrice;
    //     });
        
    //     let overallDiscount = parseFloat($('#overall_discount').val()) || 0;
    //     let overallDiscountType = $('#overall_discount_type').val();
    //     let totalAfterDiscount = overallDiscountType === 'percentage' ? total - (total * overallDiscount / 100) : total - overallDiscount;
        
    //     $('#discount_calculated_amount').text(overallDiscount);
    //     $('#final_total').text(totalAfterDiscount.toFixed(2));
    //     $('#final_totalInput').val(totalAfterDiscount.toFixed(2));
    // });
</script>

<script>
    function updateSubtotal(rowId) {
        const quantity = parseInt($(`.item-quantity[data-id="${rowId}"]`).val()) || 1;

        const baseUnitPrice = parseFloat($(`.sell_price_wot[data-id="${rowId}"]`).val()) || 0;
        const discount = parseFloat($(`.item-discount[data-id="${rowId}"]`).val()) || 0;
        const discountType = $(`.discount-type[data-id="${rowId}"]`).val();
        const taxRate = parseFloat($(`.tax-rate[data-id="${rowId}"]`).val()) || 0;
        console.log('baseUnitPrice',baseUnitPrice);
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
        let updatedSubtotal = subtotalBeforeTax;
        let updatedSubtotalwt = subtotalBeforeTax + taxAmount;
        $('.subtotal-wt-'+rowId).text(updatedSubtotalwt.toFixed(2));
        console.log('updatedSubtotal',updatedSubtotal);
        // Update UI
        $('#updated-subtotal-' + rowId).val(updatedSubtotal.toFixed(2));
        $('#subtotal-' + rowId)
            .text(updatedSubtotal.toFixed(2))
            .attr('data-sub', updatedSubtotal.toFixed(2));
            console.log('subtotal',$('#subtotal-' + rowId).text(),rowId);
    }

    $(document).on('input change', '.item-discount, .discount-type, .tax-rate , #overall_discount , #overall_discount_type, #overall_tax, .sell_price_wot,.item-quantity', function () {
        const rowId = $(this).data('id');
        console.log('rowId',rowId);
        updateSubtotal(rowId);
        recalculateFinalTotal()
        getGloabelDiscount()
        calculateGloableTax()
    });
    // $('.tax-rate').change(function () {
    //     let rowId = $(this).data('id');
    //     let taxRate = parseFloat($(this).val()) || 0;
        
    //     // Get quantity from the Blade table
    //     let quantity = parseFloat($('#quantity-' + rowId).text()) || 1;

    //     // Get the unit price after discount from data attribute (set in discount logic)
    //     let discountedUnitPrice = parseFloat($('#subtotal-' + rowId).attr('data-unit-price')) || 0;

    //     // Calculate tax amount for all items
    //     let taxAmount = ((discountedUnitPrice * taxRate) / 100) * quantity;

    //     // Calculate updated subtotal including tax
    //     let updatedSubtotal = (discountedUnitPrice * quantity) + taxAmount;

    //     // Update the hidden input and UI
    //     $('#updated-subtotal-' + rowId).val(updatedSubtotal.toFixed(2));
    //     $('#subtotal-' + rowId)
    //         .text(updatedSubtotal.toFixed(2))
    //         .attr('data-sub', updatedSubtotal.toFixed(2));
    //     recalculateFinalTotal();
    //     getGloabelDiscount();
    // });
    function calculateGloableTax(){
        let taxRate = parseFloat($('#overall_tax').val()) || 0;

        let finalTotal= $("input[name='final_totalInput']").val();
        finalTotal=parseFloat(finalTotal.replace(/,/g, ''));
       
        let taxAmount = ((finalTotal * taxRate) / 100);
        let updatedSubtotal = (finalTotal + taxAmount);
        $("input[name='final_totalInput']").val(updatedSubtotal);
        $('#final_total').text(updatedSubtotal);
    }
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
