<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\WeightUnit;

class WeightUnitController extends Controller
{
    public function index()
    {
        return view('weight_units.index');
    }

    public function getUnits(Request $request)
    {
        $units = WeightUnit::select(['id', 'code', 'unit_name', 'equivalent_to_lb']);

        return datatables()->of($units)
            ->addColumn('action', function ($row) {
                $editBtn = '<button class="btn btn-xs btn-primary btn-modal" data-href="' . action('WeightUnitController@edit', $row->id) . '" data-container=".weight_unit_modal">
                                <i class="fa fa-edit"></i> ' . __('messages.edit') . '
                            </button>';

                $deleteBtn = '<button type="button" class="btn btn-xs btn-danger delete-weight-unit" data-id="' . $row->id . '">
                                <i class="fa fa-trash"></i> ' . __('messages.delete') . '
                            </button>';

                return $editBtn . ' ' . $deleteBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        return view('weight_units.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:weight_units,code',
            'unit_name' => 'required|string|max:255',
            'equivalent_to_lb' => 'required|numeric|min:0',
        ]);

        try {
            $unit = WeightUnit::create($validated);

            return response()->json([
                'success' => true,
                'msg' => __('weight_units.added_success'),
                'data' => $unit,
            ]);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }
    }

    public function edit($id)
    {
        $unit = WeightUnit::findOrFail($id);
        return view('weight_units.edit', compact('unit'));
    }

    public function update(Request $request, $id)
    {
        $unit = WeightUnit::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:weight_units,code,' . $unit->id,
            'unit_name' => 'required|string|max:255',
            'equivalent_to_lb' => 'required|numeric|min:0',
        ]);

        try {
            $unit->update($validated);

            return response()->json([
                'success' => true,
                'msg' => __('weight_units.updated_success'),
                'data' => $unit,
            ]);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $unit = WeightUnit::findOrFail($id);
            $unit->delete();

            return response()->json([
                'success' => true,
                'msg' => __('weight_units.deleted_success'),
            ]);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }
    }
}
