{{-- Enhanced expense/index.blade.php --}}
@extends('layouts.app')
@section('title', __('expense.expenses'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('expense.expenses')</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
                @if(auth()->user()->can('all_expense.access'))
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('location_id',  __('purchase.business_location') . ':') !!}
                            {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                        </div>
                    </div>

                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('expense_for', __('expense.expense_for').':') !!}
                            {!! Form::select('expense_for', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('expense_contact_filter',  __('contact.contact') . ':') !!}
                            {!! Form::select('expense_contact_filter', $contacts, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                        </div>
                    </div>
                @endif
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('expense_category_id',__('expense.expense_category').':') !!}
                        {!! Form::select('expense_category_id', $categories, null, ['placeholder' =>
                        __('report.all'), 'class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'expense_category_id']); !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('expense_sub_category_id_filter',__('product.sub_category').':') !!}
                        {!! Form::select('expense_sub_category_id_filter', $sub_categories, null, ['placeholder' =>
                        __('report.all'), 'class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'expense_sub_category_id_filter']); !!}
                    </div>
                </div>

                {{-- Enhanced Date Filters --}}
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('expense_date_range', __('Creation Date Range') . ':') !!}
                        {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'expense_date_range', 'readonly']); !!}
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('expense_payment_date_range', __('Payment Date Range') . ':') !!}
                        {!! Form::text('payment_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'expense_payment_date_range', 'readonly']); !!}
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('expense_payment_status',  __('purchase.payment_status') . ':') !!}
                        {!! Form::select('expense_payment_status', ['paid' => __('lang_v1.paid'), 'due' => __('lang_v1.due'), 'partial' => __('lang_v1.partial')], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('expense.all_expenses')])
                @can('expense.add')
                    @slot('tool')
                        <div class="box-tools">
                            <button id="open_expense_modal" class="btn btn-primary">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </button>
                            
                            {{-- Export buttons --}}
                            <div class="btn-group" style="margin-left: 10px;">
                                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-download"></i> Export <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="#" id="export_excel"><i class="fa fa-file-excel-o"></i> Excel</a></li>
                                    <li><a href="#" id="export_pdf"><i class="fa fa-file-pdf-o"></i> PDF</a></li>
                                </ul>
                            </div>
                        </div>
                    @endslot
                @endcan
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="expense_table">
                        <thead>
                            <tr>
                                <th>@lang('messages.action')</th>
                                <th>@lang('messages.date')</th>
                                <th>@lang('purchase.ref_no')</th>
                                <th>@lang('lang_v1.recur_details')</th>
                                <th>@lang('expense.expense_category')</th>
                                <th>@lang('product.sub_category')</th>
                                <th>@lang('business.location')</th>
                                <th>@lang('sale.payment_status')</th>
                                <th>@lang('Tax Details')</th>
                                <th>@lang('sale.total_amount')</th>
                                <th>@lang('purchase.payment_due')</th>
                                <th>@lang('expense.expense_for')</th>
                                <th>@lang('contact.contact')</th>
                                <th>@lang('Contact Company')</th>
                                <th>@lang('expense.expense_note')</th>
                                <th>@lang('lang_v1.added_by')</th>
                                <th>@lang('Last Payment Date')</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr class="bg-gray font-17 text-center footer-total">
                                <td colspan="8"><strong>@lang('sale.total'):</strong></td>
                                <td class="footer_payment_status_count"></td>
                                <td class="footer_expense_total"></td>
                                <td class="footer_total_due"></td>
                                <td colspan="6"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>

</section>

<div id="expense_modal_container"></div>

<!-- Payment Modal -->
<div class="modal fade add_payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

@stop

@section('javascript')
<script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
@endsection