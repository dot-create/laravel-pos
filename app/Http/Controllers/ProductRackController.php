<?php

namespace App\Http\Controllers;

use App\ProductRack;
use App\BusinessLocation;
use App\Product;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProductRackController extends Controller
{
    public function index()
    {
        return view('product_racks.index');
    }

    public function get()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $racks = ProductRack::where('product_racks.business_id', $business_id)
                ->with(['location:id,name', 'product:id,name'])
                ->select(['id', 'rack', 'row', 'position', 'product_id', 'location_id']);

            return DataTables::of($racks)
                ->addColumn('location', fn($row) => $row->location->name ?? '')
                ->addColumn('product', fn($row) => $row->product->name ?? '')
                ->addColumn('storage_location', function($row) {
                    return $row->rack . '.' . $row->row . '.' . $row->position;
                })
                ->addColumn('action', function ($row) {
                    $editBtn = '<button class="btn btn-xs btn-primary btn-modal" data-href="' . action('ProductRackController@edit', $row->id) . '" data-container=".product_rack_modal"><i class="fa fa-edit"></i> ' . __('messages.edit') . '</button>';
                    $deleteBtn = '<button type="button" class="btn btn-xs btn-danger delete-product-rack" data-id="' . $row->id . '"><i class="fa fa-trash"></i> ' . __('messages.delete') . '</button>';
                    return $editBtn . ' ' . $deleteBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        $locations = BusinessLocation::forDropdown($business_id);
        $products = Product::forDropdown($business_id);

        $storageLocations = ProductRack::selectRaw("
                id,
                location_id,
                CONCAT_WS(' - ', 
                    IFNULL(rack, 0), 
                    IFNULL(`row`, 0), 
                    IFNULL(position, 0)
                ) as full_location
            ")
            ->get()
            ->groupBy('location_id')
            ->map(function ($items) {
                return $items->pluck('full_location', 'id');
            });

        return view('product_racks.create', compact('locations', 'products', 'storageLocations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:business_locations,id',
            'product_id' => 'required|exists:products,id',
            'rack' => 'required|string|max:255',
            'row' => 'required|string|max:255',
            'position' => 'required|string|max:255'
        ]);

        ProductRack::create([
            'business_id' => $request->session()->get('user.business_id'),
            'location_id' => $request->location_id,
            'product_id' => $request->product_id,
            'rack' => $request->rack,
            'row' => $request->row,
            'position' => $request->position
        ]);

        return ['success' => true, 'msg' => __('Product location added successfully.')];
    }

    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $rack = ProductRack::where('business_id', $business_id)->findOrFail($id);

        $locations = BusinessLocation::forDropdown($business_id);
        $products = Product::forDropdown($business_id);

        return view('product_racks.edit', compact('rack', 'locations', 'products'));
    }

    public function update(Request $request, $id)
    {
        $rack = ProductRack::findOrFail($id);

        $request->validate([
            'location_id' => 'required|exists:business_locations,id',
            'product_id' => 'required|exists:products,id',
            'rack' => 'required|string|max:255',
            'row' => 'required|string|max:255',
            'position' => 'required|string|max:255'
        ]);

        $rack->update([
            'location_id' => $request->location_id,
            'product_id' => $request->product_id,
            'rack' => $request->rack,
            'row' => $request->row,
            'position' => $request->position
        ]);

        return ['success' => true, 'msg' => __('Product location updated successfully.')];
    }

    public function destroy($id)
    {
        ProductRack::destroy($id);
        return response()->json(['success' => true, 'msg' => __('Product Rack deleted successfully.')]);
    }

    public function bulkUploadForm()
    {
        return view('product_racks.bulk_upload');
    }

    public function bulkUpload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt'
        ]);

        $business_id = $request->session()->get('user.business_id');
        $handle = fopen($request->file('csv_file')->getRealPath(), 'r');
        $header = fgetcsv($handle);

        while (($data = fgetcsv($handle)) !== false) {
            $row = array_combine($header, $data);
            ProductRack::updateOrCreate(
                [
                    'business_id' => $business_id,
                    'location_id' => $row['location_id'],
                    'product_id' => $row['product_id'],
                ],
                [
                    'rack' => $row['rack'] ?? null,
                    'row' => $row['row'] ?? null,
                    'position' => $row['position'] ?? null
                ]
            );
        }

        return redirect()->route('product-racks.index')->with('status', 'Bulk upload completed.');
    }

    public function getStorageLocations($location_id)
    {
        $business_id = request()->session()->get('user.business_id');
        $locations = ProductRack::where('location_id', $location_id)
            ->where('business_id', $business_id)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->id => "{$item->rack}.{$item->row}.{$item->position}"];
            });

        return response()->json($locations);
    }
}
