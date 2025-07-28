@extends('layouts.app')
@section('title', __('Add Currency'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('Add Currency')</h1>
</section>

<!-- Main content -->
<section class="content no-print">
	<form action="{{ action('CurrencyController@store') }}" method="POST" id="currency_form">
	    @csrf
	<div class="box box-solid">
		<div class="box-body">
			<div class="row">
				<div class="col-sm-4">
					<div class="form-group">
						<label for="country">Country</label>
						<input type="text" class="form-control" name="country" id="country" value="">
					</div>
				</div>
				
				<div class="col-sm-4">
					<div class="form-group">
						<label for="currency">Currency</label>
						<input type="text" class="form-control" name="currency" id="currency" value="">
					</div>
				</div>
				
				<div class="col-sm-4">
					<div class="form-group">
						<label for="code">Code</label>
						<input type="text" class="form-control" name="code" id="code" value="">
					</div>
				</div>
				
				<div class="col-sm-4">
					<div class="form-group">
						<label for="symbol">Symbol</label>
						<input type="text" class="form-control" name="symbol" id="symbol" value="">
					</div>
				</div>
				
				<div class="col-sm-4">
					<div class="form-group">
						<label for="rate">Rate</label>
						<input type="text" class="form-control" name="rate" id="rate" value="">
					</div>
				</div>
				
				<div class="col-sm-4">
					<div class="form-group">
						<label for="rate">Thousand Separator</label>
						<input type="text" class="form-control" name="thousand_separator" id="thousand_separator" value="">
					</div>
				</div>
				
				<div class="col-sm-4">
					<div class="form-group">
						<label for="rate">Decimal Separator</label>
						<input type="text" class="form-control" name="decimal_separator" id="decimal_separator" value="">
					</div>
				</div>
				
				<div class="col-sm-4">
					<div class="form-group">
						<label for="rate">Decimal Precision</label>
						<input type="text" class="form-control" name="decimal_precision" id="decimal_precision" value="">
					</div>
				</div>

			</div>
			<div class="row">
				<div class="col-sm-4">
					<input type="submit" class="btn btn-primary" value="Save Currency">
				</div>
			    
			</div>
		</div>
	</div> <!--box end-->
	
	</form>
</section>
@stop
@section('javascript')
	<!--<script src="{{ asset('js/stock_transfer.js?v=' . $asset_v) }}"></script>-->
	<!--<script type="text/javascript">-->
	<!--	__page_leave_confirmation('#stock_transfer_form');-->
	<!--</script>-->
@endsection
