<?php

namespace App\Http\Controllers;

use App\InvoiceScheme;
use App\InvoiceLayout;
use Illuminate\Http\Request;
use Datatables;
use Carbon\Carbon;

class InvoiceSchemeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('invoice_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {
            $schemes = InvoiceScheme::where('business_id', $business_id)
                ->select(['id', 'name', 'scheme_type', 'prefix', 'start_number', 
                    'invoice_count', 'total_digits', 'is_default', 'status', 
                    'end_number', 'start_date', 
                    'expiration_date', 'invoicing_key', 'current_number']);

            return Datatables::of($schemes)
                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '<button type="button" data-href="'.action('InvoiceSchemeController@edit', [$row->id]).'" class="btn btn-xs btn-primary btn-modal" data-container=".invoice_edit_modal"><i class="glyphicon glyphicon-edit"></i> '. __("messages.edit").'</button>';
                        
                        if (!$row->is_default) {
                            $html .= '&nbsp;<button type="button" data-href="'.action('InvoiceSchemeController@destroy', [$row->id]).'" class="btn btn-xs btn-danger delete_invoice_button"><i class="glyphicon glyphicon-trash"></i> '. __("messages.delete").'</button>&nbsp;';
                        }
                        
                        if ($row->is_default) {
                            $html .= '<button type="button" class="btn btn-xs btn-success" disabled><i class="fa fa-check-square-o" aria-hidden="true"></i> '. __("barcode.default").'</button>';
                        } else {
                            $html .= '<button class="btn btn-xs btn-info set_default_invoice" data-href="'.action('InvoiceSchemeController@setDefault', [$row->id]).'">'. __("barcode.set_as_default").'</button>';
                        }
                        
                        return $html;
                    }
                )
                ->editColumn('prefix', function ($row) {
                    if ($row->scheme_type == 'year') {
                        return $row->prefix . date('Y') . config('constants.invoice_scheme_separator');
                    }
                    return $row->prefix;
                })
                ->editColumn('name', function ($row) {
                    $name = $row->name;
                    if ($row->is_default == 1) {
                        $name .= ' &nbsp; <span class="label label-success">' . __("barcode.default") . '</span>';
                    }
                    return $name;
                })
                ->addColumn('status', function ($row) {
                    $statusLabel = $row->status == 'active' 
                        ? '<span class="label label-success">'.__("invoice.active").'</span>' 
                        : '<span class="label label-danger">'.__("invoice.inactive").'</span>';
                    
                    // Add warning indicators
                    $warnings = $this->getSchemeWarnings($row);
                    if (!empty($warnings)) {
                        $statusLabel .= '<br><small class="text-warning"><i class="fa fa-warning"></i> ' . implode(', ', $warnings) . '</small>';
                    }
                    
                    return $statusLabel;
                })
                ->addColumn('remaining_numbers', function ($row) {
                    $current = $row->current_number ?? $row->start_number;
                    $remaining = $row->end_number - $current + 1;
                    
                    $class = 'label-info';
                    if ($remaining <= 10) {
                        $class = 'label-warning';
                    }
                    if ($remaining <= 5) {
                        $class = 'label-danger';
                    }
                    
                    return '<span class="label ' . $class . '">' . $remaining . ' remaining</span>';
                })
                ->addColumn('days_remaining', function ($row) {
                    if (!$row->expiration_date) {
                        return '<span class="label label-default">No expiry</span>';
                    }
                    
                    $expiryDate = Carbon::parse($row->expiration_date);
                    $today = Carbon::today();
                    $daysRemaining = $today->diffInDays($expiryDate, false);
                    
                    $class = 'label-info';
                    $text = $daysRemaining . ' days';
                    
                    if ($daysRemaining < 0) {
                        $class = 'label-danger';
                        $text = 'Expired';
                    } elseif ($daysRemaining <= 10) {
                        $class = 'label-warning';
                    } elseif ($daysRemaining <= 5) {
                        $class = 'label-danger';
                    }
                    
                    return '<span class="label ' . $class . '">' . $text . '</span>';
                })
                ->editColumn('start_date', '@if($start_date) {{ \Carbon\Carbon::parse($start_date)->format("Y-m-d") }} @else {{ __("invoice.na") }} @endif')
                ->editColumn('expiration_date', '@if($expiration_date) {{ \Carbon\Carbon::parse($expiration_date)->format("Y-m-d") }} @else {{ __("invoice.na") }} @endif')
                ->editColumn('end_number', '@if($end_number) {{ $end_number }} @else {{ __("invoice.na") }} @endif')
                ->rawColumns(['action', 'name', 'status', 'remaining_numbers', 'days_remaining'])
                ->make(true);
        }

        $invoice_layouts = InvoiceLayout::where('business_id', $business_id)
                                        ->with(['locations'])
                                        ->get();

        return view('invoice_scheme.index')
                    ->with(compact('invoice_layouts'));
    }

    /**
     * Get warning messages for a scheme
     */
    private function getSchemeWarnings($scheme)
    {
        $warnings = [];
        
        // Check remaining numbers
        $current = $scheme->current_number ?? $scheme->start_number;
        $remaining = $scheme->end_number - $current + 1;
        
        if ($remaining <= 10) {
            $warnings[] = $remaining . ' numbers left';
        }
        
        // Check expiry date
        if ($scheme->expiration_date) {
            $expiryDate = Carbon::parse($scheme->expiration_date);
            $today = Carbon::today();
            $daysRemaining = $today->diffInDays($expiryDate, false);
            
            if ($daysRemaining <= 10 && $daysRemaining >= 0) {
                $warnings[] = $daysRemaining . ' days to expire';
            } elseif ($daysRemaining < 0) {
                $warnings[] = 'Expired';
            }
        }
        
        return $warnings;
    }

    /**
     * Get scheme warnings for invoice creation
     */
    public function getSchemeWarningsForInvoice($schemeId)
    {
        $scheme = InvoiceScheme::find($schemeId);
        if (!$scheme) {
            return response()->json(['warnings' => []]);
        }
        
        $warnings = [];
        
        // Check remaining numbers
        $current = $scheme->current_number ?? $scheme->start_number;
        $remaining = $scheme->end_number - $current + 1;
        
        if ($remaining <= 10) {
            $warnings[] = [
                'type' => 'numbers',
                'message' => "Warning: Only {$remaining} invoice numbers remaining for current scheme",
                'severity' => $remaining <= 5 ? 'danger' : 'warning'
            ];
        }
        
        // Check expiry date
        if ($scheme->expiration_date) {
            $expiryDate = Carbon::parse($scheme->expiration_date);
            $today = Carbon::today();
            $daysRemaining = $today->diffInDays($expiryDate, false);
            
            if ($daysRemaining <= 10 && $daysRemaining >= 0) {
                $warnings[] = [
                    'type' => 'expiry',
                    'message' => "Warning: Scheme expires in {$daysRemaining} days",
                    'severity' => $daysRemaining <= 5 ? 'danger' : 'warning'
                ];
            } elseif ($daysRemaining < 0) {
                $warnings[] = [
                    'type' => 'expired',
                    'message' => "Error: This scheme has expired",
                    'severity' => 'danger'
                ];
            }
        }
        
        return response()->json(['warnings' => $warnings]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('invoice_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        return view('invoice_scheme.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('invoice_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'scheme_type' => 'required|in:blank,year',
            'start_number' => 'required|integer|min:0',
            'invoice_count' => 'required|integer|min:1',
            'total_digits' => 'required|integer|min:4|max:10',
            'start_date' => 'nullable|date',
            'expiration_date' => 'nullable|date|after_or_equal:start_date',
            'invoicing_key' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'prefix' => 'nullable|string|max:10',
        ]);

        try {
            $input = $request->only([
                'name', 'scheme_type', 'prefix', 'start_number', 'total_digits',
                'is_default', 'status', 'invoice_count', 'start_date', 
                'expiration_date', 'invoicing_key'
            ]);

            // Calculate end number
            if (!empty($input['start_number']) && !empty($input['invoice_count'])) {
                $input['end_number'] = $input['start_number'] + $input['invoice_count'] - 1;
            }

            // Set current number to start number initially
            $input['current_number'] = $input['start_number'];

            $business_id = $request->session()->get('user.business_id');
            $input['business_id'] = $business_id;

            // Handle default setting
            if (!empty($request->input('is_default'))) {
                InvoiceScheme::where('business_id', $business_id)
                           ->where('is_default', 1)
                           ->update(['is_default' => 0]);
                $input['is_default'] = 1;
            } else {
                $input['is_default'] = 0;
            }

            
            InvoiceScheme::create($input);
            
            $output = [
                'success' => true,
                'msg' => __("invoice.added_success")
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = [
                'success' => false,
                'msg' => __("messages.something_went_wrong")
            ];
        }

        return $output;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('invoice_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $invoice = InvoiceScheme::where('business_id', $business_id)->find($id);

        return view('invoice_scheme.edit')
            ->with(compact('invoice'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('invoice_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'scheme_type' => 'required|in:blank,year',
            'start_number' => 'required|integer|min:0',
            'invoice_count' => 'required|integer|min:1',
            'total_digits' => 'required|integer|min:4|max:10',
            'start_date' => 'nullable|date',
            'expiration_date' => 'nullable|date|after_or_equal:start_date',
            'invoicing_key' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'prefix' => 'nullable|string|max:10',
        ]);

        try {
            $input = $request->only([
                'name', 'scheme_type', 'prefix', 'start_number', 'total_digits',
                'is_default', 'status', 'invoice_count', 'start_date', 
                'expiration_date', 'invoicing_key'
            ]);

            // Calculate end number
            if (!empty($input['start_number']) && !empty($input['invoice_count'])) {
                $input['end_number'] = $input['start_number'] + $input['invoice_count'] - 1;
            }

            $business_id = request()->session()->get('user.business_id');

            // Handle default setting
            if (!empty($request->input('is_default'))) {
                InvoiceScheme::where('business_id', $business_id)
                           ->where('is_default', 1)
                           ->where('id', '!=', $id)
                           ->update(['is_default' => 0]);
                $input['is_default'] = 1;
            } else {
                $input['is_default'] = 0;
            }

            InvoiceScheme::where('id', $id)
                        ->where('business_id', $business_id)
                        ->update($input);

            $output = [
                'success' => true,
                'msg' => __('invoice.updated_success')
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = [
                'success' => false,
                'msg' => __("messages.something_went_wrong")
            ];
        }

        return $output;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('invoice_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');
                $invoice = InvoiceScheme::where('business_id', $business_id)->find($id);
                
                if (!$invoice) {
                    return [
                        'success' => false,
                        'msg' => __("messages.something_went_wrong")
                    ];
                }
                
                if ($invoice->is_default != 1) {
                    $invoice->delete();
                    $output = [
                        'success' => true,
                        'msg' => __("invoice.deleted_success")
                    ];
                } else {
                    $output = [
                        'success' => false,
                        'msg' => __("Cannot delete default scheme")
                    ];
                }
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = [
                    'success' => false,
                    'msg' => __("messages.something_went_wrong")
                ];
            }

            return $output;
        }
    }
    /**
     * Sets invoice scheme setting as default
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function setDefault($id)
    {
        if (!auth()->user()->can('invoice_settings.access')) {
            abort(403, 'Unauthorized action.');
        }
        
        if (request()->ajax()) {
            try {
                //get_default
                $business_id = request()->session()->get('user.business_id');
                $default = InvoiceScheme::where('business_id', $business_id)
                                ->where('is_default', 1)
                                 ->update(['is_default' => 0 ]);
                                 
                $invoice = InvoiceScheme::find($id);
                $invoice->is_default = 1;
                $invoice->save();

                $output = ['success' => true,
                            'msg' => __("barcode.default_set_success")
                        ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
            }
            return $output;
        }
    }
}
