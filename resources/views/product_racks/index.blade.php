@extends('layouts.app')
@section('title', __( 'product_racks.manage_racks' ))

@section('content')
<section class="content-header">
    <h1>@lang('product_racks.manage_racks')
        <small>@lang('product_racks.manage_racks_subtitle')</small>
    </h1>
</section>

<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'product_racks.all_racks' )])
        @can('product_racks.create')
            @slot('tool')
                <div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary btn-modal"
                        data-href="{{ action('ProductRackController@create') }}"
                        data-container=".product_rack_modal">
                        <i class="fa fa-plus"></i> @lang( 'messages.add' )
                    </button>
                </div>
            @endslot
        @endcan

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="product_rack_table">
                <thead>
                    <tr>
                        <th>@lang('product_racks.location')</th>
                        <th>@lang('product_racks.product')</th>
                        <th>@lang('product_racks.storage_location')</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent

    <div class="modal fade product_rack_modal" tabindex="-1" role="dialog" aria-labelledby="productRackModalLabel"></div>
</section>
@endsection

@section('javascript')
<script type="text/javascript">
$(document).ready(function(){
    $('#product_rack_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ action("ProductRackController@get") }}',
        columns: [
            { data: 'location', name: 'location' },
            { data: 'product', name: 'product' },
            { data: 'storage_location', name: 'storage_location' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
    });

    $(document).on('click', '.btn-modal', function(e){
        e.preventDefault();
        var container = $(this).data("container");
        $.ajax({
            url: $(this).data("href"),
            dataType: "html",
            success: function(result){
                $(container).html(result).modal('show');
            }
        });
    });

    $(document).on('submit', 'form#product_rack_form', function(e){
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var method = form.attr('method');

        $.ajax({
            method: method,
            url: url,
            data: form.serialize(),
            success: function(result){
                if(result.success){
                    $('.product_rack_modal').modal('hide');
                    $('#product_rack_table').DataTable().ajax.reload();
                    toastr.success(result.msg);
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });

    $(document).on('click', '.delete-product-rack', function () {
        var id = $(this).data('id');
        if (confirm('@lang("messages.are_you_sure")')) {
            $.ajax({
                method: 'DELETE',
                url: '/product-racks/' + id,
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(result) {
                    if(result.success){
                        $('#product_rack_table').DataTable().ajax.reload();
                        toastr.success(result.msg);
                    }
                }
            });
        }
    });
});
</script>
@endsection