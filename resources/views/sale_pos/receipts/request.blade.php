<!-- business information here -->

<div class="row">


	@if($receipt_details->show_qr_code && !empty($receipt_details->qr_code_text))
		<img class="center-block" src="data:image/png;base64,{{DNS2D::getBarcodePNG($invoice_no, 'QRCODE', 2, 2, [39, 48, 54])}}" >
	@endif

<!-- Logo -->
	@if(!$receipt_details->show_qr_code )
		@if(!empty($receipt_details->logo))
			<img style="max-height: 120px; width: auto;" src="{{$receipt_details->logo}}" class="img img-responsive center-block">
		@endif
	@endif


<!-- Header text -->
	@if(!empty($receipt_details->header_text))
		<div class="col-xs-12">
			
			{!! $receipt_details->header_text !!}
		</div>
@endif

<!-- business information here -->
	<div class="col-xs-12 text-center">
		<h2 class="text-center">
			<!-- Shop & Location Name  -->
			@if(!empty($receipt_details->display_name))
				{{$receipt_details->display_name}}
			@endif
		</h2>

		<!-- Address -->
		<p>
			@if(!empty($receipt_details->address))
				<small class="text-center">
					{!! $receipt_details->address !!}
				</small>
			@endif
			@if(!empty($receipt_details->contact))
				<br/>{!! $receipt_details->contact !!}
			@endif
			@if(!empty($receipt_details->contact) && !empty($receipt_details->website))
				,
			@endif
			@if(!empty($receipt_details->website))
				{{ $receipt_details->website }}
			@endif
			@if(!empty($receipt_details->location_custom_fields))
				<br>{{ $receipt_details->location_custom_fields }}
			@endif
		</p>
		<p>
			@if(!empty($receipt_details->sub_heading_line1))
				{{ $receipt_details->sub_heading_line1 }}
			@endif
			@if(!empty($receipt_details->sub_heading_line2))
				<br>{{ $receipt_details->sub_heading_line2 }}
			@endif
			@if(!empty($receipt_details->sub_heading_line3))
				<br>{{ $receipt_details->sub_heading_line3 }}
			@endif
			@if(!empty($receipt_details->sub_heading_line4))
				<br>{{ $receipt_details->sub_heading_line4 }}
			@endif
			@if(!empty($receipt_details->sub_heading_line5))
				<br>{{ $receipt_details->sub_heading_line5 }}
			@endif
		</p>
		<p>
			@if(!empty($receipt_details->tax_info1))
				<b>{{ $receipt_details->tax_label1 }}</b> {{ $receipt_details->tax_info1 }}
			@endif

			@if(!empty($receipt_details->tax_info2))
				<b>{{ $receipt_details->tax_label2 }}</b> {{ $receipt_details->tax_info2 }}
			@endif
		</p>

		<!-- Title of receipt -->
		@if(!empty($receipt_details->invoice_heading))
			<h3 class="text-center">
				QUOTE/COTIZACIÓN
			</h3>
	@endif
	<!-- Invoice  number, Date  -->
		<p style="width: 100% !important" class="word-wrap">
			<span class="pull-left text-left word-wrap">
				
				<strong>QUOTE/COTIZACIÓN:{{$invoice_no}}</strong>
				<br>
				<strong>Request/Requisicion: 
				{{$receipt_details->invoice_no}}</strong>

				@if(!empty($receipt_details->types_of_service))
					<br/>
					<span class="pull-left text-left">
						<strong>{!! $receipt_details->types_of_service_label !!}:</strong>
						{{$receipt_details->types_of_service}}
					<!-- Waiter info -->
						@if(!empty($receipt_details->types_of_service_custom_fields))
							@foreach($receipt_details->types_of_service_custom_fields as $key => $value)
								<br><strong>{{$key}}: </strong> {{$value}}
							@endforeach
						@endif
					</span>
				@endif

			<!-- Table information-->
				@if(!empty($receipt_details->table_label) || !empty($receipt_details->table))
					<br/>
					<span class="pull-left text-left">
						@if(!empty($receipt_details->table_label))
							<b>{!! $receipt_details->table_label !!}</b>
					@endif
					{{$receipt_details->table}}

					<!-- Waiter info -->
					</span>
				@endif

				<!-- customer info -->
				@if(!empty($receipt_details->customer_info))
					<br/>
					<b>{{ $receipt_details->customer_label }}</b> <br> {!! $receipt_details->customer_info !!} <br>
				@endif
				@if(!empty($receipt_details->client_id_label))
					<br/>
					<b>{{ $receipt_details->client_id_label }}</b> {{ $receipt_details->client_id }}
				@endif
				@if(!empty($receipt_details->customer_tax_label))
					<br/>
					<b>{{ $receipt_details->customer_tax_label }}</b> {{ $receipt_details->customer_tax_number }}
				@endif
				@if(!empty($receipt_details->customer_custom_fields))
					<br/>{!! $receipt_details->customer_custom_fields !!}
				@endif
				@if(!empty($receipt_details->sales_person_label))
					<br/>
					<b>{{ $receipt_details->sales_person_label }}</b> {{ $receipt_details->sales_person }}
				@endif
				@if(!empty($receipt_details->commission_agent_label))
					<br/>
					<strong>{{ $receipt_details->commission_agent_label }}</strong> {{ $receipt_details->commission_agent }}
				@endif
				@if(!empty($receipt_details->customer_rp_label))
					<br/>
					<strong>{{ $receipt_details->customer_rp_label }}</strong> {{ $receipt_details->customer_total_rp }}
				@endif
			</span>

			<span class="pull-right text-left">
				<b>{{$receipt_details->date_label}}</b> {{ \Carbon\Carbon::now()->format('d-m-Y H:i:s') }}

				@if(!empty($receipt_details->due_date_label))
					<br><b>{{$receipt_details->due_date_label}}</b> {{$receipt_details->due_date ?? ''}}
				@endif

				@if(!empty($receipt_details->brand_label) || !empty($receipt_details->repair_brand))
					<br>
					@if(!empty($receipt_details->brand_label))
						<b>{!! $receipt_details->brand_label !!}</b>
					@endif
					{{$receipt_details->repair_brand}}
				@endif


				@if(!empty($receipt_details->device_label) || !empty($receipt_details->repair_device))
					<br>
					@if(!empty($receipt_details->device_label))
						<b>{!! $receipt_details->device_label !!}</b>
					@endif
					{{$receipt_details->repair_device}}
				@endif

				@if(!empty($receipt_details->model_no_label) || !empty($receipt_details->repair_model_no))
					<br>
					@if(!empty($receipt_details->model_no_label))
						<b>{!! $receipt_details->model_no_label !!}</b>
					@endif
					{{$receipt_details->repair_model_no}}
				@endif

				@if(!empty($receipt_details->serial_no_label) || !empty($receipt_details->repair_serial_no))
					<br>
					@if(!empty($receipt_details->serial_no_label))
						<b>{!! $receipt_details->serial_no_label !!}</b>
					@endif
					{{$receipt_details->repair_serial_no}}<br>
				@endif
				@if(!empty($receipt_details->repair_status_label) || !empty($receipt_details->repair_status))
					@if(!empty($receipt_details->repair_status_label))
						<b>{!! $receipt_details->repair_status_label !!}</b>
					@endif
					{{$receipt_details->repair_status}}<br>
				@endif

				@if(!empty($receipt_details->repair_warranty_label) || !empty($receipt_details->repair_warranty))
					@if(!empty($receipt_details->repair_warranty_label))
						<b>{!! $receipt_details->repair_warranty_label !!}</b>
					@endif
					{{$receipt_details->repair_warranty}}
					<br>
				@endif

			  <!-- Waiter info -->
				@if(!empty($receipt_details->service_staff_label) || !empty($receipt_details->service_staff))
					<br/>
					@if(!empty($receipt_details->service_staff_label))
						<b>{!! $receipt_details->service_staff_label !!}</b>
					@endif
					{{$receipt_details->service_staff}}
				@endif
				@if(!empty($receipt_details->shipping_custom_field_1_label))
					<br><strong>{!!$receipt_details->shipping_custom_field_1_label!!} :</strong> {!!$receipt_details->shipping_custom_field_1_value ?? ''!!}
				@endif

				@if(!empty($receipt_details->shipping_custom_field_2_label))
					<br><strong>{!!$receipt_details->shipping_custom_field_2_label!!}:</strong> {!!$receipt_details->shipping_custom_field_2_value ?? ''!!}
				@endif

				@if(!empty($receipt_details->shipping_custom_field_3_label))
					<br><strong>{!!$receipt_details->shipping_custom_field_3_label!!}:</strong> {!!$receipt_details->shipping_custom_field_3_value ?? ''!!}
				@endif

				@if(!empty($receipt_details->shipping_custom_field_4_label))
					<br><strong>{!!$receipt_details->shipping_custom_field_4_label!!}:</strong> {!!$receipt_details->shipping_custom_field_4_value ?? ''!!}
				@endif

				@if(!empty($receipt_details->shipping_custom_field_5_label))
					<br><strong>{!!$receipt_details->shipping_custom_field_2_label!!}:</strong> {!!$receipt_details->shipping_custom_field_5_value ?? ''!!}
				@endif
				{{-- sale order --}}
				@if(!empty($receipt_details->sale_orders_invoice_no))
					<br>
					<strong>@lang('restaurant.order_no'):</strong> {!!$receipt_details->sale_orders_invoice_no ?? ''!!}
				@endif

				@if(!empty($receipt_details->sale_orders_invoice_date))
					<br>
					<strong>@lang('lang_v1.order_dates'):</strong> {!!$receipt_details->sale_orders_invoice_date ?? ''!!}
				@endif
			</span>
		</p>
	</div>
