
@foreach( $variations as $variation)
    <tr @if(!empty($purchase_order_line)) data-purchase_order_id="{{$purchase_order_line->transaction_id}}" @endif @if(!empty($purchase_requisition_line)) data-purchase_requisition_id="{{$purchase_requisition_line->transaction_id}}" @endif>
        <td><span class="sr_number"></span></td>
        <td>
            <input type="hidden" id="p_symbol" value="{{$currency_exchange_rate->code}}">
            {{ $product->name }} ({{$variation->sub_sku}})
            @if( $product->type == 'variable' )
                <br/>
                (<b>{{ $variation->product_variation->name }}</b> : {{ $variation->name }})
            @endif
            @if($product->enable_stock == 1)
                <br>
                <small class="text-muted" style="white-space: nowrap;">@lang('report.current_stock'): @if(!empty($variation->variation_location_details->first())) {{@num_format($variation->variation_location_details->first()->qty_available)}} @else 0 @endif {{ $product->unit->short_name }}</small>
            @endif
            
        </td>
        <td>
        
            {{ $product->weight==null? 'N/A': $product->weight}}
        </td>
        <td>
            @if(!empty($purchase_order_line))
                {!! Form::hidden('purchases[' . $row_count . '][purchase_order_line_id]', $purchase_order_line->id ); !!}
            @endif

            @if(!empty($purchase_requisition_line))
                {!! Form::hidden('purchases[' . $row_count . '][purchase_requisition_line_id]', $purchase_requisition_line->id ); !!}
            @endif

            {!! Form::hidden('purchases[' . $row_count . '][product_id]', $product->id ); !!}
            {!! Form::hidden('purchases[' . $row_count . '][variation_id]', $variation->id , ['class' => 'hidden_variation_id']); !!}

            @php
                $check_decimal = 'false';
                if($product->unit->allow_decimal == 0){
                    $check_decimal = 'true';
                }
                $currency_precision = session('business.currency_precision', 2);
                $quantity_precision = session('business.quantity_precision', 2);

                $quantity_value = !empty($purchase_order_line) ? $purchase_order_line->quantity : 1;

                $quantity_value = !empty($purchase_requisition_line) ? $purchase_requisition_line->quantity - $purchase_requisition_line->po_quantity_purchased : $quantity_value;
                $max_quantity = !empty($purchase_order_line) ? $purchase_order_line->quantity - $purchase_order_line->po_quantity_purchased : 0;

                $max_quantity = !empty($purchase_requisition_line) ? $purchase_requisition_line->quantity - $purchase_requisition_line->po_quantity_purchased : $max_quantity;

                $quantity_value = !empty($imported_data) ? $imported_data['quantity'] : $quantity_value;
            @endphp
            
            <input type="text" 
                name="purchases[{{$row_count}}][quantity]" 
                value="{{@format_quantity($quantity_value)}}"
                class="form-control input-sm purchase_quantity input_number mousetrap"
                required
                data-rule-abs_digit={{$check_decimal}}
                data-msg-abs_digit="{{__('lang_v1.decimal_value_not_allowed')}}"
            >


            <input type="hidden" class="base_unit_cost" value="{{ str_replace(",","",number_format(str_replace(",","", $variation->default_purchase_price) * $currency_exchange_rate->rate, 2)) }}">
            <input type="hidden" class="base_unit_selling_price" value="{{ str_replace(",","", number_format(str_replace(",","", $variation->sell_price_inc_tax) * $currency_exchange_rate->rate, 2)) }}">

            <input type="hidden" name="purchases[{{$row_count}}][product_unit_id]" value="{{$product->unit->id}}">
            @if(!empty($sub_units))
                <br>
                <select name="purchases[{{$row_count}}][sub_unit_id]" class="form-control input-sm sub_unit">
                    @foreach($sub_units as $key => $value)
                        <option value="{{$key}}" data-multiplier="{{$value['multiplier']}}">
                            {{$value['name']}}
                        </option>
                    @endforeach
                </select>
            @else 
                {{ $product->unit->short_name }}
            @endif

            @if(!empty($product->second_unit))
                @php
                    $secondary_unit_quantity = !empty($purchase_requisition_line) ? $purchase_requisition_line->secondary_unit_quantity : "";
                @endphp
                <br>
                <span style="white-space: nowrap;">
                @lang('lang_v1.quantity_in_second_unit', ['unit' => $product->second_unit->short_name])*:</span><br>
                <input type="text" 
                name="purchases[{{$row_count}}][secondary_unit_quantity]" 
                @if($secondary_unit_quantity !== '')value="{{@format_quantity($secondary_unit_quantity)}}" @endif
                class="form-control input-sm input_number"
                required>
            @endif
        </td>
        <td>
            @php
                $pp_without_discount = !empty($purchase_order_line) ? $purchase_order_line->pp_without_discount/$purchase_order->exchange_rate : $variation->default_purchase_price;

                // $pp_without_discount = $pp_without_discount * $currency_exchange_rate->rate;
                $discount_percent = !empty($purchase_order_line) ? $purchase_order_line->discount_percent : 0;

                // $discount_percent = $discount_percent * $currency_exchange_rate->rate;

                $purchase_price = !empty($purchase_order_line) ? $purchase_order_line->purchase_price/$purchase_order->exchange_rate : $variation->default_purchase_price;

                // $discount_percent = $purchase_price * $currency_exchange_rate->rate;

                $tax_id = !empty($purchase_order_line) ? $purchase_order_line->tax_id : $product->tax;

                $tax_id = !empty($imported_data['tax_id']) ? $imported_data['tax_id'] : $tax_id;

                $pp_without_discount = !empty($imported_data['unit_cost_before_discount']) ? $imported_data['unit_cost_before_discount'] : $pp_without_discount;

                $discount_percent = !empty($imported_data['discount_percent']) ? $imported_data['discount_percent'] : $discount_percent;
            @endphp
            <small class="text-muted">{{$pp_without_discount * $currency_exchange_rate->rate}}</small>
            {!! Form::hidden('purchases[' . $row_count . '][pp_without_discount]', number_format($pp_without_discount * $currency_exchange_rate->rate, 4), ['class' => 'form-control input-sm purchase_unit_cost_without_discount input_number', 'required']); !!}
            <input type="hidden" class="form-control row_subtotal_before_tax_" value="{{$variation->sell_price_inc_tax * $currency_exchange_rate->rate}}" name="purchases[{{$row_count}}][unit_cost]">
            <input type="hidden" class="form-control row_subtotal_after_tax_hidden" value="{{ $variation->sell_price_inc_tax * $currency_exchange_rate->rate }}" name="purchases[{{$row_count}}][unit_cost]">
                
            @if(!empty($last_purchase_line))
                <br>
                <small class="text-muted">@lang('lang_v1.prev_unit_price'): @format_currency($last_purchase_line->pp_without_discount * $currency_exchange_rate->rate)</small>
            @endif
        </td>
        <td>
            <small class="text-muted"> {{$variation->profit_percent}}</small>
            {!! Form::hidden('purchases[' . $row_count . '][discount_percent]', number_format($discount_percent,2), ['class' => 'form-control input-sm inline_discounts input_number', 'required']); !!}
        </td>
        <td>
            @php 
                $is_exl_tax= $product->tax_type== "exclusive"? true :false;
                $sell_price_inc_tax = $variation->sell_price_inc_tax * $currency_exchange_rate->rate;
            @endphp
            @if($is_exl_tax)
            <input type="hidden" value=" {{ number_format($variation->sell_price_inc_tax, 2)}}" class="base_unit_selling_price">
            <input type="text" name="purchases[{{$row_count}}][purchase_price]" value="{{$variation->sell_price_inc_tax}}" class="form-control input-sm purchase_price">
        
            @else
            <input type="text" name="purchases[{{$row_count}}][purchase_price]" value="0" class="form-control input-sm purchase_price">
            @endif
        </td>
        @if(empty($is_purchase_order))
        <td>
            @php 
                $is_inc_tax=$product->tax_type=="inclusive"? true :false;
                $sell_price_inc_tax = $variation->sell_price_inc_tax * $currency_exchange_rate->rate;
            @endphp
                @if($is_inc_tax)
                    {{ number_format($sell_price_inc_tax, 2)}}
                    <input type="hidden" value=" {{ number_format($sell_price_inc_tax, 2)}}" class="base_unit_selling_price">
                @endif
        </td>
        @endif
        <td class="" data-qty="{{$currentStock}}">{{$currentStock}}</td>
        <td class="avaliableQty" data-qty="{{$availableQty}}">{{$availableQty}}</td>
        <td>
            {{$intransitQuantity}}
        </td>
        <td>
            <select name="status" id="avaliabilityStatus">
                <option value="Avaliable" {{$availableQty > 1? 'Selected' :''}}>Avaliable</option>
                <option value="Partial Avalaible" >Partial Avalaible</option>
                <option value="Not Avalaible" {{($availableQty < 1) ? "Selected" :""}}>Pending Request Quote</option>
            </select>
        </td>
        <?php $row_count++ ;?>

        <td><i class="fa fa-times remove_purchase_entry_row text-danger" title="Remove" style="cursor:pointer;"></i></td>
    </tr>
@endforeach

<input type="hidden" id="row_count" value="{{ $row_count }}">