@extends('layouts.app')
@section('title', __('purchase.edit_purchase'))

@section('content')

@php
  $custom_labels = json_decode(session('business.custom_labels'), true);
@endphp
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('purchase.edit_purchase') <i class="fa fa-keyboard-o hover-q text-muted" aria-hidden="true" data-container="body" data-toggle="popover" data-placement="bottom" data-content="@include('purchase.partials.keyboard_shortcuts_details')" data-html="true" data-trigger="hover" data-original-title="" title=""></i></h1>
</section>

<!-- Main content -->
<section class="content">


  <!-- Page level currency setting -->
  <input type="hidden" id="p_code" value="{{$currency_details->code}}">
  <input type="hidden" id="p_symbol" value="{{$currency_details->symbol}}">
  <input type="hidden" id="p_thousand" value="{{$currency_details->thousand_separator}}">
  <input type="hidden" id="p_decimal" value="{{$currency_details->decimal_separator}}">

  @include('layouts.partials.error')

  {!! Form::open(['url' =>  action('PurchaseController@update' , [$purchase->id] ), 'method' => 'PUT', 'id' => 'add_purchase_form', 'files' => true ]) !!}

  @php
    $currency_precision = session('business.currency_precision', 2);
  @endphp

  <input type="hidden" id="purchase_id" value="{{ $purchase->id }}">

    @component('components.widget', ['class' => 'box-primary'])
        <div class="row">
            <div class="@if(!empty($default_purchase_status)) col-sm-4 @else col-sm-3 @endif">
              <div class="form-group">
                {!! Form::label('supplier_id', __('purchase.supplier') . ':*') !!}
                <div class="input-group">
                  <span class="input-group-addon">
                    <i class="fa fa-user"></i>
                  </span>
                  {!! Form::select('contact_id', [ $purchase->contact_id => $purchase->contact->name], $purchase->contact_id, ['class' => 'form-control', 'placeholder' => __('messages.please_select') , 'required', 'id' => 'supplier_id']); !!}
                  <span class="input-group-btn">
                    <button type="button" class="btn btn-default bg-white btn-flat add_new_supplier" data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                  </span>
                </div>
              </div>
              <strong>
                @lang('business.address'):
              </strong>
              <div id="supplier_address_div">
                {!! $purchase->contact->contact_address !!}
              </div>
            </div>

            <div class="@if(!empty($default_purchase_status)) col-sm-4 @else col-sm-3 @endif">
              <div class="form-group">
                {!! Form::label('ref_no', __('purchase.ref_no') . '*') !!}
                @show_tooltip(__('lang_v1.leave_empty_to_autogenerate'))
                {!! Form::text('ref_no', $purchase->ref_no, ['class' => 'form-control', 'required']); !!}
              </div>
            </div>
            
            <div class="@if(!empty($default_purchase_status)) col-sm-4 @else col-sm-3 @endif">
              <div class="form-group">
                {!! Form::label('transaction_date', __('purchase.purchase_date') . ':*') !!}
                <div class="input-group">
                  <span class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                  </span>
                  {!! Form::text('transaction_date', @format_datetime($purchase->transaction_date), ['class' => 'form-control', 'readonly', 'required']); !!}
                </div>
              </div>
            </div>
            
            <div class="col-sm-3 @if(!empty($default_purchase_status)) hide @endif">
              <div class="form-group">
                {!! Form::label('status', __('purchase.purchase_status') . ':*') !!}
                @show_tooltip(__('tooltip.order_status'))
                {!! Form::select('status', $orderStatuses, $purchase->status, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select') , 'required']); !!}
              </div>
            </div>
            <div class="col-sm-3">
              <div class="form-group">
                {!! Form::label('location_id', __('purchase.business_location').':*') !!}
                @show_tooltip(__('tooltip.purchase_location'))
                {!! Form::select('location_id', $business_locations, $purchase->location_id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'disabled']); !!}
              </div>
            </div>

            <!-- Currency Exchange Rate -->
            <div class="col-sm-3 @if(!$currency_details->purchase_in_diff_currency) hide @endif">
              <div class="form-group">
                {!! Form::label('exchange_rate', __('purchase.p_exchange_rate') . ':*') !!}
                @show_tooltip(__('tooltip.currency_exchange_factor'))
                <div class="input-group">
                  <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                  </span>
                  {!! Form::number('exchange_rate', $currency_details->rate, ['class' => 'form-control', 'required', 'step' => 0.001]); !!}
                </div>
                <span class="help-block text-danger">
                  @lang('purchase.diff_purchase_currency_help', ['currency' => $currency_details->name])
                </span>
              </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                  <div class="multi-input">
                    {!! Form::label('pay_term_number', __('contact.pay_term') . ':') !!} @show_tooltip(__('tooltip.pay_term'))
                    <br/>
                    {!! Form::number('pay_term_number', $purchase->pay_term_number, ['class' => 'form-control width-40 pull-left', 'placeholder' => __('contact.pay_term')]); !!}

                    {!! Form::select('pay_term_type', 
                      ['months' => __('lang_v1.months'), 
                        'days' => __('lang_v1.days')], 
                        $purchase->pay_term_type, 
                      ['class' => 'form-control width-60 pull-left','placeholder' => __('messages.please_select'), 'id' => 'pay_term_type']); !!}
                  </div>
              </div>
          </div>

            <div class="col-sm-3">
                <div class="form-group">
                    {!! Form::label('document', __('purchase.attach_document') . ':') !!}
                    {!! Form::file('document', ['id' => 'upload_document', 'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types')))]); !!}
                    <p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)])
                    @includeIf('components.document_help_text')</p>
                </div>
            </div>
        </div>
        <div class="row">
          @php
            $custom_field_1_label = !empty($custom_labels['purchase']['custom_field_1']) ? $custom_labels['purchase']['custom_field_1'] : '';

            $is_custom_field_1_required = !empty($custom_labels['purchase']['is_custom_field_1_required']) && $custom_labels['purchase']['is_custom_field_1_required'] == 1 ? true : false;

            $custom_field_2_label = !empty($custom_labels['purchase']['custom_field_2']) ? $custom_labels['purchase']['custom_field_2'] : '';

            $is_custom_field_2_required = !empty($custom_labels['purchase']['is_custom_field_2_required']) && $custom_labels['purchase']['is_custom_field_2_required'] == 1 ? true : false;

            $custom_field_3_label = !empty($custom_labels['purchase']['custom_field_3']) ? $custom_labels['purchase']['custom_field_3'] : '';

            $is_custom_field_3_required = !empty($custom_labels['purchase']['is_custom_field_3_required']) && $custom_labels['purchase']['is_custom_field_3_required'] == 1 ? true : false;

            $custom_field_4_label = !empty($custom_labels['purchase']['custom_field_4']) ? $custom_labels['purchase']['custom_field_4'] : '';

            $is_custom_field_4_required = !empty($custom_labels['purchase']['is_custom_field_4_required']) && $custom_labels['purchase']['is_custom_field_4_required'] == 1 ? true : false;
        @endphp
        @if(!empty($custom_field_1_label))
          @php
            $label_1 = $custom_field_1_label . ':';
            if($is_custom_field_1_required) {
              $label_1 .= '*';
            }
          @endphp

          <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('custom_field_1', $label_1 ) !!}
                    {!! Form::text('custom_field_1', $purchase->custom_field_1, ['class' => 'form-control','placeholder' => $custom_field_1_label, 'required' => $is_custom_field_1_required]); !!}
                </div>
            </div>
        @endif
        @if(!empty($custom_field_2_label))
          @php
            $label_2 = $custom_field_2_label . ':';
            if($is_custom_field_2_required) {
              $label_2 .= '*';
            }
          @endphp

          <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('custom_field_2', $label_2 ) !!}
                    {!! Form::text('custom_field_2', $purchase->custom_field_2, ['class' => 'form-control','placeholder' => $custom_field_2_label, 'required' => $is_custom_field_2_required]); !!}
                </div>
            </div>
        @endif
        @if(!empty($custom_field_3_label))
          @php
            $label_3 = $custom_field_3_label . ':';
            if($is_custom_field_3_required) {
              $label_3 .= '*';
            }
          @endphp

          <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('custom_field_3', $label_3 ) !!}
                    {!! Form::text('custom_field_3', $purchase->custom_field_3, ['class' => 'form-control','placeholder' => $custom_field_3_label, 'required' => $is_custom_field_3_required]); !!}
                </div>
            </div>
        @endif
        @if(!empty($custom_field_4_label))
          @php
            $label_4 = $custom_field_4_label . ':';
            if($is_custom_field_4_required) {
              $label_4 .= '*';
            }
          @endphp

          <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('custom_field_4', $label_4 ) !!}
                    {!! Form::text('custom_field_4', $purchase->custom_field_4, ['class' => 'form-control','placeholder' => $custom_field_4_label, 'required' => $is_custom_field_4_required]); !!}
                </div>
            </div>
        @endif
        </div>
        @if(!empty($common_settings['enable_purchase_order']))
        <div class="row">
          <div class="col-sm-3">
            <div class="form-group">
              {!! Form::label('purchase_order_ids', __('lang_v1.purchase_order').':') !!}
              {!! Form::select('purchase_order_ids[]', $purchase_orders, $purchase->purchase_order_ids, ['class' => 'form-control select2', 'multiple', 'id' => 'purchase_order_ids']); !!}
            </div>
          </div>
        </div>
        @endif
    @endcomponent

    @component('components.widget', ['class' => 'box-primary'])
        <div class="row">
            <div class="col-sm-2 text-center">
              <button type="button" class="btn btn-primary btn-flat" data-toggle="modal" data-target="#import_purchase_products_modal">@lang('product.import_products')</button>
            </div>
            <div class="col-sm-8">
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon">
                    <i class="fa fa-search"></i>
                  </span>
                  {!! Form::text('search_product', null, ['class' => 'form-control mousetrap', 'id' => 'search_product', 'placeholder' => __('lang_v1.search_product_placeholder'), 'autofocus']); !!}
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

        <div class="row">
            <div class="col-sm-12">
              @include('purchase.partials.edit_purchase_entry_row')

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
                      <span id="total_subtotal" class="display_currency">{{$purchase->total_before_tax}}</span>
                      <!-- This is total before purchase tax-->
                      <input type="hidden" id="total_subtotal_input" value="{{$purchase->total_before_tax}}" name="total_before_tax">
                    </td>
                  </tr>
                </table>
              </div>

            </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary'])
        <div class="row">
            <div class="col-sm-12">
                <table class="table">
                  <tr>
                    <td class="col-md-3">
                      <div class="form-group">
                        {!! Form::label('discount_type', __( 'purchase.discount_type' ) . ':') !!}
                        {!! Form::select('discount_type', [ '' => __('lang_v1.none'), 'fixed' => __( 'lang_v1.fixed' ), 'percentage' => __( 'lang_v1.percentage' )], $purchase->discount_type, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
                      </div>
                    </td>
                    <td class="col-md-3">
                      <div class="form-group">
                      {!! Form::label('discount_amount', __( 'purchase.discount_amount' ) . ':') !!}
                      {!! Form::text('discount_amount', 
                      ($purchase->discount_type == 'fixed' ? 
                        number_format($purchase->discount_amount * $currency_details->rate, 2)
                      :
                        $purchase->discount_amount
                      )
                      , ['class' => 'form-control input_number']); !!}
                      </div>
                    </td>
                    <td class="col-md-3">
                      &nbsp;
                    </td>
                    <td class="col-md-3">
                      <b>Discount:</b>(-) 
                      <span id="discount_calculated_amount" class="display_currency">0</span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <div class="form-group">
                      {!! Form::label('tax_id', __( 'purchase.purchase_tax' ) . ':') !!}
                      <select name="tax_id" id="tax_id" class="form-control select2" placeholder="'Please Select'">
                        <option value="" data-tax_amount="0" selected>@lang('lang_v1.none')</option>
                        @foreach($taxes as $tax)
                          <option value="{{ $tax->id }}" @if($purchase->tax_id == $tax->id) {{'selected'}} @endif data-tax_amount="{{ $tax->amount }}"
                          >
                            {{ $tax->name }}
                          </option>
                        @endforeach
                      </select>
                      {!! Form::hidden('tax_amount', $purchase->tax_amount, ['id' => 'tax_amount']); !!}
                      </div>
                    </td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>
                      <b>@lang( 'purchase.purchase_tax' ):</b>(+) 
                      <span id="tax_calculated_amount" class="display_currency">0</span>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="4">
                      <div class="form-group">
                        {!! Form::label('additional_notes',__('purchase.additional_notes')) !!}
                        {!! Form::textarea('additional_notes', $purchase->additional_notes, ['class' => 'form-control', 'rows' => 3]); !!}
                      </div>
                    </td>
                  </tr>

                </table>
            </div>
        </div>
    @endcomponent
    @component('components.widget', ['class' => 'box-primary'])
    <div class="row">
      <div class="col-md-4">
        <div class="form-group">
        {!! Form::label('shipping_details', __( 'purchase.shipping_details' ) . ':') !!}
        {!! Form::text('shipping_details', $purchase->shipping_details, ['class' => 'form-control']); !!}
        </div>
      </div>
      <div class="col-md-4 col-md-offset-4">
        <div class="form-group">
          {!! Form::label('shipping_charges','(+) ' . __( 'purchase.additional_shipping_charges') . ':') !!}
          {!! Form::text('shipping_charges', number_format($purchase->shipping_charges * $currency_details->rate, 2), ['class' => 'form-control input_number']); !!}
          </div>
      </div>
    </div>
    <div class="row">
          @php
            $shipping_custom_label_1 = !empty($custom_labels['purchase_shipping']['custom_field_1']) ? $custom_labels['purchase_shipping']['custom_field_1'] : '';

            $is_shipping_custom_field_1_required = !empty($custom_labels['purchase_shipping']['is_custom_field_1_required']) && $custom_labels['purchase_shipping']['is_custom_field_1_required'] == 1 ? true : false;

            $shipping_custom_label_2 = !empty($custom_labels['purchase_shipping']['custom_field_2']) ? $custom_labels['purchase_shipping']['custom_field_2'] : '';

            $is_shipping_custom_field_2_required = !empty($custom_labels['purchase_shipping']['is_custom_field_2_required']) && $custom_labels['purchase_shipping']['is_custom_field_2_required'] == 1 ? true : false;

            $shipping_custom_label_3 = !empty($custom_labels['purchase_shipping']['custom_field_3']) ? $custom_labels['purchase_shipping']['custom_field_3'] : '';
            
            $is_shipping_custom_field_3_required = !empty($custom_labels['purchase_shipping']['is_custom_field_3_required']) && $custom_labels['purchase_shipping']['is_custom_field_3_required'] == 1 ? true : false;

            $shipping_custom_label_4 = !empty($custom_labels['purchase_shipping']['custom_field_4']) ? $custom_labels['purchase_shipping']['custom_field_4'] : '';
            
            $is_shipping_custom_field_4_required = !empty($custom_labels['purchase_shipping']['is_custom_field_4_required']) && $custom_labels['purchase_shipping']['is_custom_field_4_required'] == 1 ? true : false;

            $shipping_custom_label_5 = !empty($custom_labels['purchase_shipping']['custom_field_5']) ? $custom_labels['purchase_shipping']['custom_field_5'] : '';
            
            $is_shipping_custom_field_5_required = !empty($custom_labels['purchase_shipping']['is_custom_field_5_required']) && $custom_labels['purchase_shipping']['is_custom_field_5_required'] == 1 ? true : false;
        @endphp

        @if(!empty($shipping_custom_label_1))
          @php
            $label_1 = $shipping_custom_label_1 . ':';
            if($is_shipping_custom_field_1_required) {
              $label_1 .= '*';
            }
          @endphp

          <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('shipping_custom_field_1', $label_1 ) !!}
                    {!! Form::text('shipping_custom_field_1', $purchase->shipping_custom_field_1 ?? null, ['class' => 'form-control','placeholder' => $shipping_custom_label_1, 'required' => $is_shipping_custom_field_1_required]); !!}
                </div>
            </div>
        @endif
        @if(!empty($shipping_custom_label_2))
          @php
            $label_2 = $shipping_custom_label_2 . ':';
            if($is_shipping_custom_field_2_required) {
              $label_2 .= '*';
            }
          @endphp

          <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('shipping_custom_field_2', $label_2 ) !!}
                    {!! Form::text('shipping_custom_field_2', $purchase->shipping_custom_field_2 ?? null, ['class' => 'form-control','placeholder' => $shipping_custom_label_2, 'required' => $is_shipping_custom_field_2_required]); !!}
                </div>
            </div>
        @endif
        @if(!empty($shipping_custom_label_3))
          @php
            $label_3 = $shipping_custom_label_3 . ':';
            if($is_shipping_custom_field_3_required) {
              $label_3 .= '*';
            }
          @endphp

          <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('shipping_custom_field_3', $label_3 ) !!}
                    {!! Form::text('shipping_custom_field_3', $purchase->shipping_custom_field_3 ?? null, ['class' => 'form-control','placeholder' => $shipping_custom_label_3, 'required' => $is_shipping_custom_field_3_required]); !!}
                </div>
            </div>
        @endif
        @if(!empty($shipping_custom_label_4))
          @php
            $label_4 = $shipping_custom_label_4 . ':';
            if($is_shipping_custom_field_4_required) {
              $label_4 .= '*';
            }
          @endphp

          <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('shipping_custom_field_4', $label_4 ) !!}
                    {!! Form::text('shipping_custom_field_4', $purchase->shipping_custom_field_4 ?? null, ['class' => 'form-control','placeholder' => $shipping_custom_label_4, 'required' => $is_shipping_custom_field_4_required]); !!}
                </div>
            </div>
        @endif
        @if(!empty($shipping_custom_label_5))
          @php
            $label_5 = $shipping_custom_label_5 . ':';
            if($is_shipping_custom_field_5_required) {
              $label_5 .= '*';
            }
          @endphp

          <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('shipping_custom_field_5', $label_5 ) !!}
                    {!! Form::text('shipping_custom_field_5', $purchase->shipping_custom_field_5 ?? null, ['class' => 'form-control','placeholder' => $shipping_custom_label_5, 'required' => $is_shipping_custom_field_5_required]); !!}
                </div>
            </div>
        @endif
        </div>

    <div class="row">
      <div class="col-md-12 text-center">
        <button type="button" class="btn btn-primary btn-sm" id="toggle_additional_expense"> <i class="fas fa-plus"></i> @lang('lang_v1.add_additional_expenses') <i class="fas fa-chevron-down"></i></button>
      </div>
      <div class="col-md-8 col-md-offset-4" id="additional_expenses_div">
        <table class="table table-condensed">
          <thead>
            <tr>
              <th>@lang('lang_v1.additional_expense_name')</th>
              <th>@lang('sale.amount')</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                {!! Form::text('additional_expense_key_1', $purchase->additional_expense_key_1, ['class' => 'form-control', 'id' => 'additional_expense_key_1']); !!}
              </td>
              <td>
                {!! Form::text('additional_expense_value_1', number_format(str_replace(",", "", $purchase->additional_expense_value_1) * $currency_details->rate, 2), ['class' => 'form-control input_number', 'id' => 'additional_expense_value_1']); !!}
              </td>
            </tr>
            <tr>
              <td>
                {!! Form::text('additional_expense_key_2', $purchase->additional_expense_key_2, ['class' => 'form-control', 'id' => 'additional_expense_key_2']); !!}
              </td>
              <td>
                {!! Form::text('additional_expense_value_2', number_format(str_replace(",", "", $purchase->additional_expense_value_2) * $currency_details->rate, 2), ['class' => 'form-control input_number', 'id' => 'additional_expense_value_2']); !!}
              </td>
            </tr>
            <tr>
              <td>
                {!! Form::text('additional_expense_key_3', $purchase->additional_expense_key_3, ['class' => 'form-control', 'id' => 'additional_expense_key_3']); !!}
              </td>
              <td>
                {!! Form::text('additional_expense_value_3', number_format(str_replace(",", "", $purchase->additional_expense_value_3) * $currency_details->rate, 2), ['class' => 'form-control input_number', 'id' => 'additional_expense_value_3']); !!}
              </td>
            </tr>
            <tr>
              <td>
                {!! Form::text('additional_expense_key_4', $purchase->additional_expense_key_4, ['class' => 'form-control', 'id' => 'additional_expense_key_4']); !!}
              </td>
              <td>
                {!! Form::text('additional_expense_value_4', number_format(str_replace(",", "", $purchase->additional_expense_value_4) * $currency_details->rate, 2), ['class' => 'form-control input_number', 'id' => 'additional_expense_value_4']); !!}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="row">
    <div class="col-md-12 text-right">
      {!! Form::hidden('final_total', $purchase->final_total , ['id' => 'grand_total_hidden']); !!}
      <b>@lang('purchase.purchase_total'): </b><span id="grand_total" class="display_currency" data-currency_symbol='true'>{{$purchase->final_total}}</span>
    </div>
    </div>
    @endcomponent
  
    <div class="row">
        <div class="col-sm-12 text-center">
          <button type="button" id="submit_purchase_form" class="btn btn-primary btn-big">@lang('messages.update')</button>
        </div>
    </div>
{!! Form::close() !!}
</section>
<!-- /.content -->
<!-- quick product modal -->
<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>
<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
  @include('contact.create', ['quick_add' => true])
</div>
@include('purchase.partials.import_purchase_products_modal')
@endsection

@section('javascript')
  <script src="{{ asset('js/purchase.js?v=' . $asset_v) }}"></script>
  <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
  <script type="text/javascript">
    $(document).ready( function(){
      update_table_total();
      update_grand_total();
      __page_leave_confirmation('#add_purchase_form');
    });
  </script>
  @include('purchase.partials.keyboard_shortcuts')
@endsection