</div>

<div class="row">
	@includeIf('sale_pos.receipts.partial.common_repair_invoice')
</div>
@php
function calculateItemDetails($unitPrice, $quantity, $discountType = null, $discountValue = null, $taxPercentage = null) {
    $unitPrice = floatval($unitPrice);
    $quantity = intval($quantity);
    $discountValue = $discountValue !== null ? floatval($discountValue) : 0;
    $taxPercentage = $taxPercentage !== null ? floatval($taxPercentage) : 0;

    // --- Discount per unit ---
    $unitDiscount = 0;
    if ($discountType === 'fixed') {
        $unitDiscount = $discountValue;
    } elseif ($discountType === 'percentage') {
        $unitDiscount = ($discountValue / 100) * $unitPrice;
    }

    $discountedUnitPrice = $unitPrice - $unitDiscount;

    // --- Tax per unit after discount ---
    $taxAmount = ($taxPercentage / 100) * $discountedUnitPrice;

    // --- Final unit price including tax ---
    $finalUnitPrice = $discountedUnitPrice + $taxAmount;

    // --- Total for all quantities ---
    $subtotal = round($finalUnitPrice * $quantity, 2);
    $totalTax = round($taxAmount * $quantity, 2);
    $totalDiscount = round($unitDiscount * $quantity, 2);

    return [
        'subtotal' => $subtotal,
        'total_tax' => $totalTax,
        'total_discount' => $totalDiscount,
        'final_unit_price' => round($finalUnitPrice, 2),
    ];
}
@endphp



