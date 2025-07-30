<?php

namespace App\Http\Controllers;

use App\ShippingWay;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ShippingWayController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('shipping_ways.view') && !auth()->user()->can('shipping_ways.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $shipping_ways = ShippingWay::select(['id', 'code', 'shipping_method', 'freight_rate', 'type']);

            return DataTables::of($shipping_ways)
                ->addColumn('action', function ($row) {
                    $html = '';
                    if (auth()->user()->can('shipping_ways.update')) {
                        $html .= '<button data-href="' . action('ShippingWayController@edit', [$row->id]) . '" class="btn btn-xs btn-primary btn-modal" data-container=".shipping_way_modal"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</button>';
                    }
                    if (auth()->user()->can('shipping_ways.delete')) {
                        $html .= ' <button data-href="' . action('ShippingWayController@destroy', [$row->id]) . '" class="btn btn-xs btn-danger delete_shipping_way_button"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                    }
                    return $html;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('shipping_ways.index');
    }

    public function create()
    {
        if (!auth()->user()->can('shipping_ways.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('shipping_ways.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('shipping_ways.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $request->validate([
                'code' => 'required|unique:shipping_ways,code',
                'shipping_method' => 'required',
                'freight_rate' => 'required|numeric',
                'type' => 'required',
            ]);

            $shipping_way = ShippingWay::create($request->only(['code', 'shipping_method', 'freight_rate', 'type']));

            $output = ['success' => true,
                        'msg' => __('shipping_ways.shipping_way_added_success'),
                        'data' => $shipping_way];
        } catch (\Exception $e) {
            \Log::emergency("File: {$e->getFile()} Line: {$e->getLine()} Message: {$e->getMessage()}");

            $output = ['success' => false,
                        'msg' => __('messages.something_went_wrong')];
        }

        return $output;
    }

    public function edit($id)
    {
        if (!auth()->user()->can('shipping_ways.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $shipping_way = ShippingWay::findOrFail($id);
            return view('shipping_ways.edit')->with(compact('shipping_way'));
        }
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('shipping_ways.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $shipping_way = ShippingWay::findOrFail($id);

            $request->validate([
                'code' => 'required|unique:shipping_ways,code,' . $shipping_way->id,
                'shipping_method' => 'required',
                'freight_rate' => 'required|numeric',
                'type' => 'required',
            ]);

            $shipping_way->update($request->only(['code', 'shipping_method', 'freight_rate', 'type']));

            $output = ['success' => true,
                        'msg' => __('shipping_ways.shipping_way_updated_success')];
        } catch (\Exception $e) {
            \Log::emergency("File: {$e->getFile()} Line: {$e->getLine()} Message: {$e->getMessage()}");

            $output = ['success' => false,
                        'msg' => __('messages.something_went_wrong')];
        }

        return $output;
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('shipping_ways.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $shipping_way = ShippingWay::findOrFail($id);
            $shipping_way->delete();

            $output = ['success' => true,
                        'msg' => __('shipping_ways.shipping_way_deleted_success')];
        } catch (\Exception $e) {
            \Log::emergency("File: {$e->getFile()} Line: {$e->getLine()} Message: {$e->getMessage()}");

            $output = ['success' => false,
                        'msg' => __('messages.something_went_wrong')];
        }

        return $output;
    }
}
