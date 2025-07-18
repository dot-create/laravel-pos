@extends('layouts.app')
@section('title', __( 'weight_units.manage_weight_units' ))

@section('content')

<section class="content-header">
    <h1>@lang( 'weight_units.manage_weight_units' )
        <small>@lang( 'weight_units.manage_weight_units_subtitle' )</small>
    </h1>
</section>

<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'weight_units.all_units' )])
    @can('weight_units.create')
    @slot('tool')
    <div class="box-tools">
        <button type="button" class="btn btn-block btn-primary btn-modal"
            data-href="{{ action('WeightUnitController@create') }}"
            data-container=".weight_unit_modal">
            <i class="fa fa-plus"></i> @lang( 'messages.add' )
        </button>
    </div>
    @endslot
    @endcan

    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="weight_unit_table">
            <thead>
                <tr>
                    <th>@lang( 'weight_units.code' )</th>
                    <th>@lang( 'weight_units.unit_name' )</th>
                    <th>@lang( 'weight_units.equivalent_to_lb' )</th>
                    <th>@lang( 'messages.action' )</th>
                </tr>
            </thead>
        </table>
    </div>
    @endcomponent

    <div class="modal fade weight_unit_modal" tabindex="-1" role="dialog" aria-labelledby="weightUnitModalLabel"></div>

</section>

@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        var weight_unit_table = $('#weight_unit_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("weight-units.get") }}',
            columns: [{
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'unit_name',
                    name: 'unit_name'
                },
                {
                    data: 'equivalent_to_lb',
                    name: 'equivalent_to_lb'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        // Show modal for create or edit
        $(document).on('click', '.btn-modal', function(e) {
            e.preventDefault();
            var container = $(this).data("container");
            $.ajax({
                url: $(this).data("href"),
                dataType: "html",
                success: function(result) {
                    $(container).html(result).modal('show');
                }
            });
        });

        // Handle create & edit form submit
        $(document).on('submit', '#weight_unit_add_form, #weight_unit_edit_form', function(e) {
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
                        $('.weight_unit_modal').modal('hide');
                        toastr.success(result.msg);
                        weight_unit_table.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                },
                error: function(xhr) {
                    toastr.error('@lang("messages.something_went_wrong")');
                }
            });
        });


        // Handle delete with confirmation
        $(document).on('click', '.delete-weight-unit', function () {
            var id = $(this).data('id');
            if (!confirm('@lang("messages.confirm_delete")')) return;

            $.ajax({
                url: '/weight-units/' + id,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (result) {
                    if (result.success) {
                        toastr.success(result.msg);
                        $('#weight_unit_table').DataTable().ajax.reload();
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