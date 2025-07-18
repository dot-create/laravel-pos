<?php

namespace App\Http\Controllers;

use App\ProductRack;
use App\BusinessLocation;
use App\Product;
use App\StorageLocation;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProductRackController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('permission:product_rack.view')->only('get');
    //     $this->middleware('permission:product_rack.create')->only(['create', 'store']);
    //     $this->middleware('permission:product_rack.edit')->only(['edit', 'update']);
    //     $this->middleware('permission:product_rack.delete')->only('destroy');
    // }

    public function index()
    {
        return view('product_racks.index');
    }

    public function get()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $racks = ProductRack::where('product_racks.business_id', $business_id)
                ->join('storage_locations', 'product_racks.storage_location_id', '=', 'storage_locations.id')
                ->with(['location:id,name', 'product:id,name'])
                ->select([
                    'product_racks.id',
                    'storage_locations.rack',
                    'storage_locations.row',
                    'storage_locations.position',
                    'product_racks.product_id',
                    'product_racks.location_id'
                ]);

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
        
        // Get all rack locations for dropdown
        $storageLocations = StorageLocation::where('business_id', $business_id)
            ->selectRaw("id, CONCAT(rack, '.', `row`, '.', position) as full_location")
            ->pluck('full_location', 'id');
            
        return view('product_racks.create', compact('locations', 'products', 'storageLocations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:business_locations,id',
            'product_id' => 'required|exists:products,id',
            'storage_location_id' => 'required|exists:storage_locations,id'
        ]);

        ProductRack::create([
            'business_id' => $request->session()->get('user.business_id'),
            'location_id' => $request->location_id,
            'product_id' => $request->product_id,
            'storage_location_id' => $request->rack_location_id
        ]);

        return ['success' => true, 'msg' => __('Product location added successfully.')];
    }

    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $rack = ProductRack::where('business_id', $business_id)
            ->with('storageLocation')
            ->findOrFail($id);
            
        $locations = BusinessLocation::forDropdown($business_id);
        $products = Product::forDropdown($business_id);
        
        $rackLocations = StorageLocation::where('business_id', $business_id)
            ->selectRaw("id, CONCAT(rack, '.', `row`, '.', position) as full_location")
            ->pluck('full_location', 'id');
            
        return view('product_racks.edit', compact('rack', 'locations', 'products', 'storageLocations'));
    }

    public function update(Request $request, $id)
    {
        $rack = ProductRack::findOrFail($id);

        $request->validate([
            'location_id' => 'required|exists:business_locations,id',
            'product_id' => 'required|exists:products,id',
            'storage_location_id' => 'required|exists:storage_locations,id'
        ]);

        $rack->update([
            'location_id' => $request->location_id,
            'product_id' => $request->product_id,
            'storage_location_id' => $request->rack_location_id
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
                    'rack' => $row['rack'] ?? null
                ]
            );
        }

        return redirect()->route('product-racks.index')->with('status', 'Bulk upload completed.');
    }

    // New method to get storage locations
    public function getStorageLocations($location_id)
    {
        $business_id = request()->session()->get('user.business_id');
        $locations = StorageLocation::where('location_id', $location_id)
            ->where('business_id', $business_id)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->id => "{$item->rack}.{$item->row}.{$item->position}"];
            });

        return response()->json($locations);
    }
}
