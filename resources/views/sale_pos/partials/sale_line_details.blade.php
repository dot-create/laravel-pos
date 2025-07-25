<table class="table @if(!empty($for_ledger)) table-slim mb-0 bg-light-gray @else bg-gray @endif" @if(!empty($for_pdf)) style="width: 100%;" @endif>
        <tr @if(empty($for_ledger)) class="bg-green" @endif>
        <th>#</th>
        <th>{{ __('sale.product') }}</th>
        @if( session()->get('business.enable_lot_number') == 1 && empty($for_ledger))
            <th>{{ __('lang_v1.lot_n_expiry') }}</th>
        @endif
        @if($sell->type == 'sales_order')
            <th>@lang('lang_v1.quantity_remaining')</th>
        @endif
        <th>{{ __('sale.qty') }}</th>
        @if(!empty($pos_settings['inline_service_staff']))
            <th>
                @lang('restaurant.service_staff')
            </th>
        @endif
        <th>{{ __('sale.unit_price') }}</th>
        <th>{{ __('sale.discount') }}</th>
        <th>{{ __('sale.tax') }}</th>
        <th>{{ __('sale.price_inc_tax') }}</th>
        <th>{{ __('sale.subtotal') }}</th>
    </tr>
    @foreach($sell->sell_lines as $sell_line)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>
                {{ $sell_line->product->name }}
                @if( $sell_line->product->type == 'variable')
                - {{ $sell_line->variations->product_variation->name ?? ''}}
                - {{ $sell_line->variations->name ?? ''}},
                @endif
                {{ $sell_line->variations->sub_sku ?? ''}}
                @php
                $brand = $sell_line->product->brand;
                @endphp
                @if(!empty($brand->name))
                , {{$brand->name}}
                @endif

                @if(!empty($sell_line->sell_line_note))
                <br> {{$sell_line->sell_line_note}}
                @endif
                @if($is_warranty_enabled && !empty($sell_line->warranties->first()) )
                    <br><small>{{$sell_line->warranties->first()->display_name ?? ''}} - {{ @format_date($sell_line->warranties->first()->getEndDate($sell->transaction_date))}}</small>
                    @if(!empty($sell_line->warranties->first()->description))
                    <br><small>{{$sell_line->warranties->first()->description ?? ''}}</small>
                    @endif
                @endif

                @if(in_array('kitchen', $enabled_modules) && empty($for_ledger))
                    <br><span class="label @if($sell_line->res_line_order_status == 'cooked' ) bg-red @elseif($sell_line->res_line_order_status == 'served') bg-green @else bg-light-blue @endif">@lang('restaurant.order_statuses.' . $sell_line->res_line_order_status) </span>
                @endif
            </td>
            @if( session()->get('business.enable_lot_number') == 1 && empty($for_ledger))
                <td>{{ $sell_line->lot_details->lot_number ?? '--' }}
                    @if( session()->get('business.enable_product_expiry') == 1 && !empty($sell_line->lot_details->exp_date))
                    ({{@format_date($sell_line->lot_details->exp_date)}})
                    @endif
                </td>
            @endif
            @if($sell->type == 'sales_order')
                <td><span class="display_currency" data-currency_symbol="false" data-is_quantity="true">{{ $sell_line->quantity - $sell_line->so_quantity_invoiced }}</span> @if(!empty($sell_line->sub_unit)) {{$sell_line->sub_unit->short_name}} @else {{$sell_line->product->unit->short_name}} @endif</td>
            @endif
            <td>
                @if(!empty($for_ledger))
                    {{@format_quantity($sell_line->quantity)}}
                @else
                    <span class="display_currency" data-currency_symbol="false" data-is_quantity="true">{{ $sell_line->quantity }}</span> 
                @endif
                    @if(!empty($sell_line->sub_unit)) {{$sell_line->sub_unit->short_name}} @else {{$sell_line->product->unit->short_name}} @endif

                @if(!empty($sell_line->product->second_unit) && $sell_line->secondary_unit_quantity != 0)
                    <br>
                    @if(!empty($for_ledger))
                        {{@format_quantity($sell_line->secondary_unit_quantity)}}
                    @else
                        <span class="display_currency" data-is_quantity="true" data-currency_symbol="false">{{ $sell_line->secondary_unit_quantity }}</span> 
                    @endif
                    {{$sell_line->product->second_unit->short_name}}
                @endif
            </td>
            @if(!empty($pos_settings['inline_service_staff']))
                <td>
                {{ $sell_line->service_staff->user_full_name ?? '' }}
                </td>
            @endif
            <td>
                @if(!empty($for_ledger))
                    @format_currency($sell_line->unit_price_before_discount , $currency_details->symbol)
                @else
                    <span class="display_currency" data-currency_symbol="true" data-currency="{{ $currency_details->symbol }}">{{ $sell_line->unit_price_before_discount  }}</span>
                @endif
            </td>
            <td>
                @if(!empty($for_ledger))
                    @format_currency($sell_line->get_discount_amount() , $currency_details->symbol)
                @else
                    <span class="display_currency" data-currency_symbol="true" data-currency="{{ $currency_details->symbol }}">{{ $sell_line->get_discount_amount()  }}</span>
                @endif
                @if($sell_line->line_discount_type == 'percentage') ({{number_format($sell_line->line_discount_amount, 4)}}%) @endif
                <!--@if($sell_line->line_discount_type == 'percentage') ({{number_format($sell_line->line_discount_amount , 2)}}%) @endif-->
            </td>
            <td>
                @if(!empty($for_ledger))
                    @format_currency($sell_line->item_tax , $currency_details->symbol)
                @else
                    <span class="display_currency" data-currency_symbol="true" data-currency="{{ $currency_details->symbol }}">{{ $sell_line->item_tax  }}</span> 
                @endif
                @if(!empty($taxes[$sell_line->tax_id]))
                ( {{ $taxes[$sell_line->tax_id]}} )
                @endif
            </td>
            <td>
                @if(!empty($for_ledger))
                    @format_currency($sell_line->unit_price_inc_tax , $currency_details->symbol)
                @else
                    <span class="display_currency" data-currency_symbol="true" data-currency="{{ $currency_details->symbol }}" data-test="{{ $sell_line->unit_price_inc_tax }}">{{ $sell_line->unit_price_inc_tax  }}</span>
                @endif
            </td>
            <td>
                @if(!empty($for_ledger))
                    @format_currency($sell_line->quantity * $sell_line->unit_price_inc_tax , $currency_details->symbol)
                @else
                    <span class="display_currency" data-currency_symbol="true" data-currency="{{ $currency_details->symbol }}">{{ $sell_line->quantity * $sell_line->unit_price_inc_tax  }}</span>
                @endif
            </td>
        </tr>
        @if(!empty($sell_line->modifiers))
        @foreach($sell_line->modifiers as $modifier)
            <tr>
                <td>&nbsp;</td>
                <td>
                    {{ $modifier->product->name }} - {{ $modifier->variations->name ?? ''}},
                    {{ $modifier->variations->sub_sku ?? ''}}
                </td>
                @if( session()->get('business.enable_lot_number') == 1)
                    <td>&nbsp;</td>
                @endif
                <td>{{ $modifier->quantity }}</td>
                @if(!empty($pos_settings['inline_service_staff']))
                    <td>
                        &nbsp;
                    </td>
                @endif
                <td>
                    @if(!empty($for_ledger))
                        @format_currency($modifier->unit_price , $currency_details->symbol)
                    @else
                        <span class="display_currency" data-currency_symbol="true" data-currency="{{ $currency_details->symbol }}">{{ $modifier->unit_price  }}</span>
                    @endif
                </td>
                <td>
                    &nbsp;
                </td>
                <td>
                    @if(!empty($for_ledger))
                        @format_currency($modifier->item_tax , $currency_details->symbol)
                    @else
                        <span class="display_currency" data-currency_symbol="true" data-currency="{{ $currency_details->symbol }}">{{ $modifier->item_tax  }}</span> 
                    @endif
                    @if(!empty($taxes[$modifier->tax_id]))
                    ( {{ $taxes[$modifier->tax_id]}} )
                    @endif
                </td>
                <td>
                    @if(!empty($for_ledger))
                        @format_currency($modifier->unit_price_inc_tax , $currency_details->symbol)
                    @else
                        <span class="display_currency" data-currency_symbol="true" data-currency="{{ $currency_details->symbol }}">{{ $modifier->unit_price_inc_tax  }}</span>
                    @endif
                </td>
                <td>
                    @if(!empty($for_ledger))
                        @format_currency($modifier->quantity * $modifier->unit_price_inc_tax , $currency_details->symbol)
                    @else
                        <span class="display_currency" data-currency_symbol="true" test="{{ $modifier->quantity }}" data-currency="{{ $currency_details->symbol }}">{{ $modifier->quantity * $modifier->unit_price_inc_tax  }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        @endif
    @endforeach
</table>