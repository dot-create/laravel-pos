@php
use App\Utils\ProductUtil;
@endphp
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
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('request_list_filter_sku_model', __('request.search_by_SKU/Model') . ':') !!}
                        {!! Form::text('request_list_filter_sku_model', null, ['placeholder' => __('request.search_by_SKU/Model'), 'class' => 'form-control']); !!}
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
            <form action="{{route('request.item.accepteQuoteForm',$request->id)}}" method="POST">
                @csrf
                <h2 >Request Items</h2>
                <div class="row">
                    <div class="col-sm-12 table-responsive" >
                            <table class="table table-bordered table-striped ajax_view " id="" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>@lang( 'product.product_name' )</th>
                                        <th>@lang( 'product.sku' )</th>
                                        <th>@lang( 'request.avaliable_stock' )</th>
                                        <th>@lang( 'request.required_quantity' )</th>
                                        <th>@lang( 'request.accepted_quantity' )</th>
                                        <th>@lang( 'request.suggested_quantity' )</th>
                                        <th>@lang( 'request.customer_po' )</th>
                                        <th>@lang( 'request.selling_price' )</th>
                                        <th>@lang( 'request.discount' )</th>.
                                        <th>@lang( 'request.subTotal' )</th>
                                        <th>@lang( 'request.tax' )</th>
                                        
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
                                            $subtotal = $item->quantity * $unit_price_supplier;
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
                                                {{ $item->product->sku }} ({{$item->variation->sub_sku}})
                                            </td>
                                            <td>
                                                @php
                                                    $productUtil= new ProductUtil();
                                                    $getValues=$productUtil->getAvaliableQty($business_id,$item->variation_id,$item->request->business_location_id);
                                                    $avaliabilityQty=$getValues['avaliabilityQty'];
                                                @endphp
                                                <span class="avaliableQty">{{$avaliabilityQty}}</span>
                                            </td>
                                            <td>
                                                {{$item->quantity}}
                                            </td>
                                            <td>
                                                <input type="text" name="accepted_qty[]" required class="accepted-quantity">
                                            </td>
                                            <td>
                                                @php
                                                $id=$item->variation_id;
                                                $totalCommitedQty = App\CustomerRequest::where(['status'=> 'AcceptedQuote','business_location_id'=>$item->request->business_location_id])
                                                ->whereHas('items', function ($query) use ($id) {
                                                    $query->where('variation_id', $id);
                                                })
                                                ->withSum(['items' => function ($query) use ($id) {
                                                    $query->where('variation_id', $id);
                                                }], 'accepted_qty')
                                                ->get()
                                                ->sum('items_sum_accepted_qty');
                                            
                                                @endphp
                                                @if($avaliabilityQty > $totalCommitedQty)
                                                    <span class="suggestedQty">0</span>
                                                
                                                @else
                                                    <span class="suggestedQty"></span>
                                                @endif
                                            </td>
                                            <!-- <td>
                                                <input type="text" name="backorder_qty[]" required>
                                            </td>
                                            <td>
                                                <input type="text" name="invoice_qty[]" required>
                                            </td> -->
                                            <td>
                                                <input type="text" name="po_number[]" required>
                                            </td>
                                            <td>
                                                
                                                <span>{{$item->suggested_sell_price_USD_wot?? $item->variation->default_sell_price}}</span>
                                                
                                            </td>
                                            <td>
                                                <input type="number" value="{{$item->discount}}" disabled>
                                                <select class="form-control discount-type" data-id="{{$row_count}}" name="discount_type[]" disabled>
                                                    <option value="fixed" {{$item->discount_type=="fixed"? "selected":""}}>Fixed</option>
                                                    <option value="percentage" {{$item->discount_type=="percentage"? "selected":""}}>Percentage</option>
                                                </select>
                                            </td>
                                            @php
                                                $subtotal=( (float) $item->total_price  * (float) $item->quantity);
                                                if($item->subtotal_wd){
                                                    $subtotal=$item->subtotal_wd;
                                                }
                                            @endphp
                                            <td id="subtotal-{{$row_count}}" data-sub="{{$subtotal}}">
                                                {{ number_format($subtotal, 2) }}
                                            </td>
                                            <td>{{$item->tax}} %</td>
                                            
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
                                            @if($item->best_supplier_name)
                                                <td colspan="7">
                                                    <p>
                                                        <b>Supplier</b>: <span>{{$item->best_supplier_name}}</span>
                                                        <b>Quantity</b>: <span>{{$quantity}}</span>
                                                        <b>Freight</b>: <span>{{$freight}}</span>
                                                        <b>Ecommerce Fees Percentage</b>: <span>{{$ecom_fee_percentage_supplier}}</span>
                                                        <b>Price</b>: <span>{{$formula_price_supplier}}</span>
                                                        <b>Total Price</b>: <span style="color:green">{{$item->total_price}}</span>
                                                        <b>Delivery Time</b>: <span>{{$item->delivery_time}}</span>
                                                    </p>
                                                    <textarea class="form-control" id="" rows="5" cols="40" placeholder="Seller note..." readonly>{{$item->seller_note}}</textarea>
                                                </td>
                                            @else
                                                <td colspan="7">
                                                    
                                                </td>
                                            @endif
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
                            <input type="number" id="overall_discount" class="form-control" value="{{$request->discount?? 0}}" name="overall_discount" disabled>
                        </div>
                        <div class="col-md-2">
                            <label>@lang('purchase.discount_type')</label>
                            <select id="overall_discount_type" class="form-control" name="overall_discount_type" disabled>
                                <option value="fixed" {{$request->discount_type=="fixed"? 'selected':''}}>Fixed</option>
                                <option value="percentage" {{$request->discount_type=="percentage"? 'selected':''}}>Percentage</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <b>@lang('purchase.discount'):</b> (-) <span id="discount_calculated_amount">{{$request->discount?? 0}}</span>
                        </div>
                        <div class="col-md-2">
                            <label>@lang('purchase.tax')</label>
                            <select name="overall_tax" id="overall_tax" class="form-control" style="width:75px">
                                <option value="0" {{$request->tax==0? 'selected' :''}}>0%</option>
                                <option value="15" {{$request->tax==15? 'selected' :''}}>15%</option>
                                <option value="18" {{$request->tax==18? 'selected' :''}}>18%</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                        @php
                            $finalTotal= (float) $request->items->sum('subtotal_wd_tax');
                                $actualDiscount=(float) $request->discount;
                                if($actualDiscount){
                                    $discountType=$request->discount_type;
                                    if($discountType=="fixed" || $discountType=='["fixed"]'){
                                        $finalTotal=$finalTotal - $actualDiscount;
                                    }
                                    else{
                                        $finalTotal = ($actualDiscount / 100) * $finalTotal;
                                    }
                                }
                                if($request->tax){
                                    $totalTax = ($request->tax / 100) * $finalTotal;
                                    $finalTotal= $finalTotal + $totalTax;
                                }
                            @endphp
                            <input type="hidden" name="final_totalInput" value="{{number_format($finalTotal, 2)}}">
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
                <button type="submit" class="btn btn-success">Accept Quote</button>
            </form>
        </div>
    </div>
