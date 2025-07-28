@if(!session('business.enable_price_tax')) 
  @php
    $default = 0;
    $class = 'hide';
  @endphp
@else
  @php
    $default = null;
    $class = '';
  @endphp
@endif

<div class="table-responsive">
    <table class="table table-bordered add-product-price-table table-condensed {{$class}}">
        <tr>
          <th>@lang('product.default_purchase_price')</th>
          <th>@lang('product.profit_percent') @show_tooltip(__('tooltip.profit_percent'))</th>
          <th>@lang('product.default_selling_price')</th>
          @if(empty($quick_add))
            <th>@lang('lang_v1.product_image')</th>
          @endif
        </tr>
        <tr>
          <td>
            <div class="col-sm-6">
              {!! Form::label('single_dpp', trans('product.exc_of_tax') . ':*') !!}

              {!! Form::text('single_dpp', $default, ['class' => 'form-control input-sm dpp input_number', 'placeholder' => __('product.exc_of_tax'), 'required']); !!}
            <br>
                {!! Form::label('estimated_supplier_freight', trans('Estimated Supplier Freigt Value') . ':*') !!}

                {!! Form::text('estimated_supplier_freight', $default, ['class' => 'form-control input-sm dpp input_number', 'placeholder' => __('Estimated Supplier Freight Value'), 'required']); !!}

                <br>

                    <div class="form-group">
                        {!! Form::label('estimated_forwarfer_fright',  __('Estimated Forwarder Freight') . ':') !!}
                        {!! Form::number('estimated_forwarfer_fright', !empty($duplicate_product->estimated_forwarfer_fright) ? $duplicate_product->estimated_forwarfer_fright : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.preparation_time_in_minutes')]); !!}
                    </div>

                         </div>

            <div class="col-sm-6">
              {!! Form::label('single_dpp_inc_tax', trans('product.inc_of_tax') . ':*') !!}
            
              {!! Form::text('single_dpp_inc_tax', $default, ['class' => 'form-control input-sm dpp_inc_tax input_number', 'placeholder' => __('product.inc_of_tax'), 'required']); !!}
                <br>
                {!! Form::label('international_supplier_tax', trans('International Supplier TAX') . ':*') !!}

                {!! Form::text('international_supplier_tax', $default, ['class' => 'form-control input-sm dpp input_number', 'placeholder' => __('International Supplier TAX'), 'required']); !!}
                <br>
                <div class="form-group">
                    {!! Form::label('destination_tax',  __('Destination Tax') . ':') !!}
                    {!! Form::number('destination_tax', !empty($duplicate_product->destination_tax) ? $duplicate_product->destination_tax : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.preparation_time_in_minutes')]); !!}
                </div>
            </div>
          </td>

          <td>
            <br/>
            {!! Form::text('profit_percent', @num_format($profit_percent), ['class' => 'form-control input-sm input_number', 'id' => 'profit_percent', 'required']); !!}
              <br>
              {!! Form::label('purchase_site_commission', trans(' Purchasing site Commission %') . ':*') !!}

              {!! Form::text('purchase_site_commission', $default, ['class' => 'form-control input-sm dpp input_number', 'placeholder' => __(' Purchasing site Commision %'), 'required']); !!}
              <br>
              <div class="form-group">
                  {!! Form::label('type', __('Shipping Way') . ':*') !!}<br>
                  {!! Form::select('shipping_way', $shippingList, !empty($duplicate_product->shipping_way) ? $duplicate_product->shipping_way : null, ['class' => 'form-control select2 w-100',
                  'required', 'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add', 'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0']); !!}
              </div>
          </td>

          <td>
            <label><span class="dsp_label">@lang('product.exc_of_tax')</span></label>
            {!! Form::text('single_dsp', $default, ['class' => 'form-control input-sm dsp input_number', 'placeholder' => __('product.exc_of_tax'), 'id' => 'single_dsp', 'required']); !!}

            {!! Form::text('single_dsp_inc_tax', $default, ['class' => 'form-control input-sm hide input_number', 'placeholder' => __('product.inc_of_tax'), 'id' => 'single_dsp_inc_tax', 'required']); !!}
         <br>
              {!! Form::label('cif_cost', trans('CIF COST') . ':*') !!}

              {!! Form::text('cif_cost', $default, ['class' => 'form-control input-sm dpp input_number', 'placeholder' => __('CIF COST'), 'required']); !!}
              <br>
              <div class="form-group">
                  {!! Form::label('type', __('Weigh Unit Of Measure') . ':*') !!} @show_tooltip(__('tooltip.product_type'))
                  {!! Form::select('weight_unit_of_measure', $weightUnits, !empty($duplicate_product->weight_unit_of_measure) ? $duplicate_product->weight_unit_of_measure : null, ['class' => 'form-control select2',
                  'required', 'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add', 'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0']); !!}
              </div>
              {!! Form::label('purchasing_weight', trans(' Purchasing Weight') . ':*') !!}

              {!! Form::text('purchasing_weight', $default, ['class' => 'form-control input-sm dpp input_number', 'placeholder' => __(' Purchasing site Purchasing Weight'), 'required']); !!}
              <br>
          </td>
          @if(empty($quick_add))
          <td>
              <div class="form-group">
                {!! Form::label('variation_images', __('lang_v1.product_image') . ':') !!}
                {!! Form::file('variation_images[]', ['class' => 'variation_images', 'accept' => 'image/*', 'multiple']); !!}
                <small><p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]) <br> @lang('lang_v1.aspect_ratio_should_be_1_1')</p></small>
              </div>

              <div class="form-group">
                  {!! Form::label('total_cost',  __('Total Cost') . ':') !!}
                  {!! Form::number('total_cost', !empty($duplicate_product->total_cost) ? $duplicate_product->total_cost : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.preparation_time_in_minutes')]); !!}
              </div>
          </td>
          @endif
        </tr>
    </table>
</div>