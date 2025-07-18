@extends('layouts.app')
@section('title', __('lang_v1.list_currency'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>@lang('lang_v1.list_currency')</h1>
</section>

<!-- Main content -->
<section class="content no-print">
    
    @component('components.widget', ['class' => 'box-primary', 'title' => __('All Currencies')])
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary" href="{{action('CurrencyController@create')}}">
                    <i class="fa fa-plus"></i> @lang('messages.add')</a>
                </div>
            @endslot

        <table class="table table-bordered table-striped ajax_view" id="currencies_table" style="width: 100%;">
            <thead>
                <tr>
                    <th>@lang('Action')</th>
                    <th>@lang('Country')</th>
                    <th>@lang('Currency')</th>
                    <th>@lang('Code')</th>
                    <th>@lang('Symbol')</th>
                    <th>@lang('Rate')</th>
                </tr>
            </thead>
        </table>
    @endcomponent
    <div class="modal fade edit_pso_status_modal" tabindex="-1" role="dialog"></div>
</section>
<!-- /.content -->
@stop
@section('javascript')	
<!--@includeIf('purchase_order.common_js')-->
<script type="text/javascript">
    $(document).ready( function(){
        //Purchase table
        var currency_table = $('#currencies_table').DataTable({
            processing: true,
            serverSide: true,
            aaSorting: [[1, 'desc']],
            scrollY: "75vh",
            scrollX:        true,
            scrollCollapse: true,
            ajax: {
                url: '{{action("CurrencyController@index")}}'
            },
            columns: [
                { data: 'action', name: 'action', orderable: false, searchable: false },
                { data: 'country', name: 'country' },
                { data: 'currency', name: 'currency' },
                { data: 'code', name: 'code' },
                { data: 'symbol', name: 'symbol' },
                { data: 'rate', name: 'rate' },
            ]
        });

    //     $(document).on('click', 'a.delete-purchase-order', function(e) {
    //         e.preventDefault();
    //         swal({
    //             title: LANG.sure,
    //             icon: 'warning',
    //             buttons: true,
    //             dangerMode: true,
    //         }).then(willDelete => {
    //             if (willDelete) {
    //                 var href = $(this).attr('href');
    //                 $.ajax({
    //                     method: 'DELETE',
    //                     url: href,
    //                     dataType: 'json',
    //                     success: function(result) {
    //                         if (result.success == true) {
    //                             toastr.success(result.msg);
    //                             purchase_order_table.ajax.reload();
    //                         } else {
    //                             toastr.error(result.msg);
    //                         }
    //                     },
    //                 });
    //             }
    //         });
    //     });
    });
</script>
@endsection