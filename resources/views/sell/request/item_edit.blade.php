@extends('layouts.app')
@section('title', __('request.pending_request_items'))

@section('content')

@php
	$custom_labels = json_decode(session('business.custom_labels'), true);
@endphp
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('request.pending_request_items_edit') <i class="fa fa-keyboard-o hover-q text-muted" aria-hidden="true" data-container="body" data-toggle="popover" data-placement="bottom" data-content="@include('purchase.partials.keyboard_shortcuts_details')" data-html="true" data-trigger="hover" data-original-title="" title=""></i></h1>
</section>
<!-- Main content -->
<section class="content">
@if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="box  box-solid ">
        <div class="box-body">
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="">{!! Form::label('ref_no', __('request.customer').':') !!}</label>
                        <input type="text" class="form-control" value="{{$requestItem->request->contact? $requestItem->request->contact :''}}" disabled>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="">{!! Form::label('ref_no', __('request.reference').':') !!}</label>
                        <input type="text" class="form-control" value="{{$requestItem->request->request_reference}}" disabled>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="">{{__('request.product_name')}}</label>
                        <input type="text" class="form-control" value="{{$requestItem->product->name}}">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="">{{__('request.product_type')}}</label>
                        <input type="text" class="form-control" value="{{$requestItem->product->type}}">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="">{{__('request.product_sku')}}</label>
                        <input type="text" class="form-control" value="{{$requestItem->variation->sub_sku}}">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="">{{__('request.product_tax_type')}}</label>
                        <input type="text" class="form-control" value="{{$requestItem->product->tax_type}}">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="">{{__('request.product_stock')}}</label>
                        @php
                        $variationdetail=$requestItem->variation->variation_location_details->where('location_id',$requestItem->request->business_location_id)->pluck('qty_available');
                        $qty=$avaliabilityQty;
                        $stockOnHand=$qty;
                        $status="Not Available";
                        $remainingQty=$requestItem->quantity;
                        if($qty >= $requestItem->quantity){
                            $status="Avaliable";
                        }
                        else if($qty != 0 && $qty > 0){
                            $status="Partial Avalaible";
                            if($qty > $requestItem->quantity){
                                $remainingQty= $qty - $requestItem->quantity;
                            }
                            else{
                                $remainingQty= $requestItem->quantity - $qty;
                            }
                        }
                        else{
                            $remainingQty=$requestItem->quantity - $qty;
                        }
                        @endphp
                        <!-- <span>{{$status}}</span>x   -->
                        <input type="text" class="form-control" value="{{$status}}" readonly>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="">Remaining Quantity</label>
                        <input type="text" class="form-control" value="{{$remainingQty}}" readonly>
                    </div>
                </div>
            </div>
            <!-- <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <button type="button" class="btn btn-primary" id="add_vendor">{{__('request.add_vendor')}}</button>
                    </div>
                </div>
            </div> -->
            <form action="{{route('request.item.update',$requestItem->id)}}" method="POST" id="itemForm">
                @csrf
                <div class="row">
                    <div class="vendorDynamic">
                        <div id="supplierSection-1" class="supplier-section p-3 border rounded mb-2">
                            <input type="hidden" name="is_best_supplier1" value="{{$requestItem->is_best_supplier1==true? '1' : '0'}}">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input compare-checkbox" data-id="1" {{$requestItem->is_best_supplier1==true? 'checked' :""}}>
                                        <label class="form-check-label">Include in Comparison</label>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Select Vendor</label>
                                        <select name="supplier1" class="form-control supplierSelect1 select2" data-id="1" required>
                                            <option value="" selected disabled>Select Supplier</option>
                                            @foreach($suppliers as $key=>$value)
                                                <option value="{{$key}}" data-name="{{$value}}" {{$requestItem->supplier1_id== $key? 'selected':''}}>{{$value}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="number" name="quantity1" class="form-control supplier-input" data-id="1" required value="{{old('quantity1',$requestItem->quantity_supplier1?? '')}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Price</label>
                                        <input type="number" name="price1" class="form-control supplier-input" data-id="1" required value="{{$requestItem->unit_price_supplier1?? ''}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Freight</label>
                                        <input type="number" name="freight1" class="form-control supplier-input" data-id="1" required value="{{$requestItem->freight_supplier1?? ''}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Ecommerce Fees Percentage</label>
                                        <input type="number" name="ecommerce_fees_percentage1" class="form-control supplier-input" data-id="1" required value="{{$requestItem->ecom_fee_percentage_supplier1?? ''}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Formula Price (Auto-calculated)</label>
                                        <input type="number" name="formula_price1" class="form-control formula-price"  data-id="1" readonly  value="{{$requestItem->formula_price_supplier1?? '0.0'}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Delivery Time</label>
                                        <input type="text" name="delivery_time1" class="form-control delivery_time"  data-id="1" value="{{$requestItem->delivery_time1}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="">{{__('request.product_link')}}</label>
                                        <input type="text" name="product_link1" class="form-control"  data-id="1" required value="{{$requestItem->product_link1 }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="supplierSection-2" class="supplier-section p-3 border rounded mb-2">
                            <input type="hidden" name="is_best_supplier2" value="{{$requestItem->is_best_supplier2==true? '1' : '0'}}">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input compare-checkbox" data-id="2" {{$requestItem->is_best_supplier2==true? 'checked' :""}}>
                                        <label class="form-check-label">Include in Comparison</label>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Select Vendor</label>
                                        <select name="supplier2" class="form-control supplierSelect2 select2" data-id="2" >
                                            <option value="" selected disabled>Select Supplier</option>
                                            @foreach($suppliers as $key=>$value)
                                                <option value="{{$key}}" data-name="{{$value}}" {{$requestItem->supplier2_id== $key? 'selected':''}}>{{$value}}>{{$value}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="number" name="quantity2" class="form-control supplier-input" data-id="2"  value="{{$requestItem->quantity_supplier2?? ''}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Price</label>
                                        <input type="number" name="price2" class="form-control supplier-input" data-id="2"  value="{{$requestItem->unit_price_supplier2?? ''}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Freight</label>
                                        <input type="number" name="freight2" class="form-control supplier-input" data-id="2"  value="{{$requestItem->freight_supplier2?? ''}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Ecommerce Fees Percentage</label>
                                        <input type="number" name="ecommerce_fees_percentage2" class="form-control supplier-input" data-id="2"  value="{{$requestItem->ecom_fee_percentage_supplier2?? ''}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Formula Price (Auto-calculated)</label>
                                        <input type="number" name="formula_price2" class="form-control formula-price" data-id="2" readonly value="{{$requestItem->formula_price_supplier2?? '0.0'}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Delivery Time</label>
                                        <input type="text" name="delivery_time2" class="form-control delivery_time"  data-id="2" value="{{$requestItem->delivery_time2?? ''}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="">{{__('request.product_link')}}</label>
                                        <input type="text" name="product_link2" class="form-control"  data-id="2"  value="{{$requestItem->product_link2?? ''}}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="supplierSection-3" class="supplier-section p-3 border rounded mb-2">
                            <input type="hidden" name="is_best_supplier3" value="{{$requestItem->is_best_supplier3==true? '1' : '0'}}">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input compare-checkbox" data-id="3" {{$requestItem->is_best_supplier3==true? 'checked' :""}}>
                                        <label class="form-check-label">Include in Comparison</label>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Select Vendor</label>
                                        <select name="supplier3" class="form-control supplierSelect3 select2" data-id="3" >
                                            <option value="" selected disabled>Select Supplier</option>
                                            @foreach($suppliers as $key=>$value)
                                                <option value="{{$key}}" data-name="{{$value}}" {{$requestItem->supplier3_id== $key? 'selected':''}}>{{$value}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="number" name="quantity3" class="form-control supplier-input" data-id="3"  value="{{$requestItem->quantity_supplier3?? ''}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Price</label>
                                        <input type="number" name="price3" class="form-control supplier-input" data-id="3"  value="{{$requestItem->unit_price_supplier3?? ''}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Freight</label>
                                        <input type="number" name="freight3" class="form-control supplier-input" data-id="3"  value="{{$requestItem->freight_supplier3?? ''}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Ecommerce Fees Percentage</label>
                                        <input type="number" name="ecommerce_fees_percentage3" class="form-control supplier-input" data-id="3"  value="{{$requestItem->ecom_fee_percentage_supplier3?? ''}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Formula Price (Auto-calculated)</label>
                                        <input type="number" name="formula_price3" class="form-control formula-price"  data-id="3" readonly value="{{$requestItem->formula_price_supplier3?? ''}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Delivery Time</label>
                                        <input type="text" name="delivery_time3" class="form-control delivery_time"  data-id="3" value="{{$requestItem->delivery_time3?? ''}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="">{{__('request.product_link')}}</label>
                                        <input type="text" name="product_link3" class="form-control"  data-id="3"  value="{{$requestItem->product_link3?? ''}}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="supplierSection-4" class="supplier-section p-3 border rounded mb-2">
                            <input type="hidden" name="is_best_supplier4" value="{{$requestItem->is_best_supplier4==true? '1' : '0'}}">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input compare-checkbox" data-id="4" {{$requestItem->is_best_supplier4==true? 'checked' :""}}>
                                        <label class="form-check-label">Include in Comparison</label>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Select Vendor</label>
                                        <select name="supplier4" class="form-control supplierSelect4v select2" data-id="4" >
                                            <option value="" selected disabled>Select Supplier</option>
                                            @foreach($suppliers as $key=>$value)
                                                <option value="{{$key}}" data-name="{{$value}}" {{$requestItem->supplier4_id== $key? 'selected':''}}>{{$value}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="number" name="quantity4" class="form-control supplier-input" data-id="4"  value="{{$requestItem->quantity_supplier4?? ''}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Price</label>
                                        <input type="number" name="price4" class="form-control supplier-input" data-id="4"  value="{{$requestItem->unit_price_supplier4?? ''}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Freight</label>
                                        <input type="number" name="freight4" class="form-control supplier-input" data-id="4"  value="{{$requestItem->freight_supplier4?? ''}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Ecommerce Fees Percentage</label>
                                        <input type="number" name="ecommerce_fees_percentage4" class="form-control supplier-input" data-id="4"  value="{{$requestItem->ecom_fee_percentage_supplier4?? ''}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Formula Price (Auto-calculated)</label>
                                        <input type="number" name="formula_price4" class="form-control formula-price" data-id="4" readonly value="{{$requestItem->formula_price_supplier4?? '0.0'}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Delivery Time</label>
                                        <input type="text" name="delivery_time4" class="form-control delivery_time" data-id="4" value="{{$requestItem->delivery_time4?? ''}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="">{{__('request.product_link')}}</label>
                                        <input type="text" name="product_link4" class="form-control" data-id="4"  value="{{$requestItem->product_link4?? ''}}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="bestSupplierDiv" style="display:none">
                        <ul>
                            <li ><label for="">Best Supplier:</label><span id="bestSupplierDetails"></span></li>
                            <li ><label for="">Quantity:</label><span id="bestSupplierDetailsQuantity"></span></li>
                            <li ><label for="">Price:</label><span id="bestSupplierDetailsPrice"></span></li>
                            <li ><label for="">Delivery Time:</label><span id="bestSupplierDelivery"></span></li>
                            <li ><label for="">Product Link:</label><span id="bestSupplierProductLink"></span></li>
                        </ul>
                    </div>
                </div>
                <hr>
                <div class="row">
                <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">{{__('request.item_notes')}}</label>
                            <input type="text" name="item_notes" class="form-control" required value="{{$requestItem->item_notes?? ''}}">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">{{__('request.item_supply_ref')}}</label>
                            <input type="text" name="supply_ref" class="form-control"  value="{{$requestItem->supply_ref?? ''}}" required>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">{{__('request.purchase weight')}}</label>
                            <input type="string" name="purchase_weight" class="form-control" required id="purchase_weight" value="{{$requestItem->purchase_weight?? ''}}"
                            inputmode="decimal" pattern="^\d*\.?\d*$" oninput="this.value=this.value.replace(/[^0-9.]/g,'')">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">{{__('request.weight_unit')}}</label>
                            <select name="weight_unit" id="weight_unit" class="form-control" name="weight_unit" required>
                                <option value="" selected disabled>Select weight unit</option>
                                @foreach($units as $unit)
                                    <option value="{{$unit->code}}" data-rate="{{$unit->equivalent_to_lb}}" {{$requestItem->weight_unit==$unit->code? 'selected' : ''}}>{{$unit->code}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">{{__('request.shippingway')}}</label>
                            <select name="shippingway" class="form-control" required id="shippingway">
                                <option value="" selected disabled>Select shippingWay</option>
                                @foreach($shippingways as $shipp)
                                    <option value="{{$shipp->code}}" data-type="{{$shipp->type}}" data-rate="{{$shipp->freight_rate}}" {{$requestItem->shipping_way== $shipp->code? 'selected':''}}>{{$shipp->code}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">{{__('request.Estimated Forwarder Freight')}}</label>
                            <input type="text" name="est_fwd_freight" class="form-control" id="estimated_Forwarder_Freight" value="{{$requestItem->est_fwd_freight?? ''}}" required
                            inputmode="decimal" pattern="^\d*\.?\d*$" oninput="this.value=this.value.replace(/[^0-9.]/g,'')">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">{{__('request.destination_tax')}}</label>
                            <select class="form-control" name="destination_tax" required id="destination_tax">
                                <option value="" selected disabled>Select weight unit</option>
                                @foreach($taxes as $value)
                                    <option value="{{$value->amount}}" {{$requestItem->destination_tax==$value->amount? 'selected' : ''}}>{{$value->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">{{__('request.total_cost')}}</label>
                            <input type="string" name="total_price" class="form-control" required id="total_cost" value="{{$requestItem->total_price?? ''}}"
                            inputmode="decimal" pattern="^\d*\.?\d*$" oninput="this.value=this.value.replace(/[^0-9.]/g,'')">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">{{__('request.suggested_sell_price_USD_wot')}}</label>
                            <input type="text" name="suggested_sell_price_USD_wot" class="form-control" required id="suggested_sell_price_USD_wot" value="{{$requestItem->suggested_sell_price_USD_wot?? ''}}"
                            inputmode="decimal" pattern="^\d*\.?\d*$" oninput="this.value=this.value.replace(/[^0-9.]/g,'')">
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <!-- <div class="form-group"> -->
                            <!-- <label for="">{{__('request.delivery_time')}}</label> -->
                            <input type="hidden" name="delivery_time" id="delivery_time" class="form-control" required value="{{$requestItem->delivery_time?? ''}}">
                        <!-- </div> -->
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">{{__('request.sell_price_wot')}}</label>
                            <input type="string" id="sell_price_wot" name="sell_price_wot" class="form-control" required value="{{$requestItem->sell_price_wot?? ''}}"
                            inputmode="decimal" pattern="^\d*\.?\d*$" oninput="this.value=this.value.replace(/[^0-9.]/g,'')">
                        </div>
                    </div>
                    
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <input type="hidden" name="status" id="status">
                            <button class="btn btn-success save-btn" data-status="Save">Save</button>
                            <button class="btn btn-success save-btn" data-status="reject">Reject</button>
                            <button class="btn btn-success save-btn" data-status="Supplier-Confirmed">Ready To Draft</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
    </div>
</section>
<!-- /.content -->
@endsection

@section('javascript')
	<script src="{{ asset('js/purchase.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
	<script type="text/javascript">
        //IF(OR(R9="";T9="");"NO HAY INFO";((T9+(U9/S9))*V9)+(T9+(U9/S9)))
        //((price + (freight/quantity)) * tax fee) +price +(freight/quantity)
        let suppliers=<?php echo json_encode($suppliers); ?>;
        let defaultBusinessProfit=parseFloat("{{$business->default_profit_percent}}");
        console.log('default bussines profit', defaultBusinessProfit);
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
        // Remove vendor section dynamically
        // $(document).on('click', '.removeVendor', function () {
        //     let vendorId = $(this).data('id');
        //     $('#supplierSection-' + vendorId).remove();
        //     // Remove from suppliersData array
        //     suppliersData = suppliersData.filter(s => s.id !== vendorId);
        //     suggestBestSupplier();
        // });
        // $(document).on('click', '#add_vendor', function () {
           
        // });
        let vendorCount = 1;
        for(let i=1;i<=4;i++){
            appendVendor();
            vendorCount++; // Unique identifier for each supplier
        }
        function appendVendor() {
           

            // let supplierOptions = '<option value="" disabled selected>Select supplier</option>';
            // $.each(suppliers, function (key, value) {
            //     supplierOptions += `<option value="${key}">${value}</option>`;
            // });

            // let html = `
            //     <div id="supplierSection-${vendorCount}" class="supplier-section p-3 border rounded mb-2">
            //         <input type="hidden" name="is_best_supplier${vendorCount}" value="0">
            //         <div class="row">
            //             <div class="col-lg-12">
            //                 <div class="form-check">
            //                     <input type="checkbox" class="form-check-input compare-checkbox" data-id="${vendorCount}">
            //                     <label class="form-check-label">Include in Comparison</label>
            //                 </div>
            //             </div>
            //             <div class="col-lg-6">
            //                 <div class="form-group">
            //                     <label>Select Vendor</label>
            //                     <select name="supplier${vendorCount}" class="form-control supplierSelect" data-id="${vendorCount}" required>
            //                         ${supplierOptions}
            //                     </select>
            //                 </div>
            //             </div>
            //             <div class="col-lg-6">
            //                 <div class="form-group">
            //                     <label>Quantity</label>
            //                     <input type="number" name="quantity${vendorCount}" class="form-control supplier-input" data-id="${vendorCount}" required>
            //                 </div>
            //             </div>
            //             <div class="col-lg-6">
            //                 <div class="form-group">
            //                     <label>Price</label>
            //                     <input type="number" name="price${vendorCount}" class="form-control supplier-input" data-id="${vendorCount}" required>
            //                 </div>
            //             </div>
            //             <div class="col-lg-6">
            //                 <div class="form-group">
            //                     <label>Freight</label>
            //                     <input type="number" name="freight${vendorCount}" class="form-control supplier-input" data-id="${vendorCount}" required>
            //                 </div>
            //             </div>
            //             <div class="col-lg-6">
            //                 <div class="form-group">
            //                     <label>Ecommerce Fees Percentage</label>
            //                     <input type="number" name="ecommerce_fees_percentage${vendorCount}" class="form-control supplier-input" data-id="${vendorCount}" required>
            //                 </div>
            //             </div>
            //             <div class="col-lg-6">
            //                 <div class="form-group">
            //                     <label>Formula Price (Auto-calculated)</label>
            //                     <input type="text" name="formula_price${vendorCount}" class="form-control formula-price" data-id="${vendorCount}" readonly>
            //                 </div>
            //             </div>
            //         </div>
            //     </div>`;

            // $('.vendorDynamic').append(html);
        }

        // Function to calculate the best supplier among selected ones
        function suggestBestSupplier() {
            let bestSupplierId = null;
            let bestScore = Infinity;

            $(".compare-checkbox:checked").each(function () {
                let id = $(this).data("id");

                // Ensure valid numerical values, default to 0 if NaN
                let price = parseFloat($(`input[name='price${id}']`).val()) || 0;
                let freight = parseFloat($(`input[name='freight${id}']`).val()) || 0;
                let quantity = parseFloat($(`input[name='quantity${id}']`).val()) || 1;
                let taxFee = parseFloat($(`input[name='ecommerce_fees_percentage${id}']`).val()) || 0;

                // Ensure quantity is at least 1 to prevent division errors
                quantity = Math.max(quantity, 1);
                
                // Convert percentage to decimal
                let taxMultiplier = taxFee / 100;
                
                // Calculate total cost safely
                let perUnitFreight = freight / quantity;
                let totalCost = ((price + perUnitFreight) * taxMultiplier) + price + perUnitFreight;
                
                // Ensure totalCost is a valid number (avoid NaN issues)
                totalCost = isNaN(totalCost) ? 0 : totalCost;

                // Update the formula price field
                let formulaInput = $(`input[name='formula_price${id}']`);
                formulaInput.val(totalCost.toFixed(2));

                // Determine best supplier based on lowest cost
                if (totalCost < bestScore) {
                    bestScore = totalCost;
                    bestSupplierId = id;
                }
            });

            // Reset all "is_best_supplier" values to 0
            $(".compare-checkbox:checked").each(function () {
                let id = $(this).data("id");
                $(`input[name='is_best_supplier${id}']`).val(id === bestSupplierId ? "1" : "0");
            });

            // Remove previous highlights
            $(".formula-price").removeClass("bg-success text-white border-success");

            // Highlight the best supplier's formula price field
            if (bestSupplierId !== null) {
                $('#bestSupplierDiv').css('display', 'block');

                let name = $(`.supplierSelect${bestSupplierId} option:selected`).data('name') || "N/A";
                let price = $(`input[name='formula_price${bestSupplierId}']`).val() || "0";
                let quantity = $(`input[name='quantity${bestSupplierId}']`).val() || "1";
                let deliveryTime = $(`input[name='delivery_time${bestSupplierId}']`).val() || "N/A";
                let productLink = $(`input[name='product_link${bestSupplierId}']`).val() || "N/A";

                $('#bestSupplierDetails').text(name);
                $('#bestSupplierDetailsPrice').text(price);
                $('#bestSupplierDetailsQuantity').text(quantity);
                $('#bestSupplierDelivery').text(deliveryTime);
                $('#bestSupplierProductLink').text(productLink);

                $(`#delivery_time`).val(deliveryTime);
                $(`input[name='is_best_supplier${bestSupplierId}']`).val("1");
            }
        }


        // Event Listeners
        $(document).on("input", ".supplier-input", function () {
            suggestBestSupplier();
        });
        $(document).ready(function(){
            suggestBestSupplier();
        })
        let value=$('#purchase_weight').val();
        let purchaseWeight=$('#weight_unit').val();
        let purchaseWeightRate=$('#weight_unit option:selected').data('rate');
        if(purchaseWeight !="" && purchaseWeight !=undefined ){
            if(purchaseWeight !="KG"){
                $('#estimated_Forwarder_Freight').val(parseFloat(purchaseWeightRate) * parseFloat(value));
            }else{
                $('#estimated_Forwarder_Freight').val(parseFloat(purchaseWeightRate) * parseFloat(value));
            }
        }
        function calculateSugestedSellPrice(){
            calculateEstimatedFreight();
            let shippingway= $('#shippingway option:selected')
            console.log(shippingway);
            let type=$(shippingway).data('type');
            let rate=$(shippingway).data('rate');
            let estFreightValue=isNaN($('#estimated_Forwarder_Freight').val())? 0.0: $('#estimated_Forwarder_Freight').val();
            let bestPrice=parseFloat($('#bestSupplierDetailsPrice').text());
            let bestQuantity=parseFloat($('#bestSupplierDetailsQuantity').text());
            let calculateValue=0;
            let estprice=0;
            debugger;
            console.log('type',type,'rate',rate);
            if(type=="Variable"){
                calculateValue=parseFloat(rate) * parseFloat(estFreightValue);
                estprice= parseFloat(calculateValue * bestQuantity);
            }else{
                calculateValue=parseFloat(rate);
                estprice=calculateValue;
            }
            
            let destinationTax=$('#destination_tax option:selected');
            let destinationTaxValue=parseFloat($(destinationTax).val()).toFixed(4);
            
            $('#estimated_Forwarder_Freight').val(estprice);

            let finalPrice=parseFloat((estprice/bestQuantity) + bestPrice).toFixed(4);
            let finalTotalCostWithDestinationTax=parseFloat((finalPrice * destinationTaxValue +1) / 100).toFixed(4);
            
            
            let totalCost = (parseFloat(finalTotalCostWithDestinationTax) + parseFloat(finalPrice)).toFixed(4);
            let result = (totalCost * defaultBusinessProfit +1) / 100;
            console.log('%',result);
            console.log('totalCost',totalCost);
            $('#suggested_sell_price_USD_wot').val((parseFloat(totalCost) + parseFloat(result)).toFixed(4));
            $('#total_cost').val(totalCost);
            $('#sell_price_wot').val((parseFloat(totalCost) + parseFloat(result)).toFixed(4));
            
        }
        function calculateEstimatedFreight() {

            let value=$('#purchase_weight').val() || 0; //2
            console.log('value',value);
            let purchaseWeight=$('#weight_unit').val() || 'KG'; // LB
            if (value !== "" && purchaseWeight !== "") {
                let calculate = parseFloat($('#weight_unit option:selected').data('rate')) * parseFloat(value); 
                
                $('#estimated_Forwarder_Freight').val(calculate.toFixed(4));
                // if($('#shippingway').val() !==""){
                //     calculateSugestedSellPrice();
                // }
            } else {
                $('#estimated_Forwarder_Freight').val(0.00);
            }
        }
        $('#purchase_weight, #weight_unit').on('change', calculateSugestedSellPrice);
        $('#shippingway').on('change',function(){
            calculateSugestedSellPrice();
        })
        $('#destination_tax').on('change',function(){
            calculateSugestedSellPrice();
        })
        $(document).on("change", ".compare-checkbox", function () {
            suggestBestSupplier();
        });
        document.querySelectorAll('.save-btn').forEach(button => {
            button.addEventListener('click', function () {
                let status = this.getAttribute('data-status'); // Get status from button
                const form = document.getElementById('itemForm');

                if (form.reportValidity()) {
                    document.getElementById('status').value = status; // Set hidden input
                    form.submit(); // Submit only if valid
                }
                // Else: form is invalid, browser will show validation messages
            });
        });
	</script>
	@include('purchase.partials.keyboard_shortcuts')
@endsection
