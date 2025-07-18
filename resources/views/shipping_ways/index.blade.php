@extends('layouts.app')
@section('title', __( 'shipping_ways.manage_shipping_ways' ))

@section('content')
<section class="content-header">
    <h1>@lang( 'shipping_ways.manage_shipping_ways' )
        <small>@lang( 'shipping_ways.manage_shipping_ways_subtitle' )</small>
    </h1>
</section>

<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'shipping_ways.all_shipping_ways' )])
        @can('shipping_ways.create')
            @slot('tool')
                <div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary btn-modal" 
                        data-href="{{ action('ShippingWayController@create') }}" 
                        data-container=".shipping_way_modal">
                        <i class="fa fa-plus"></i> @lang( 'messages.add' )
                    </button>
                </div>
            @endslot
        @endcan

        @can('shipping_ways.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="shipping_way_table">
                    <thead>
                        <tr>
                            <th>@lang( 'shipping_ways.code' )</th>
                            <th>@lang( 'shipping_ways.shipping_method' )</th>
                            <th>@lang( 'shipping_ways.freight_rate' )</th>
                            <th>@lang( 'shipping_ways.type' )</th>
                            <th>@lang( 'messages.action' )</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade shipping_way_modal" tabindex="-1" role="dialog" aria-labelledby="shippingWayModalLabel"></div>
</section>
@endsection


@section('javascript')
<script>
$(document).ready(function () {
    // Initialize DataTable
    var shipping_way_table = $('#shipping_way_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ action("ShippingWayController@index") }}',
        columns: [
            { data: 'code', name: 'code' },
            { data: 'shipping_method', name: 'shipping_method' },
            { data: 'freight_rate', name: 'freight_rate' },
            { data: 'type', name: 'type' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        drawCallback: function(settings) {
            __currency_convert_recursively($('#shipping_way_table'));
        }
    });

    // Show modal for creating shipping way
    $(document).on('click', '.btn-modal', function(e) {
        e.preventDefault();
        var container = $(this).data('container');
        $.ajax({
            url: $(this).data('href'),
            dataType: 'html',
            success: function(result) {
                $(container).html(result).modal('show');
            }
        });
    });

    // Confirm before delete
    $(document).on('submit', 'form', function (e) {
        if ($(this).hasClass('delete-confirm')) {
            e.preventDefault();
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete,
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    this.submit();
                }
            });
        }
    });

    // Handle dynamic select box changes in modals
    $(document).on('change', '#shipping_method_select', function () {
        if ($(this).val() == 'Other') {
            $('#custom_shipping_method_container').show();
            $('input[name="shipping_method"]').val('');
        } else {
            $('#custom_shipping_method_container').hide();
            $('input[name="shipping_method"]').val($(this).val());
        }
    });

    $(document).on('change', '#type_select', function () {
        if ($(this).val() == 'Other') {
            $('#custom_type_container').show();
            $('input[name="type"]').val('');
        } else {
            $('#custom_type_container').hide();
            $('input[name="type"]').val($(this).val());
        }
    });

    // Handle form submission via AJAX and reload DataTable
    $(document).on('submit', '#shipping_way_add_form, #shipping_way_edit_form', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var method = form.find('input[name="_method"]').val() || 'POST';

        $.ajax({
            method: method,
            url: url,
            data: form.serialize(),
            success: function(result) {
                if (result.success) {
                    $('.shipping_way_modal').modal('hide');
                    toastr.success(result.msg);
                    shipping_way_table.ajax.reload();
                } else {
                    toastr.error(result.msg);
                }
            },
            error: function(xhr) {
                toastr.error("@lang('messages.something_went_wrong')");
            }
        });
    });

    // Handle delete with confirmation
    $(document).on('click', '.delete_shipping_way_button', function () {
        var id = $(this).data('id');
        if (!confirm('@lang("messages.confirm_delete")')) return;

        $.ajax({
            url: $(this).data('href'),
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (result) {
                if (result.success) {
                    toastr.success(result.msg);
                    $('#shipping_way_table').DataTable().ajax.reload();
                } else {
                    toastr.error(result.msg || 'Something went wrong.');
                }
            },
            error: function () {
                toastr.error('Server error.');
            }
        });
    });
});
</script>
@endsection
