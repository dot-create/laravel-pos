@extends('layouts.app')
@section('title', __('Edit Currency'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('Edit Currency')</h1>
</section>

<!-- Main content -->
<section class="content no-print">
	{!! Form::open(['url' => action('CurrencyController@update', [$currency->id]), 'method' => 'put', 'id' => 'currency_form' ]) !!}
	<div class="box box-solid">
		<div class="box-body">
			<div class="row">
				<div class="col-sm-4">
					<div class="form-group">
						<label for="country">Country</label>
						<input type="text" class="form-control" name="country" id="country" value="{{ $currency->country }}">
					</div>
				</div>
				
				<div class="col-sm-4">
					<div class="form-group">
						<label for="currency">Currency</label>
						<input type="text" class="form-control" name="currency" id="currency" value="{{ $currency->currency }}">
					</div>
				</div>
				
				<div class="col-sm-4">
					<div class="form-group">
						<label for="code">Code</label>
						<input type="text" class="form-control" name="code" id="code" value="{{ $currency->code }}">
					</div>
				</div>
				
				<div class="col-sm-4">
					<div class="form-group">
						<label for="symbol">Symbol</label>
						<input type="text" class="form-control" name="symbol" id="symbol" value="{{ $currency->symbol }}">
					</div>
				</div>
				
				<div class="col-sm-4">
					<div class="form-group">
						<label for="rate">Rate</label>
						<input type="text" class="form-control" name="rate" id="rate" value="{{ $currency->rate }}">
					</div>
				</div>
				
				<div class="col-sm-4">
					<div class="form-group">
						<label for="rate">Thousand Separator</label>
						<input type="text" class="form-control" name="thousand_separator" id="thousand_separator" value="{{ $currency->thousand_separator }}">
					</div>
				</div>
				
				<div class="col-sm-4">
					<div class="form-group">
						<label for="rate">Decimal Separator</label>
						<input type="text" class="form-control" name="decimal_separator" id="decimal_separator" value="{{ $currency->decimal_separator }}">
					</div>
				</div>
				
				<div class="col-sm-4">
					<div class="form-group">
						<label for="rate">Decimal Precision</label>
						<input type="text" class="form-control" name="decimal_precision" id="decimal_precision" value="{{ $currency->decimal_precision }}">
					</div>
				</div>

			</div>
			<div class="row">
				<div class="col-sm-4">
					<input type="submit" class="btn btn-primary" value="Update Currency">
				</div>
			    
			</div>
		</div>
	</div> <!--box end-->
	
	{!! Form::close() !!}
</section>
@stop
@section('javascript')
	<!--<script src="{{ asset('js/stock_transfer.js?v=' . $asset_v) }}"></script>-->
	<!--<script type="text/javascript">-->
	<!--	__page_leave_confirmation('#stock_transfer_form');-->
	<!--</script>-->
@endsection