@php $grandTotal = 0;
$totalDiscount = 0;
$lineTax =0;
$totalTax =0;
@endphp
<div class="row">
	<div class="col-xs-12">
		<br/>
		@php
			$p_width = 40;
		@endphp
		@if(!empty($receipt_details->item_discount_label))
			@php
				$p_width -= 15;
			@endphp
		@endif
		<table class="table table-responsive table-slim">
			<thead>
			<tr>
				<th width="{{$p_width}}%">{{$receipt_details->table_product_label}}</th>
				<th class="text-right" width="15%">{{$receipt_details->table_qty_label}}</th>
				<th class="text-right" width="15%">{{$receipt_details->table_unit_price_label}}</th>
				@if(!empty($receipt_details->item_discount_label))
					<th class="text-right" width="15%">{{$receipt_details->item_discount_label}}</th>
				@endif
                <th class="text-right" width="15%">Tax</th>
				<th class="text-right" width="15%">{{$receipt_details->table_subtotal_label}}</th>
			</tr>
			</thead>
			<tbody>
			@forelse($request->items->where('status','!=','Rejected') as $line)
				<tr>
					<td>
                        @php
                        $media = $line->product->image;
                        if ($media) {
                            $line_array['image'] = asset('uploads/img/'.$media);
							if (!file_exists(public_path('uploads/img/'.$media))) {
								$line_array['image'] = asset('img/default.png');
							}
                        } else {
                            $line_array['image'] = asset('/img/default.png');
                        }
                        @endphp
						@if(!empty($line_array['image']))
							<img src="{{$line_array['image']}}" alt="Image" width="50" style="float: left; margin-right: 8px;" class="img img-responsive center-block">
						@endif
						{{$line->product->name}}
						{{$line->variation->sub_sku}}
						
							<br>
							<small>
								{{$line->sell_line_note}}
							</small>
							
							<br>
							<small>
								{{$line->supply_ref}}
							</small>
					</td>
					<td class="text-right">{{$line->quantity}} {{$line->product->unit->actual_name}} </td>
					<td class="text-right">
					{{format_currency($line->sell_price_wot?? $line->variation->default_sell_price,$business_details->currency_symbol )}}
					</td>
					@if(!empty($receipt_details->item_discount_label))
						<td class="text-right">
						    <!--$receipt_details->currency->rate-->
						    <!--{{ $line['total_line_discount'] }}--->
							 @if($line->discount_type== "percentage")
							 	{{$line->discount ? $line->discount :'0'}} %
							 @else
							{{format_currency($line->discount ? $line->discount : '0.00',$business_details->currency_symbol)}}
                            @endif
						</td>
					@endif
                    <td class="text-right">
                    {{$line->tax ? $line->tax : '0'}} %
                    </td>
					<td class="text-right">
                        @php
                        $details = calculateItemDetails(
                            $line->sell_price_wot ?? $line->variation->default_sell_price,
                            $line->quantity,
                            $line->discount_type ?? null,
                            $line->discount ?? null,
                            $line->tax ?? null
                        );
                            $subtotal = $details['subtotal'];
                            $lineDiscount = $details['total_discount'];
                            $lineTax = $details['total_tax'];

                            $grandTotal += $subtotal;
                            $totalDiscount += $lineDiscount;
                            $totalTax += $lineTax;
                        @endphp
						{{format_currency($subtotal,$business_details->currency_symbol)}}
                    </td>
				</tr>
			@empty
				<tr>
					<td colspan="4">&nbsp;</td>
				</tr>
			@endforelse
			</tbody>
		</table>
	</div>
