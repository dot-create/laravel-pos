@extends('layouts.app')
@section('title', __('request.pending_request_items'))

@section('content')

@php
	$custom_labels = json_decode(session('business.custom_labels'), true);
@endphp
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('request.view_request') <i class="fa fa-keyboard-o hover-q text-muted" aria-hidden="true" data-container="body" data-toggle="popover" data-placement="bottom" data-content="@include('purchase.partials.keyboard_shortcuts_details')" data-html="true" data-trigger="hover" data-original-title="" title=""></i></h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="box  box-solid ">
        <div class="box-body">
            <div class="row">
            <div class="col-sm-6">
                    <div class="form-group">
                        <label for="">{{__('request.customer')}}</label>
                        <input type="text" class="form-control" value="{{$request->contact->name}}" disabled>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="">{!! Form::label('ref_no', __('request.reference').':') !!}</label>
                        <input type="text" class="form-control" value="{{$request->request_reference}}" disabled>
                    </div>
                </div>
                
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="">{{__('request.description')}}</label>
                        <textarea name="" class="form-control" cols="20" rows="5" readonly>{{$request->description}}</textarea>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="">{{__('request.status')}}: <span>{{$request->status}}</span></label>
                        
                    </div>
                </div>
            </div>
            <hr>
            
            <!-- Pending Quantities Summary -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="box box-info">
                        <div class="box-header">
                            <h3 class="box-title">
                                <i class="fa fa-users"></i> Pending Items Summary by Assigned User
                            </h3>
                        </div>
                        <div class="box-body">
                            <div id="pending-summary" class="row">
                                <!-- Summary will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <h2>Items List</h2>
            
            <!-- Filters -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="box box-default">
                        <div class="box-header">
                            <h3 class="box-title">
                                <i class="fa fa-filter"></i> Filters
                            </h3>
                        </div>
                        <div class="box-body">
                            <form id="filterForm" class="form-inline">
                                <div class="form-group">
                                    <label for="filter_assigned_user">Assigned User:</label>
                                    <select class="form-control" id="filter_assigned_user" name="assigned_user">
                                        <option value="">All Users</option>
                                        <option value="unassigned" {{ (request('assigned_user') == 'unassigned') ? 'selected' : '' }}>Unassigned</option>
                                        @foreach($companyUsers as $user)
                                            <option value="{{$user['id']}}" {{ (request('assigned_user') == $user['id']) ? 'selected' : '' }}>
                                                {{$user['name']}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="filter_status">Status:</label>
                                    <select class="form-control" id="filter_status" name="status">
                                        <option value="">All Statuses</option>
                                        @foreach($statuses as $status)
                                            <option value="{{$status}}" {{ (request('status') == $status) ? 'selected' : '' }}>
                                                {{$status}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <button type="button" class="btn btn-primary" id="applyFilters">
                                    <i class="fa fa-search"></i> Apply Filters
                                </button>
                                
                                <button type="button" class="btn btn-default" id="clearFilters">
                                    <i class="fa fa-times"></i> Clear
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="box box-primary">
                        <div class="box-header">
                            <h3 class="box-title">
                                Items (<span id="items-count">{{count($items)}}</span>)
                            </h3>
                        </div>
                        <div class="box-body">
                            <table class="table table-bordered table-striped ajax_view" id="items-table" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>@lang( 'product.product_name' )</th>
                                        <th>@lang( 'product.weight' )</th>
                                        <th>@lang( 'request.requested_quantity' )</th>
                                        <th>@lang( 'request.avaliable_stock' )</th>
                                        <th>@lang( 'request.status' )</th>
                                        <th>Assigned To</th>
                                        <th>@lang( 'request.action' )</th>
                                        <!-- <th><i class="fa fa-trash" aria-hidden="true"></i></th> -->
                                    </tr>
                                </thead>
                                <tbody id="items-tbody">
                                @include('sell.request.partials.items_table_rows')
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</section>

<!-- Assignment Modal -->
<div class="modal fade" id="assignUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Assign User</h4>
            </div>
            <div class="modal-body">
                <form id="assignUserForm">
                    <input type="hidden" id="assign_item_id" name="item_id">
                    <div class="form-group">
                        <label for="assign_user_id">Select User:</label>
                        <select class="form-control" id="assign_user_id" name="user_id" required>
                            <option value="">-- Select User --</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAssignment">Assign</button>
            </div>
        </div>
    </div>
</div>

<!-- /.content -->
@endsection

@section('javascript')
	<script src="{{ asset('js/purchase.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
	<script type="text/javascript">
		$(document).ready( function(){
      		__page_leave_confirmation('#add_purchase_form');
      		$('.paid_on').datetimepicker({
                format: moment_date_format + ' ' + moment_time_format,
                ignoreReadonly: true,
            });

            // Load company users on page load
            loadCompanyUsers();
            
            // Load pending quantities summary
            loadPendingSummary();
            
            // Load initial table data
            loadTableData(false);
    	});

        // Load pending quantities summary
        function loadPendingSummary() {
            $.ajax({
                url: '{{ route("request.pending-qty-by-users") }}',
                type: 'GET',
                success: function(data) {
                    var html = '';
                    if (data.length > 0) {
                        data.forEach(function(item) {
                            var badgeClass = item.user_id ? 'bg-blue' : 'bg-gray';
                            html += '<div class="col-sm-3 col-xs-6">' +
                                   '<div class="small-box ' + badgeClass + '">' +
                                   '<div class="inner">' +
                                   '<h3>' + item.total_quantity + '</h3>' +
                                   '<p>' + item.user_name + '<br><small>' + item.items_count + ' items</small></p>' +
                                   '</div>' +
                                   '<div class="icon">' +
                                   '<i class="fa fa-user"></i>' +
                                   '</div>' +
                                   '</div>' +
                                   '</div>';
                        });
                    } else {
                        html = '<div class="col-sm-12"><p class="text-muted">No pending items found.</p></div>';
                    }
                    $('#pending-summary').html(html);
                },
                error: function() {
                    toastr.error('Failed to load pending summary');
                }
            });
        }

        // Load table data based on current filters
        function loadTableData(applyFilters = false) {
            let filters = {};

            if (applyFilters) {
                filters = {
                    assigned_user: $('#filter_assigned_user').val(),
                    status: $('#filter_status').val()
                };
            

                $.ajax({
                    url: '{{ route("request.get-filtered-items", $request->id) }}',
                    type: 'GET',
                    data: filters,
                    success: function(response) {
                        if (response.success) {
                            $('#items-tbody').html(response.html);
                            $('#items-count').text(response.count);
                        }
                    },
                    error: function() {
                        toastr.error('Failed to load items');
                    }
                });
            }
        }


        // Handle filter application
        $('#applyFilters').on('click', function() {
            loadTableData(true);
        });

        // Handle filter clearing
        $('#clearFilters').on('click', function() {
            $('#filter_assigned_user').val('');
            $('#filter_status').val('');
            loadTableData(false);
        });

        // Auto-apply filters on change
        $('#filter_assigned_user, #filter_status').on('change', function() {
            loadTableData();
        });
        function loadCompanyUsers() {
            $.ajax({
                url: '{{ route("request.get-company-users") }}',
                type: 'GET',
                success: function(users) {
                    var options = '<option value="">-- Select User --</option>';
                    users.forEach(function(user) {
                        options += '<option value="' + user.id + '">' + user.name + '</option>';
                    });
                    $('#assign_user_id').html(options);
                },
                error: function() {
                    toastr.error('Failed to load users');
                }
            });
        }

        // Handle assign user button click
        $(document).on('click', '.assign-user, .change-assignment', function() {
            var itemId = $(this).closest('.assigned-user-section').data('item-id');
            $('#assign_item_id').val(itemId);
            $('#assignUserModal').modal('show');
        });

        // Handle assignment confirmation
        $('#confirmAssignment').on('click', function() {
            var formData = {
                item_id: $('#assign_item_id').val(),
                user_id: $('#assign_user_id').val(),
                _token: '{{ csrf_token() }}'
            };

            if (!formData.user_id) {
                toastr.error('Please select a user');
                return;
            }

            $.ajax({
                url: '{{ route("request.assign-user") }}',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        // Update the UI
                        var assignedSection = $('.assigned-user-section[data-item-id="' + formData.item_id + '"]');
                        var newHtml = '<span class="assigned-user-name">' + 
                                     response.assigned_user.name + 
                                     '</span> ' +
                                     '<button class="btn btn-xs btn-default change-assignment" title="Change Assignment">' +
                                     '<i class="fa fa-edit"></i>' +
                                     '</button>';
                        assignedSection.html(newHtml);
                        
                        $('#assignUserModal').modal('hide');
                        toastr.success(response.message);
                        
                        // Refresh the summary and table data
                        loadPendingSummary();
                        loadTableData();
                    }
                },
                error: function() {
                    toastr.error('Failed to assign user');
                }
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
	</script>
	@include('purchase.partials.keyboard_shortcuts')
@endsection