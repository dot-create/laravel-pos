<?php

namespace App\Http\Controllers;

use App\InvoiceScheme;
use App\InvoiceLayout;
use Illuminate\Http\Request;
use Datatables;

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
                    'expiration_date', 'invoicing_key']);

            return Datatables::of($schemes)
                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '<button type="button" data-href="'.action('InvoiceSchemeController@edit', [$row->id]).'" class="btn btn-xs btn-primary btn-modal" data-container=".invoice_edit_modal"><i class="glyphicon glyphicon-edit"></i> '. __("messages.edit").'</button>';
                        $html .= '&nbsp;<button type="button" data-href="'.action('InvoiceSchemeController@destroy', [$row->id]).'" class="btn btn-xs btn-danger delete_invoice_button" @if($is_default) disabled @endif><i class="glyphicon glyphicon-trash"></i> '. __("messages.delete").'</button>&nbsp;';
                        
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
                    return $row->status == 'active' 
                        ? '<span class="label label-success">'.__("invoice.active").'</span>' 
                        : '<span class="label label-danger">'.__("invoice.inactive").'</span>';
                })
                ->editColumn('start_date', '@if($start_date) {{ \Carbon\Carbon::parse($start_date)->format("Y-m-d") }} @else {{ __("invoice.na") }} @endif')
                ->editColumn('expiration_date', '@if($expiration_date) {{ \Carbon\Carbon::parse($expiration_date)->format("Y-m-d") }} @else {{ __("invoice.na") }} @endif')
                ->editColumn('end_number', '@if($end_number) {{ $end_number }} @else {{ __("invoice.na") }} @endif')
                ->rawColumns(['action', 'name', 'status', 'start_date', 'expiry_date'])
                ->make(true);
        }

        $invoice_layouts = InvoiceLayout::where('business_id', $business_id)
                                        ->with(['locations'])
                                        ->get();

        return view('invoice_scheme.index')
                    ->with(compact('invoice_layouts'));
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
            'name' => 'required|string',
            'scheme_type' => 'required|in:blank,year',
            'start_number' => 'nullable|integer|min:0',
            'invoice_count' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'expiration_date' => 'nullable|date|after_or_equal:start_date',
            'invoicing_key' => 'nullable|string|max:255',
        ]);

        try {
            // $input = $request->only(['name', 'scheme_type', 'prefix', 'start_number', 'total_digits']);
            $input = $request->only([
                'name', 'scheme_type', 'prefix', 'start_number', 'total_digits',
                'is_default', 'status', 'invoice_count', 'start_date', 'expiration_date', 'invoicing_key'
            ]);

            if (!empty($input['start_number']) && !empty($input['invoice_count'])) {
                $input['end_number'] = $input['start_number'] + $input['invoice_count'] - 1;
            }

            $business_id = $request->session()->get('user.business_id');
            $input['business_id'] = $business_id;

            if (!empty($request->input('is_default'))) {
                //get_default
                $default = InvoiceScheme::where('business_id', $business_id)
                                ->where('is_default', 1)
                                ->update(['is_default' => 0 ]);
                $input['is_default'] = 1;
            }
            InvoiceScheme::create($input);
            $output = ['success' => true,
                            'msg' => __("invoice.added_success")
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->can('invoice_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        //
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
            'name' => 'required|string',
            'scheme_type' => 'required|in:blank,year',
            'start_number' => 'nullable|integer|min:0',
            'invoice_count' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'expiration_date' => 'nullable|date|after_or_equal:start_date',
            'invoicing_key' => 'nullable|string|max:255',
        ]);


        try {
            // $input = $request->only(['name', 'scheme_type', 'prefix', 'start_number', 'total_digits']);

            $input = $request->only([
                'name', 'scheme_type', 'prefix', 'start_number', 'total_digits',
                'is_default', 'status', 'invoice_count', 'start_date', 'expiration_date', 'invoicing_key'
            ]);

            if (!empty($input['start_number']) && !empty($input['invoice_count'])) {
                $input['end_number'] = $input['start_number'] + $input['invoice_count'] - 1;
            }

            $invoice = InvoiceScheme::where('id', $id)->update($input);

            $output = ['success' => true,
                            'msg' => __('invoice.updated_success')
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
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
                $invoice = InvoiceScheme::find($id);
                if ($invoice->is_default != 1) {
                    $invoice->delete();
                    $output = ['success' => true,
                                'msg' => __("invoice.deleted_success")
                                ];
                } else {
                    $output = ['success' => false,
                                'msg' => __("messages.something_went_wrong")
                                ];
                }
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false,
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
