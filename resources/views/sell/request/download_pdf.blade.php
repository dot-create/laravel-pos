@php
	$pdf_generation_for = ['Original for Buyer'];
@endphp

	<input type="hidden" id="p_code" value="{{$currency_details->code}}">
	<input type="hidden" id="p_symbol" value="{{$currency_details->symbol}}">
	<input type="hidden" id="p_thousand" value="{{$currency_details->thousand_separator}}">
	<input type="hidden" id="p_decimal" value="{{$currency_details->decimal_separator}}">

@foreach($pdf_generation_for as $pdf_for)
	<link rel="stylesheet" href="{{ asset('css/app.css?v='.$asset_v) }}">
	<style type="text/css">
		table.tpdf {
		  width: 100% !important;
		  border-collapse: collapse;
		  line-height: 1.1;
		}

		table.tpdf, table.tpdf tr, table.tpdf td, table.tpdf th {
		  border: 1px solid black;
		  padding-left: 10px;
		  padding-top: 6px;
		}
		.box {
			border: 1px solid black;
		}

	</style>
	<div class="width-100">
		<div class="width-100 f-left" align="center">
			<strong class="font-17">@lang('request.AcceptedQuote')</strong>
		</div>
		{{-- <div class="width-50 f-left" align="right">
			<strong>{{$pdf_for}}</strong>
		</div> --}}
	</div>
	<div class="width-100 box">
		<div class="width-100 mb-10 mt-10" align="center">
		</div>
		<div class="width-40 f-left" style="text-align: center;">
			@if(!empty($logo))
	          <img src="{{$logo}}" alt="Logo" style="width: 85%; height: 60%; margin: auto;padding-left: 30px;">
	        @endif
	        <div style="margin-left: 30px;margin-top: 0px;padding-top: 0px;">
	        	@if(!empty($location_details->custom_field1) && !empty($custom_labels['location']['custom_field_1']))
					{{$custom_labels['location']['custom_field_1']}} : {{$location_details->custom_field1}}
		        @endif
	        	<br>
	        	@if(!empty($business->tax_number_1))
		          <br>{{$business->tax_label_1}}: {{$business->tax_number_1}}
		        @endif

		        @if(!empty($business->tax_number_2))
		          , {{$business->tax_label_2}}: {{$business->tax_number_2}}
		        @endif
	        </div>
		</div>
		<div class="width-60 f-left" align="center" style="color: #22489B;padding-top: 5px;">
			<strong class="font-23">
	    		{!!$business->name!!}
	    	</strong>
	    	<br>
	    	{{ $location_details->name }}
	        @if(!empty($location_details->landmark))
	          <br>{{$location_details->landmark}}
	        @endif
	        @if(!empty($location_details->city) || !empty($location_details->state) || !empty($location_details->country))
	          {{implode(',', array_filter([$location_details->city, $location_details->state, $location_details->country]))}}
	        @endif
	    	@if(!empty($location_details->mobile) || !empty($location_details->alternate_number))
	    		<br>
	    		@lang('lang_v1.contact_no') : {{!empty($location_details->mobile) ? $location_details->mobile .', ': ''}} {{$location_details->alternate_number}}
	    	@endif
	    	@if(!empty($location_details->website))
	    		<br>
	    		@lang('lang_v1.website'): 
	    		<a href="{!!$location_details->website!!}" target="_blank" style="text-decoration: none;">
					{!!$location_details->website!!}
				</a>
	    	@endif
	    	@if(!empty($location_details->email))
	    		<br>@lang('business.email'): {!!$location_details->email!!}
	    	@endif
	        @if(!empty($location_details->custom_field2) && !empty($custom_labels['location']['custom_field_2']))
	          <br>{{$custom_labels['location']['custom_field_2']}} : {{$location_details->custom_field2}}
	        @endif
	        @if(!empty($location_details->custom_field3) && !empty($custom_labels['location']['custom_field_3']))
	          <br>{{$custom_labels['location']['custom_field_3']}} : {{$location_details->custom_field3}}
	        @endif
	        @if(!empty($location_details->custom_field4) && !empty($custom_labels['location']['custom_field_4']))
	          <br>{{$custom_labels['location']['custom_field_4']}} : {{$location_details->custom_field4}}
	        @endif
		</div>
	</div>
	<table class="tpdf">
		<tr>
			<td class="width-50">
				<strong>@lang('request.ref_no'):</strong> #{{ $request->request_reference }} <br>
				
			</td>
			<td class="width-50">
				{{-- <strong>Due date:</strong> {{ @format_date($purchase->due_date) }}<br> --}}
				@if(!empty($purchase->shipping_custom_field_1))
		          <strong>
		          	{{$custom_labels['shipping']['custom_field_1'] ?? ''}}:
		          </strong>
		          	{{$purchase->shipping_custom_field_1}}
		          <br>
		        @endif


				<strong>@lang('request.order_date'):</strong> {{ @format_date($request->created_at) }}
			</td>
		</tr>
		<tr>
			<td class="width-50">
				<strong>@lang('purchase.supplier')</strong> <br>
		        @php
		        	$customer_address = [];
		            if (!empty($request->contact->supplier_business_name)) {
		                $customer_address[] = $request->contact->supplier_business_name;
		            }
		            if (!empty($request->contact->address_line_1)) {
		                $customer_address[] = '<br>' . $request->contact->address_line_1;
		            }
		            if (!empty($request->contact->address_line_2)) {
		                $customer_address[] =  '<br>' . $request->contact->address_line_2;
		            }
		            if (!empty($request->contact->city)) {
		                $customer_address[] = '<br>' . $request->contact->city;
		            }
		            if (!empty($request->contact->state)) {
		                $customer_address[] = $request->contact->state;
		            }
		            if (!empty($request->contact->country)) {
		                $customer_address[] = $request->contact->country;
		            }
		            if (!empty($request->contact->zip_code)) {
		                $customer_address[] = '<br>' . $request->contact->zip_code;
		            }
		            if (!empty($request->contact->name)) {
		                $customer_address[] = '<br>' . $request->contact->name;
		            }
		            if (!empty($request->contact->mobile)) {
		                $customer_address[] = '<br>' .$request->contact->mobile;
		            }
		            if (!empty($request->contact->landline)) {
		                $customer_address[] = $request->contact->landline;
		            }
		        @endphp
		        {!! implode(', ', $customer_address) !!}
		        @if(!empty($request->contact->email))
		          <br>@lang('business.email'): {{$request->contact->email}}
		        @endif
		        @if(!empty($request->contact->tax_number))
		          <br>@lang('contact.tax_no'): {{$request->contact->tax_number}}
		        @endif
		        @if(!empty($custom_labels['contact']['custom_field_1']) && !empty($request->contact->custom_field1))
		        	<br>{{$custom_labels['contact']['custom_field_1']}} : {{$request->contact->custom_field1}}
		        @endif
		        @if(!empty($custom_labels['contact']['custom_field_2']) && !empty($request->contact->custom_field2))
		        	<br>{{$custom_labels['contact']['custom_field_2']}} : {{$request->contact->custom_field2}}
		        @endif
		        @if(!empty($custom_labels['contact']['custom_field_3']) && !empty($request->contact->custom_field3))
		        	<br>{{$custom_labels['contact']['custom_field_3']}} : {{$request->contact->custom_field3}}
		        @endif
		        @if(!empty($custom_labels['contact']['custom_field_4']) && !empty($request->contact->custom_field4))
		        	<br>{{$custom_labels['contact']['custom_field_4']}} : {{$request->contact->custom_field4}}
		        @endif
		        @if(!empty($custom_labels['contact']['custom_field_5']) && !empty($request->contact->custom_field5))
		        	<br>{{$custom_labels['contact']['custom_field_5']}} : {{$request->contact->custom_field5}}
		        @endif
		        @if(!empty($custom_labels['contact']['custom_field_6']) && !empty($request->contact->custom_field6))
		        	<br>{{$custom_labels['contact']['custom_field_6']}} : {{$request->contact->custom_field6}}
		        @endif
		        @if(!empty($custom_labels['contact']['custom_field_7']) && !empty($request->contact->custom_field7))
		        	<br>{{$custom_labels['contact']['custom_field_7']}} : {{$request->contact->custom_field7}}
		        @endif
		        @if(!empty($custom_labels['contact']['custom_field_8']) && !empty($request->contact->custom_field8))
		        	<br>{{$custom_labels['contact']['custom_field_8']}} : {{$request->contact->custom_field8}}
		        @endif
		        @if(!empty($custom_labels['contact']['custom_field_9']) && !empty($request->contact->custom_field9))
		        	<br>{{$custom_labels['contact']['custom_field_9']}} : {{$request->contact->custom_field9}}
		        @endif
		        @if(!empty($custom_labels['contact']['custom_field_10']) && !empty($request->contact->custom_field10))
		        	<br>{{$custom_labels['contact']['custom_field_10']}} : {{$request->contact->custom_field10}}
		        @endif
			</td>
			<td class="width-50">
				<strong>@lang('lang_v1.delivery_at')</strong><br>
				{!! $location_details->location_address !!}
		        <br>
		        {{--<strong>@lang('lang_v1.dispatch_from'):</strong>
				@if(!empty($request->contact->city))
					{{$request->contact->city}}
				@else
					{{'-'}}
				@endif --}}
			</td>
		</tr>
	</table>
	<div class="box">
	<table class="table-pdf td-border">
		@php
			$show_cat_code = !empty($invoice_layout->show_cat_code) && $invoice_layout->show_cat_code == 1 ? true : false;

			$show_brand = !empty($invoice_layout->show_brand) && $invoice_layout->show_brand == 1 ? true : false;

			$show_sku = !empty($invoice_layout->show_sku) && $invoice_layout->show_sku == 1 ? true : false;
		@endphp
		<thead>
			<tr class="row-border">
				<th>
					#
				</th>
				<th style="width: 40% !important;">
					{{$invoice_layout->table_product_label}}
				</th>
				@if($show_cat_code)
					<th>
						{{$invoice_layout->cat_code_label}}
					</th>
				@endif
				<th>
					{{$invoice_layout->table_qty_label}}
				</th>
				<th >
					{{$invoice_layout->table_unit_price_label}}
				</th>
				<th>
					Discount
				</th>
				<th>
					{{$invoice_layout->table_subtotal_label}}
				</th>
		</tr>
		</thead>
	 	@php 
        	$total = 0.00;
        	$is_empty_row_looped = true;
        	$tax_array = [];
      	@endphp
		@foreach($request->items as $purchase_line)
			<tr @if($loop->iteration % 2 !== 0) class="odd" @endif style="border:hidden;">
				<td>
					{{$loop->iteration}}
				</td>
				<td style="width: 40% !important;">
					{{ $purchase_line->product->name }}
	                @if( $purchase_line->product->type == 'variable')
	                  - {{ $purchase_line->variation->product_variation->name}}
	                  - {{ $purchase_line->variation->name}}
	                 @endif

	                @if($show_sku)
	                , {{$purchase_line->variation->sub_sku}}
	                @endif

	                @if($show_brand && !empty($purchase_line->product->brand))
	                , {{$purchase_line->product->brand->name ?? ''}}
	                @endif
				</td>
				@if($show_cat_code)
					<td>
						{{ $purchase_line->product->category->short_code ?? '' }}
					</td>
				@endif
				<td>
					{{@format_quantity($purchase_line->quantity)}}
				</td>
				<td>
					{{format_currency($purchase_line->total_price,$currency_details->symbol)}}
				</td>
				<td>
					@php
						if($purchase_line->discount){
							$discount=$purchase_line->discount;
							$type=$purchase_line->discount_type;
							echo $discount." ".$type;
						}
						else{
							echo 'N/A';
						}
					@endphp
				</td>
				<td>
					@php 
						$discountPrice=$purchase_line->total_price;
						if($purchase_line->discount && $purchase_line->discount != 0){
							$discount=$purchase_line->discount;
							$type=$purchase_line->discount_type;
							if($type=="fixed"){
								$discountPrice= $discountPrice - $discount;
							}
							else{
								$discountPricePercente = ($discount/$discountPrice) * 100;
								$discountPrice= $discountPrice - $discountPricePercente;
							}
						}
						
		              $total += ($discountPrice);
		              
		            @endphp
		            {{format_currency(($discountPrice),$currency_details->symbol)}}
				</td>
			</tr>
			@if(count($request->items) < 6 && $is_empty_row_looped && $loop->last)
				@php
					$i = 0;
					$is_empty_row_looped = false;
					$loop_until = 0;
					if (count($request->items) == 1) {
						$loop_until = 5;
					} elseif (count($request->items) == 2) {
						$loop_until = 4;
					} elseif (count($request->items) == 3) {
						$loop_until = 3;
					} elseif (count($request->items) == 4) {
						$loop_until = 3;
					}
				@endphp
				@for($i; $i<= $loop_until ; $i++)
					<tr style="border:hidden;">
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						@if($show_cat_code)
							<td>
								&nbsp;
							</td>
						@endif
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
				@endfor
			@endif
		@endforeach
		<tr>
			<td @if($show_cat_code) colspan="5" @else colspan="4" @endif style="text-align: center;">
				{{$invoice_layout->sub_total_label}}
			</td>
			<td colspan="1">
				<strong>
					{{format_currency($total,$currency_details->symbol)}}
				</strong>
			</td>
		</tr>
		<tr>
			<td @if($show_cat_code) colspan="3" @else colspan="2" @endif>
			<b>Note:</b>
				@foreach($request->items as $item)
					@if($item->seller_note)
		          		{{ $item->seller_note}}
					@else
						--
					@endif
				@endforeach
			</td>
			<td colspan="3">
				@if(!empty($tax_array))
		        	@foreach($tax_array as $key => $value)
		        		{{$taxes->where('id', $key)->first()->name}} ({{$taxes->where('id', $key)->first()->amount}}%) : {{format_currency(array_sum($value),$currency_details->symbol)}} <br>
		        	@endforeach
		        @endif
				{{$invoice_layout->total_label}} : {{format_currency($total,$currency_details->symbol)}}
			</td>
		</tr>
		<tr>
			<td colspan="7">
				
			</td>
		</tr>
		<tr>
			<td colspan="6">
				@if(!empty($invoice_layout->footer_text))
					{!!$invoice_layout->footer_text!!}
				@endif
			</td>
		</tr>
	</table>
	</div>
	
	@php
		$bottom = '5px';
		if (count($request->items) >= 3) {
			$bottom = '-15px';
		}
	@endphp
	@if (!$loop->last)
		<pagebreak>
	@endif
@endforeach