</section>

<!-- /.content -->
@endsection

@section('javascript')
    <script>
    $(document).on('input change', '.item-discount, .discount-type, #overall_discount, #overall_discount_type', function() {
        let total = 0;
        $('.item-discount').each(function() {
            let id = $(this).data('id');
            let subTotal="{{$subtotal}}";
            let discount = parseFloat($(this).val()) || 0;
            let discountType = $('.discount-type[data-id="' + id + '"]').val();
            let price = parseFloat($('#subtotal-' + id).data('sub')) || 0;
            
            let discountedPrice = discountType === 'percentage' ? price - (price * discount / 100) : price - discount;
            $('#subtotal-' + id).text(discountedPrice.toFixed(2));
            total += discountedPrice;
        });
        
        let overallDiscount = parseFloat($('#overall_discount').val()) || 0;
        let overallDiscountType = $('#overall_discount_type').val();
        let totalAfterDiscount = overallDiscountType === 'percentage' ? total - (total * overallDiscount / 100) : total - overallDiscount;
        
        $('#discount_calculated_amount').text(overallDiscount);
        $('#final_total').text(totalAfterDiscount.toFixed(2));
        $('#final_totalInput').val(totalAfterDiscount.toFixed(2));
    });
    $(".accepted-quantity").on("input", function() {
        var acceptedQuantity = parseInt($(this).val()) || 0;
        var avalaliableQty = parseInt($(this).closest("tr").find(".avaliableQty").text()) || 0;

        // Calculate the suggested quantity
        var suggestedQuantity = acceptedQuantity - avalaliableQty ;
        if(avalaliableQty > acceptedQuantity){
            $(this).closest("tr").find(".suggestedQty").text(0);
        }
        else{
        $(this).closest("tr").find(".suggestedQty").text(suggestedQuantity);
        }

        
    });
</script>
	@include('purchase.partials.keyboard_shortcuts')
@endsection