</div>

<div class="row">
	<div class="col-md-12"><hr/></div>
	@if(!empty($receipt_details->total_paid))
		<div class="col-xs-6">

			<table class="table table-slim">

				@if(!empty($receipt_details->payments))
					@foreach($receipt_details->payments as $payment)
						<tr>
							<td>{{$payment['method']}}</td>
							<td class="text-right" >{{$payment['amount']}}</td>
							<td class="text-right">{{$payment['date']}}</td>
						</tr>
					@endforeach
				@endif

			<!-- Total Paid-->
				@if(!empty($receipt_details->total_paid))
					<tr>
						<th>
							{!! $receipt_details->total_paid_label !!}
						</th>
						<td class="text-right">
							{{$receipt_details->total_paid}}
						</td>
					</tr>
				@endif
			
			<!-- Total Due-->
				@if(!empty($receipt_details->total_due) && !empty($receipt_details->total_due_label))
					<tr>
						<th>
							{!! $receipt_details->total_due_label !!}
						</th>
						<td class="text-right">
							@php
							if($receipt_details->total_paid == 0) {
								$due = $receipt_details->total;
							} else {
								$total_due = explode(" ", $receipt_details->total_due);
								$due = $total_due[0] . " " . number_format(str_replace(",","",$total_due[1]), 2);
							}
							@endphp
						{{ $due }}
						</td>
					</tr>
				@endif

				@if(!empty($receipt_details->all_due))
					<tr>
						<th>
							{!! $receipt_details->all_bal_label !!}
						</th>
						<td class="text-right">
							{{$receipt_details->all_due}}
						</td>
					</tr>
				@endif
			</table>
		</div>
	@endif
	{{-- // Foreign Currency Table --}}
	<div class="col-xs-6">
		<div class="table-responsive">
			<table class="table table-slim">
				<tbody>
				@if(!empty($receipt_details->foreign_location))
					<tr class="color-555">
						<th style="width:70%">
							Foreign Location</th>
						<td class="text-right">
							{{$receipt_details->foreign_location}}
						</td>
					</tr>
					<tr class="color-555">
						<th style="width:50%">
							Currency Rate</th>
						<td class="text-right">
							{{$receipt_details->foreign_currency->rate}} {{$receipt_details->foreign_currency->symbol}}
						</td>
					</tr>
				@endif

                <tr>
                    <th >
                        <!--{!! $receipt_details->line_discount_label !!}-->
                        Line Discount
                    </th>

                    <td class="text-right" >
                        {{format_currency($totalItemDiscount * $receipt_details->foreign_currency->rate,$receipt_details->foreign_currency->symbol)}}
                    </td>
                </tr>
                <tr>
						<th style="white-space: nowrap;">
							<!--{!! $receipt_details->line_discount_label !!}-->
							Line Subtotal
						</th>

						<td class="text-right" style="white-space: nowrap;">
                            {{format_currency($subtotalAfterItemDiscount * $receipt_details->foreign_currency->rate,$receipt_details->foreign_currency->symbol )}}
				
						</td>
					</tr>
                <tr>
                    <th style="white-space: nowrap;">
                        Line Tax
                    </th>
                    <td class="text-right" style="white-space: nowrap;">
                        {{format_currency($totalItemTax  * $receipt_details->foreign_currency->rate,$receipt_details->foreign_currency->symbol )}}
                    </td>
                </tr>
                <tr>
                    <th style="white-space: nowrap;">
                        {!! $receipt_details->subtotal_label !!}
                    </th>
                    <td class="text-right" style="white-space: nowrap;">
                        {{format_currency($subtotalAfterDiscountAndTax * $receipt_details->foreign_currency->rate,$receipt_details->foreign_currency->symbol )}}
                    </td>
                </tr>
				<!-- Discount -->
                <tr>
                    <th style="white-space: nowrap;">
                        <!--{!! $receipt_details->discount_label !!}-->
                        Total Order Discount
                    </th>
							
                    <td class="text-right" style="white-space: nowrap;">
                        (-) {{format_currency($requestDiscountValue * $receipt_details->foreign_currency->rate,$receipt_details->foreign_currency->symbol )}}
                    </td>
                </tr>
				<!-- tax -->
                <tr>
                    <th  style="white-space: nowrap;">
                        <!--{!! $receipt_details->discount_label !!}-->
                        Total Order Tax
                    </th>

                    <td class="text-right" style="white-space: nowrap;">
                        (+) {{format_currency($totalRequestTax * $receipt_details->foreign_currency->rate,$receipt_details->foreign_currency->symbol )}}
                    </td>
                </tr>

				<!-- Total -->
				<tr>
					<th style="white-space: nowrap;">
						{!! $receipt_details->total_label !!}
					</th>
					<td class="text-right" >
						{{format_currency($finalTotal * $receipt_details->foreign_currency->rate,$receipt_details->foreign_currency->symbol )}}
                        <br>
                            @php
                                $format=$receipt_details->word_format;
                                if ($format == 'indian') {
                                echo $this->numToIndianFormat($finalTotal * $receipt_details->foreign_currency->rate); 
                                }

                                if (!extension_loaded('intl')) {
                                    echo '';
                                }

                                if (empty($lang)) {
                                    $lang = !empty(auth()->user()) ? auth()->user()->language : 'en';
                                }

                                $f = new \NumberFormatter($lang, \NumberFormatter::SPELLOUT);
                            @endphp
							<small>({{$f->format($finalTotal * $receipt_details->foreign_currency->rate)}})</small>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>

	{{-- Main Currency Table --}}
	<div class="col-xs-6">
		<div class="table-responsive">
			<table class="table table-slim">
				<tbody>
				@if(!empty($receipt_details->total_quantity_label))
					<tr class="color-555">
						<th style="white-space: nowrap;">
							{!! $receipt_details->total_quantity_label !!}
						</th>
						<td class="text-right" style="white-space: nowrap;">
							{{$receipt_details->total_quantity}}
						</td>
					</tr>
				@endif
					<tr>
						<th style="white-space: nowrap;">
							<!--{!! $receipt_details->line_discount_label !!}-->
							Line Discount
						</th>

						<td class="text-right" style="white-space: nowrap;">
                        (-) {{format_currency($totalItemDiscount ,$business_details->currency_symbol )}}
				
						</td>
					</tr>
					<tr>
						<th style="white-space: nowrap;">
							<!--{!! $receipt_details->line_discount_label !!}-->
							Line Subtotal
						</th>

						<td class="text-right" style="white-space: nowrap;">
                            {{format_currency($subtotalAfterItemDiscount,$business_details->currency_symbol )}}
				
						</td>
					</tr>
                    <tr>
						<th style="white-space: nowrap;">
							Line tax
						</th>
						<td class="text-right" style="white-space: nowrap;">
                        (+) {{format_currency($totalItemTax,$business_details->currency_symbol )}}
						</td>
					</tr>

                    <tr>
						<th style="white-space: nowrap;">
							{!! $receipt_details->subtotal_label !!}
						</th>
						<td class="text-right" style="white-space: nowrap;">
                        {{format_currency($subtotalAfterDiscountAndTax,$business_details->currency_symbol )}}

						</td>
					</tr>
					<tr>
						<th style="white-space: nowrap;">
							<!--{!! $receipt_details->discount_label !!}-->
							Total Order Discount
						</th>

						<td class="text-right" style="white-space: nowrap;">
                            @php
                                $totalorderdiscount=$requestDiscountValue;
                            @endphp
							(-) {{format_currency($totalorderdiscount,$business_details->currency_symbol )}}
						</td>
					</tr>
					<tr>
						<th style="white-space: nowrap;">
							<!--{!! $receipt_details->discount_label !!}-->
							Total Order Tax {{$request->tax? "($request->tax %)" :""}}
						</th>

						<td class="text-right" style="white-space: nowrap;">
                            @php
                                $totalorderdiscountTax=$totalRequestTax;
                            @endphp
							(+) {{format_currency($totalorderdiscountTax,$business_details->currency_symbol )}}
						</td>
					</tr>

				<!-- Total -->
				<tr>
					<th style="white-space: nowrap;">
						{!! $receipt_details->total_label !!}
					</th>
					<td class="text-right" style="white-space: nowrap;">
                        {{format_currency($finalTotal,$business_details->currency_symbol )}}
						
							<br>
                            @php
                                function numToIndianFormat(float $number)
                                {
                                    $decimal = round($number - ($no = floor($number)), 2) * 100;
                                    $hundred = null;
                                    $digits_length = strlen($no);
                                    $i = 0;
                                    $str = array();
                                    $words = array(
                                        0 => '', 1 => 'one', 2 => 'two',
                                        3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six',
                                        7 => 'seven', 8 => 'eight', 9 => 'nine',
                                        10 => 'ten', 11 => 'eleven', 12 => 'twelve',
                                        13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen',
                                        16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen',
                                        19 => 'nineteen', 20 => 'twenty', 30 => 'thirty',
                                        40 => 'forty', 50 => 'fifty', 60 => 'sixty',
                                        70 => 'seventy', 80 => 'eighty', 90 => 'ninety'
                                    );
                                    $digits = array('', 'hundred', 'thousand', 'lakh', 'crore');
                                    while ($i < $digits_length) {
                                        $divider = ($i == 2) ? 10 : 100;
                                        $number = floor($no % $divider);
                                        $no = floor($no / $divider);
                                        $i += $divider == 10 ? 1 : 2;
                                        if ($number) {
                                            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                                            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                                            $str[] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
                                        } else $str[] = null;
                                    }
                                    $whole_number_part = implode('', array_reverse($str));
                                    $decimal_part = ($decimal > 0) ? " point " . ($words[$decimal / 10] . " " . $words[$decimal % 10]) : '';
                                    return ($whole_number_part ? $whole_number_part : '') . $decimal_part;
                                }
                                $format=$receipt_details->word_format;
                            if ($format == 'indian') {
                               echo $this->numToIndianFormat($grandTotal); 
                            }

                            if (!extension_loaded('intl')) {
                                echo '';
                            }

                            if (empty($lang)) {
                                $lang = !empty(auth()->user()) ? auth()->user()->language : 'en';
                            }

                            $f = new \NumberFormatter($lang, \NumberFormatter::SPELLOUT);
                            @endphp
							<small>({{$f->format($finalTotal)}})</small>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>

	

	<div class="border-bottom col-md-12">
	@if(empty($receipt_details->hide_price) && !empty($receipt_details->tax_summary_label) )
		<!-- tax -->
			@if(!empty($receipt_details->taxes))
				<table class="table table-slim table-bordered">
					<tr>
						<th colspan="2" class="text-center">{{$receipt_details->tax_summary_label}}</th>
					</tr>
					@foreach($receipt_details->taxes as $key => $val)
						<tr>
							<td class="text-center"><b>{{$key}}</b></td>
							{{-- Foriegn Tax --}}
							
							<td class="text-center">{{format_currency($receipt_details->foreign_taxes[$key] * $receipt_details->foreign_currency->rate,$receipt_details->foreign_currency->symbol )}} 	</td>
							
							<td class="text-center">{{$val}}</td>
						</tr>

					@endforeach
				</table>
			@endif
		@endif
	</div>

</div>
<div class="row">
<div class="col-xs-12">
				<p>{!! nl2br($request->request_note) !!}</p>
		</div>

	@if(!empty($receipt_details->footer_text))
		<div class="@if($receipt_details->show_barcode || $receipt_details->show_qr_code) col-xs-8 @else col-xs-12 @endif">
			{!! $receipt_details->footer_text !!}
		</div>
	@endif
	@if($receipt_details->show_barcode || $receipt_details->show_qr_code)
		<div class="@if(!empty($receipt_details->footer_text)) col-xs-4 @else col-xs-12 @endif text-center">
			@if($receipt_details->show_barcode)
				{{-- Barcode --}}
				<img class="center-block" src="data:image/png;base64,{{DNS1D::getBarcodePNG($invoice_no, 'C128', 2,30,array(39, 48, 54), true)}}">
			@endif

			{{--			@if($receipt_details->show_qr_code && !empty($receipt_details->qr_code_text))--}}
			{{--				<img class="center-block mt-5" src="data:image/png;base64,{{DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE', 3, 3, [39, 48, 54])}}">--}}
			{{--			@endif--}}
		</div>
	@endif
</div>
