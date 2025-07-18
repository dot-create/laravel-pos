@extends('layouts.app')
@section('title', 'Reset Database')

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Reset Database</h1>
        <!-- <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
            <li class="active">Here</li>
        </ol> -->
    </section>

    <!-- Main content -->
    
<section class="content">
    <form method="post" action="{{route('reset_database_delete')}}">
        @csrf
        <div class="row">
            <div class="col-md-12 text-center">
                <!--<div class="col-md-6" id="location_filter">-->
                <!--    <div class="form-group">-->
                <!--        {!! Form::label('location_id',  __('purchase.business_location') . ':') !!}-->
                <!--        {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}-->
                <!--    </div>-->
                <!--</div>-->
                <!--<div class="col-md-6" style="margin-top: 22px">-->
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Reset </button>
                    </div>
                <!--</div>-->
            </div>
        </div>
    </form>
    
</section>


@endsection
