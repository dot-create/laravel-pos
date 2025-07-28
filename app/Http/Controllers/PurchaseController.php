<?php

namespace App\Http\Controllers;

use Excel;
use App\User;
use App\Contact;
use App\Product;
use App\TaxRate;
use App\Business;
use App\Currency;
use App\Variation;
use App\Transaction;
use App\PurchaseLine;
use App\CustomerGroup;
use App\Utils\ContactUtil;
use App\BusinessLocation;
use App\CustomerRequest;
use App\RequestItem;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;

use App\AccountTransaction;
use App\Utils\BusinessUtil;
use Illuminate\Http\Request;
use App\Utils\TransactionUtil;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

class PurchaseController extends Controller
{
    /** 
     * All Utils instance.
     *
     */
    protected $productUtil;
    protected $transactionUtil;
    protected $moduleUtil;
    protected $contactUtil;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ContactUtil $contactUtil,ProductUtil $productUtil, TransactionUtil $transactionUtil, BusinessUtil $businessUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->contactUtil = $contactUtil;
        $this->transactionUtil = $transactionUtil;
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;

        $this->dummyPaymentLine = ['method' => 'cash', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
        'is_return' => 0, 'transaction_no' => ''];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {
            $purchases = $this->transactionUtil->getListPurchases($business_id);
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $purchases->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->supplier_id)) {
                $purchases->where('contacts.id', request()->supplier_id);
            }
            if (!empty(request()->location_id)) {
                $purchases->where('transactions.location_id', request()->location_id);
            }
            if (!empty(request()->input('payment_status')) && request()->input('payment_status') != 'overdue') {
                $purchases->where('transactions.payment_status', request()->input('payment_status'));
            } elseif (request()->input('payment_status') == 'overdue') {
                $purchases->whereIn('transactions.payment_status', ['due', 'partial'])
                    ->whereNotNull('transactions.pay_term_number')
                    ->whereNotNull('transactions.pay_term_type')
                    ->whereRaw("IF(transactions.pay_term_type='days', DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number DAY) < CURDATE(), DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number MONTH) < CURDATE())");
            }

            if (!empty(request()->status)) {
                $purchases->where('transactions.status', request()->status);
            }
            
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $purchases->whereDate('transactions.transaction_date', '>=', $start)
                            ->whereDate('transactions.transaction_date', '<=', $end);
            }

            if (!auth()->user()->can('purchase.view') && auth()->user()->can('view_own_purchase')) {
                $purchases->where('transactions.created_by', request()->session()->get('user.id'));
            }
            
            return Datatables::of($purchases)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                data-toggle="dropdown" aria-expanded="false">' .
                                __("messages.actions") .
                                '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                    if (auth()->user()->can("purchase.view")) {
                        $html .= '<li><a href="#" data-href="' . action('PurchaseController@show', [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>' . __("messages.view") . '</a></li>';
                    }
                    if (auth()->user()->can("purchase.view")) {
                        $html .= '<li><a href="#" class="print-invoice" data-href="' . action('PurchaseController@printInvoice', [$row->id]) . '"><i class="fas fa-print" aria-hidden="true"></i>'. __("messages.print") .'</a></li>';
                    }
                    if (auth()->user()->can("purchase.update")) {
                        $html .= '<li><a href="' . action('PurchaseController@edit', [$row->id]) . '"><i class="fas fa-edit"></i>' . __("messages.edit") . '</a></li>';
                    }
                    if (auth()->user()->can("purchase.delete")) {
                        $html .= '<li><a href="' . action('PurchaseController@destroy', [$row->id]) . '" class="delete-purchase"><i class="fas fa-trash"></i>' . __("messages.delete") . '</a></li>';
                    }

                    $html .= '<li><a href="' . action('LabelsController@show') . '?purchase_id=' . $row->id . '" data-toggle="tooltip" title="' . __('lang_v1.label_help') . '"><i class="fas fa-barcode"></i>' . __('barcode.labels') . '</a></li>';

                    if (auth()->user()->can("purchase.view") && !empty($row->document)) {
                        $document_name = !empty(explode("_", $row->document, 2)[1]) ? explode("_", $row->document, 2)[1] : $row->document ;
                        $html .= '<li><a href="' . url('uploads/documents/' . $row->document) .'" download="' . $document_name . '"><i class="fas fa-download" aria-hidden="true"></i>' . __("purchase.download_document") . '</a></li>';
                        if (isFileImage($document_name)) {
                            $html .= '<li><a href="#" data-href="' . url('uploads/documents/' . $row->document) .'" class="view_uploaded_document"><i class="fas fa-image" aria-hidden="true"></i>' . __("lang_v1.view_document") . '</a></li>';
                        }
                    }
                                        
                    if (auth()->user()->can("purchase.payments") || 
                        auth()->user()->can("edit_purchase_payment") || 
                        auth()->user()->can("delete_purchase_payment")) {

                        $html .= '<li class="divider"></li>';
                        if ($row->payment_status != 'paid') {
                            $html .= '<li><a href="' . action('TransactionPaymentController@addPayment', [$row->id]) . '" class="add_payment_modal"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("purchase.add_payment") . '</a></li>';
                        }
                        
                        $html .= '<li><a href="' . action('TransactionPaymentController@show', [$row->id]) .
                        '" class="view_payment_modal"><i class="fas fa-money-bill-alt" aria-hidden="true" ></i>' . __("purchase.view_payments") . '</a></li>';
                    }

                    if (auth()->user()->can("purchase.update")) {
                        $html .= '<li><a href="' . action('PurchaseReturnController@add', [$row->id]) .
                        '"><i class="fas fa-undo" aria-hidden="true" ></i>' . __("lang_v1.purchase_return") . '</a></li>';
                    }

                    if (auth()->user()->can("purchase.update") || auth()->user()->can("purchase.update_status")) {
                        $html .= '<li><a href="#" data-purchase_id="' . $row->id .
                        '" data-status="' . $row->status . '" class="update_status"><i class="fas fa-edit" aria-hidden="true" ></i>' . __("lang_v1.update_status") . '</a></li>';
                    }

                    if ($row->status == 'ordered') {
                        $html .= '<li><a href="#" data-href="' . action('NotificationController@getTemplate', ["transaction_id" => $row->id,"template_for" => "new_order"]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-envelope" aria-hidden="true"></i> ' . __("lang_v1.new_order_notification") . '</a></li>';
                    } elseif ($row->status == 'received') {
                        $html .= '<li><a href="#" data-href="' . action('NotificationController@getTemplate', ["transaction_id" => $row->id,"template_for" => "items_received"]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-envelope" aria-hidden="true"></i> ' . __("lang_v1.item_received_notification") . '</a></li>';
                    } elseif ($row->status == 'pending') {
                        $html .= '<li><a href="#" data-href="' . action('NotificationController@getTemplate', ["transaction_id" => $row->id,"template_for" => "items_pending"]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-envelope" aria-hidden="true"></i> ' . __("lang_v1.item_pending_notification") . '</a></li>';
                    }

                    $html .=  '</ul></div>';
                    return $html;
                })
                ->removeColumn('id')
                ->editColumn('ref_no', function ($row) {
                    return !empty($row->return_exists) ? $row->ref_no . ' <small class="label bg-red label-round no-print" title="' . __('lang_v1.some_qty_returned') .'"><i class="fas fa-undo"></i></small>' : $row->ref_no;
                })
                ->editColumn('final_total', function($row) use($business_id) {
                    $transaction = Transaction::where('id', $row->id)->first();
                    $business_location = BusinessLocation::where('id', $transaction->location_id)->first();
                    $currency_details = Currency::where('id', $business_location->currency_id)->first();
                    $final_total = $row->final_total;
                    return "<span class='final_total' data-orig-value='".$final_total."'>".$currency_details->symbol . $this->transactionUtil->num_f($final_total )."</span>";
                })
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->editColumn('name', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br> @endif {{$name}}')
                ->editColumn(
                    'status',
                    '<a href="#" @if(auth()->user()->can("purchase.update") || auth()->user()->can("purchase.update_status")) class="update_status no-print" data-purchase_id="{{$id}}" data-status="{{$status}}" @endif><span class="label @transaction_status($status) status-label" data-status-name="{{__(\'lang_v1.\' . $status)}}" data-orig-value="{{$status}}">{{__(\'lang_v1.\' . $status)}}
                        </span></a>'
                )
                ->editColumn(
                    'payment_status',
                    function ($row) {
                        $payment_status = Transaction::getPaymentStatus($row);
                        return (string) view('sell.partials.payment_status', ['payment_status' => $payment_status, 'id' => $row->id, 'for_purchase' => true]);
                    }
                )
                ->addColumn('payment_due', function ($row) {
                    $transaction = Transaction::where('id', $row->id)->first();
                    $business_location = BusinessLocation::where('id', $transaction->location_id)->first();
                    $currency_details = Currency::where('id', $business_location->currency_id)->first();
                    $due = $row->final_total - $row->amount_paid;
                    // $due = $row->final_total - ($row->amount_paid *  $currency_details ->rate) ;
                    // dd($due);
                    $due_html = '<strong>' . __('lang_v1.purchase') .':</strong> <span class="payment_due"  data-orig-value="' . $due . '">' . $currency_details->symbol .$this->transactionUtil->num_f($due ) . '</span>';

                    if (!empty($row->return_exists)) {
                        $return_due = $row->amount_return - $row->return_paid;
                        $due_html .= '<br><strong>' . __('lang_v1.purchase_return') .':</strong> <a href="' . action("TransactionPaymentController@show", [$row->return_transaction_id]) . '" class="view_purchase_return_payment_modal"><span class="purchase_return" data-orig-value="' . $return_due . '">' . $this->transactionUtil->num_f($return_due, true) . '</span></a>';
                    }
                    return $due_html;
                })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can("purchase.view")) {
                            return  action('PurchaseController@show', [$row->id]) ;
                        } else {
                            return '';
                        }
                    }])
                ->rawColumns(['final_total', 'action', 'payment_due', 'payment_status', 'status', 'ref_no', 'name'])
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        $suppliers = Contact::suppliersDropdown($business_id, false);
        $orderStatuses = $this->productUtil->orderStatuses();

        return view('purchase.index')
            ->with(compact('business_locations', 'suppliers', 'orderStatuses'));
    }
    public function listRequests(Request $request)
    {
        // if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
        //     abort(403, 'Unauthorized action.');
        // }
        $business_id = request()->session()->get('user.business_id');
        $requests = CustomerRequest::query()
        ->where('business_id', $business_id)
        ->with(['items', 'items.product', 'contact:id,name,supplier_business_name']);

        if (request()->ajax()) {
            if (!empty(request()->customer_id)) {
                $requests->whereHas('contact', function ($q) {
                    $q->where('id', request()->customer_id);
                });
            }

            if (!empty(request()->status)) {
                $requests->where('status', request()->status);
            }

            if (!empty(request()->sku)) {
                $searchTerm = request()->sku;
                $requests->whereHas('items', function ($q) use ($searchTerm) {
                    $q->whereHas('product', function ($subQ) use ($searchTerm) {
                        $subQ->where('sku', $searchTerm);
                    });
                });
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $requests->whereDate('created_at', '>=', request()->start_date)
                    ->whereDate('created_at', '<=', request()->end_date);
            }

            return Datatables::eloquent($requests)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
               
                    if ($search = $request->input('search.value')) {
                        $query->where(function ($q) use ($search) {
                            $search = strtolower($search);
                            $q->orWhere(function ($q2) use ($search) {
                                $q2->whereRaw('LOWER(request_reference) LIKE ?', ["%$search%"]);
                            });
                        });
                    }
                })
                ->addColumn('date', fn($row) => $row->created_at ?? 'N/A')
                ->addColumn('name', function ($row) {
                    $name = $row->contact->name ?? '';
                    $supplier_business_name = $row->contact->supplier_business_name ?? '';
                    return $name ?: $supplier_business_name;
                })
                ->addColumn('ref_no', function ($row) {
                    return $row->request_reference;
                })
                ->addColumn('status', function ($row) {
                    $html = '';
                    $status = ucfirst($row->status);
                    switch ($row->status) {
                        case 'Pending':
                            $html .= "<span class='label bg-red'>{$status}</span>";
                            break;
                        case 'ReadyToDraft':
                            $html .= "<span class='label bg-yellow'>{$status}</span>";
                            break;
                        case 'draft':
                            $html .= "<span class='label bg-green'>{$status}</span>";
                            break;
                        case 'Rejected':
                            $html .= "<span class='label bg-red'>{$status}</span>";
                            break;
                        default:
                            $html .= "<span class='label bg-primary'>{$status}</span>";
                            break;
                    }

                    if ($row->items->where('status', 'Rejected')->count() > 0) {
                        $html .= ' <span class="label bg-red"><i class="fa fa-info-circle" title="Has Rejected Item"></i></span>';
                    }

                    return $html;
                })
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                                <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                    data-toggle="dropdown" aria-expanded="false">' .
                                    __("messages.actions") .
                                    '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                    // if (auth()->user()->can("customer_request.view")) {
                        $html .= '<li><a href="' . action('PurchaseController@viewRequests', [$row->id]) . '">
                                    <i class="fas fa-eye"></i> ' . __("messages.view") . '</a></li>';
                    // }

                    // if (auth()->user()->can("customer_request.update")) {
                        $html .= '<li><a href="' . action('RequestController@edit', [$row->id]) . '">
                                    <i class="fas fa-edit"></i> ' . __("messages.edit") . '</a></li>';
                    // }
                    // if (auth()->user()->can("customer_request.update")) {
                        $html .= '<li><a href="' . action('RequestController@rejectQuote', [$row->id]) . '">
                                    <i class="fas fa-times"></i> ' . __("messages.reject") . '</a></li>';
                    // }
                    

                    $html .= '</ul></div>';
                    return $html;
                })
                ->rawColumns(['status', 'action', 'name'])
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        $suppliers = Contact::customersDropdown($business_id, false);
        $orderStatuses = $this->productUtil->requestStatuses();
        $status=$request->query('status');
        return view('sell.request.index')
            ->with(compact('requests','business_locations', 'suppliers', 'orderStatuses','status'));
    }
    public function requestitems(Request $request)
{
    // if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
    //     abort(403, 'Unauthorized action.');
    // }

    $business_id = $request->session()->get('user.business_id');

    $query = RequestItem::query()
        ->whereHas('request', function ($q) use ($business_id) {
            $q->where('business_id', $business_id);
        })
        ->whereNotIn('status', [
            'ReadyToDraft',
            'Rejected',
            'RejectedQuote',
            'AcceptedQuote',
            'Quote',
            'Stock',
            'Supplier-Confirmed',
            'Draft',
        ])
        ->with([
            'request:id,customer_id,request_reference,business_location_id,created_at',
            'request.contact:id,name,supplier_business_name',
            'product:id,name,sku,type',
            'variation:id,product_id,sub_sku',
            'variation.variation_location_details'
        ]);

    // Apply filters
    if (!empty($request->customer_id)) {
        $query->whereHas('request', function ($q) use ($request) {
            $q->where('customer_id', $request->customer_id);
        });
    }

    if (!empty($request->sku)) {
        $query->whereHas('product', function ($q) use ($request) {
            $q->where('sku', $request->sku);
        });
    }

    if (!empty($request->start_date) && !empty($request->end_date)) {
        $query->whereHas('request', function ($q) use ($request) {
            $q->whereBetween('created_at', [$request->start_date, $request->end_date]);
        });
    }

    if ($request->ajax()) {
        return DataTables::eloquent($query)
            ->addIndexColumn()

            // 🔍 Custom global search for related fields
            ->filter(function ($query) use ($request) {
               
                if ($search = $request->input('search.value')) {
                    $query->where(function ($q) use ($search) {
                        $search = strtolower($search);
                        dd($search);
                        $q->orWhereHas('product', function ($q2) use ($search) {
                            $q2->whereRaw('LOWER(name) LIKE ?', ["%$search%"])
                                ->orWhereRaw('LOWER(type) LIKE ?', ["%$search%"]);
                        });

                        $q->orWhereHas('variation', function ($q2) use ($search) {
                            $q2->whereRaw('LOWER(sub_sku) LIKE ?', ["%$search%"]);
                        });

                        $q->orWhereHas('request', function ($q2) use ($search) {
                            $q2->whereRaw('LOWER(request_reference) LIKE ?', ["%$search%"]);
                        });

                        $q->orWhereRaw('LOWER(status) LIKE ?', ["%$search%"]);
                    });
                }
            })
            ->addColumn('date', fn($row) => $row->request->created_at ?? 'N/A')
            ->addColumn('ref_no', fn($row) => $row->request->request_reference)
            ->addColumn('product_name', fn($row) => $row->product->name ?? 'N/A')
            ->addColumn('product_type', fn($row) => $row->product->type ?? 'N/A')
            ->addColumn('product_sku', fn($row) => $row->variation->sub_sku ?? 'N/A')

            ->addColumn('availability_status', function ($row) use ($business_id) {
                $locationId = $row->request->business_location_id;
                $getValues=$this->productUtil->getAvaliableQty($business_id,$row->variation_id,$row->request->business_location_id);
                $availableQty=$getValues['avaliabilityQty'];
                

                if ($availableQty >= $row->quantity) {
                    RequestItem::where(['id' => $row->id, 'status' => 'Pending'])
                        ->update(['status' => 'Stock']);

                    return '<span class="label bg-green">' . $row->status . '</span>';
                } elseif ($availableQty > 0) {
                    return '<span class="label bg-yellow">Partially Available</span>';
                }

                return '<span class="label bg-red">Not Available</span>';
            })

            ->addColumn('action', function ($row) {
                $editUrl = route('request.item.edit', [$row->id]);
                $rejectUrl = route('request.item.reject', [$row->id]);

                $actionBtn = '<div class="btn-group">
                    <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                        data-toggle="dropdown" aria-expanded="false">
                        ' . __("messages.actions") . '
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                // if (auth()->user()->can("customer_request.update")) {
                    $actionBtn .= '<li><a href="' . $editUrl . '" class="edit-request">
                        <i class="fas fa-edit"></i> ' . __("messages.edit") . '</a></li>';
                // }

                // if (auth()->user()->can("customer_request.delete")) {
                    $actionBtn .= '<li><a href="' . $rejectUrl . '" class="delete-request">
                        <i class="fas fa-times"></i> ' . __("messages.reject") . '</a></li>';
                // }

                $actionBtn .= '</ul></div>';

                return $actionBtn;
            })
            ->rawColumns(['availability_status', 'action'])
            ->make(true);
    }
    $requestItems=$query->get();
    // For non-AJAX (page load)
    $business_locations = BusinessLocation::forDropdown($business_id);
    $suppliers = Contact::customersDropdown($business_id, false);
    $orderStatuses = $this->productUtil->orderStatuses();
    $status = $request->query('status');

    return view('sell.request.request_items')
        ->with(compact('requestItems', 'business_locations', 'suppliers', 'orderStatuses', 'status'));
}

    public function viewRequests($id)
    {
        // if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
        //     abort(403, 'Unauthorized action.');
        // }
        $business_id = request()->session()->get('user.business_id');

        $request = CustomerRequest::where('id', $id)
                ->with('contact:id,name','items','items.variation','items.variation.variation_location_details') // Load contact name
                ->first();
        $business_locations = BusinessLocation::forDropdown($business_id,false,true);
        $bl_attributes = $business_locations['attributes'];
        $orderStatuses = $this->productUtil->requestStatuses();
        $items=$request->items;
        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
        $productUtil=$this->productUtil;
        return view('sell.request.show')
            ->with(compact('request','business_locations','orderStatuses','bl_attributes','items','currency_details','productUtil','business_id'));
    }
    // public function listPendingRequests()
    // {
    //     if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
    //         abort(403, 'Unauthorized action.');
    //     }
    //     $business_id = request()->session()->get('user.business_id');
    //     $requests = CustomerRequest::where('business_id', $business_id)
    //             ->with('contact:id,name') // Load contact name
    //             ->get();
    //     if (request()->ajax()) {
    //         if (!empty(request()->customer_id)) {
    //             $purchases->where('contacts.id', request()->customer_id);
    //         }
    //         if (!empty(request()->status)) {
    //             $requests->where('status', request()->status);
    //         }
            
    //         if (!empty(request()->start_date) && !empty(request()->end_date)) {
    //             $start = request()->start_date;
    //             $end =  request()->end_date;
    //             $requests->whereDate('created_at', '>=', $start)
    //                         ->whereDate('created_at', '<=', $end);
    //         }
    //         return Datatables::of($requests)
    //             ->addIndexColumn() // Adds Sr#
    //             ->addColumn('name', function ($row) {
    //                 return optional($row->contact)->name ?? 'N/A';
    //             })
    //             ->addColumn('ref_no', function ($row) {
    //                 return $row->request_reference;
    //             })
    //             ->addColumn('status', function ($row) {
    //                 return ucfirst($row->status);
    //             })
    //             ->addColumn('action', function ($row) {
    //                 $html = '<div class="btn-group">
    //                             <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
    //                                 data-toggle="dropdown" aria-expanded="false">' .
    //                                 __("messages.actions") .
    //                                 '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
    //                             </button>
    //                             <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                    
    //                 if (auth()->user()->can("customer_request.view")) {
    //                     $html .= '<li><a href="#" data-href="' . action('PurchaseController@show', [$row->id]) . '" 
    //                                 class="btn-modal" data-container=".view_modal">
    //                                 <i class="fas fa-eye" aria-hidden="true"></i> ' . __("messages.view") . '</a></li>';
    //                 }
    //                 if (auth()->user()->can("customer_request.update")) {
    //                     $html .= '<li><a href="' . action('PurchaseController@edit', [$row->id]) . '">
    //                                 <i class="fas fa-edit"></i> ' . __("messages.edit") . '</a></li>';
    //                 }
    //                 if (auth()->user()->can("customer_request.delete")) {
    //                     $html .= '<li><a href="' . action('PurchaseController@destroy', [$row->id]) . '" 
    //                                 class="delete-request">
    //                                 <i class="fas fa-trash"></i> ' . __("messages.delete") . '</a></li>';
    //                 }
                    
    //                 $html .= '</ul></div>';
    //                 return $html;
    //             })
    //             ->rawColumns(['action'])
    //             ->make(true);
    //     }

    //     $business_locations = BusinessLocation::forDropdown($business_id);
    //     $suppliers = Contact::customersDropdown($business_id, false);
    //     $orderStatuses = $this->productUtil->orderStatuses();
    //     return view('sell.request.index')
    //         ->with(compact('requests','business_locations', 'suppliers', 'orderStatuses'));
    // }
    // public function readyTodraft()
    // {
    //     if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
    //         abort(403, 'Unauthorized action.');
    //     }
    //     $business_id = request()->session()->get('user.business_id');
    //     $requests = CustomerRequest::where('business_id', $business_id)
    //             ->with('contact:id,name') // Load contact name
    //             ->get();
    //     if (request()->ajax()) {
    //         if (!empty(request()->customer_id)) {
    //             $purchases->where('contacts.id', request()->customer_id);
    //         }
    //         if (!empty(request()->status)) {
    //             $requests->where('status', request()->status);
    //         }
            
    //         if (!empty(request()->start_date) && !empty(request()->end_date)) {
    //             $start = request()->start_date;
    //             $end =  request()->end_date;
    //             $requests->whereDate('created_at', '>=', $start)
    //                         ->whereDate('created_at', '<=', $end);
    //         }
    //         return Datatables::of($requests)
    //             ->addIndexColumn() // Adds Sr#
    //             ->addColumn('name', function ($row) {
    //                 return optional($row->contact)->name ?? 'N/A';
    //             })
    //             ->addColumn('ref_no', function ($row) {
    //                 return $row->request_reference;
    //             })
    //             ->addColumn('status', function ($row) {
    //                 return ucfirst($row->status);
    //             })
    //             ->addColumn('action', function ($row) {
    //                 $html = '<div class="btn-group">
    //                             <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
    //                                 data-toggle="dropdown" aria-expanded="false">' .
    //                                 __("messages.actions") .
    //                                 '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
    //                             </button>
    //                             <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                    
    //                 if (auth()->user()->can("customer_request.view")) {
    //                     $html .= '<li><a href="#" data-href="' . action('PurchaseController@show', [$row->id]) . '" 
    //                                 class="btn-modal" data-container=".view_modal">
    //                                 <i class="fas fa-eye" aria-hidden="true"></i> ' . __("messages.view") . '</a></li>';
    //                 }
    //                 if (auth()->user()->can("customer_request.update")) {
    //                     $html .= '<li><a href="' . action('PurchaseController@edit', [$row->id]) . '">
    //                                 <i class="fas fa-edit"></i> ' . __("messages.edit") . '</a></li>';
    //                 }
    //                 if (auth()->user()->can("customer_request.delete")) {
    //                     $html .= '<li><a href="' . action('PurchaseController@destroy', [$row->id]) . '" 
    //                                 class="delete-request">
    //                                 <i class="fas fa-trash"></i> ' . __("messages.delete") . '</a></li>';
    //                 }
                    
    //                 $html .= '</ul></div>';
    //                 return $html;
    //             })
    //             ->rawColumns(['action'])
    //             ->make(true);
    //     }

    //     $business_locations = BusinessLocation::forDropdown($business_id);
    //     $suppliers = Contact::customersDropdown($business_id, false);
    //     $orderStatuses = $this->productUtil->orderStatuses();
    //     return view('sell.request.index')
    //         ->with(compact('requests','business_locations', 'suppliers', 'orderStatuses'));
    // }
    // public function draftRequest()
    // {
    //     if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
    //         abort(403, 'Unauthorized action.');
    //     }
    //     $business_id = request()->session()->get('user.business_id');
    //     $requests = CustomerRequest::where('business_id', $business_id)
    //             ->with('contact:id,name') // Load contact name
    //             ->get();
    //     if (request()->ajax()) {
    //         if (!empty(request()->customer_id)) {
    //             $purchases->where('contacts.id', request()->customer_id);
    //         }
    //         if (!empty(request()->status)) {
    //             $requests->where('status', request()->status);
    //         }
            
    //         if (!empty(request()->start_date) && !empty(request()->end_date)) {
    //             $start = request()->start_date;
    //             $end =  request()->end_date;
    //             $requests->whereDate('created_at', '>=', $start)
    //                         ->whereDate('created_at', '<=', $end);
    //         }
    //         return Datatables::of($requests)
    //             ->addIndexColumn() // Adds Sr#
    //             ->addColumn('name', function ($row) {
    //                 return optional($row->contact)->name ?? 'N/A';
    //             })
    //             ->addColumn('ref_no', function ($row) {
    //                 return $row->request_reference;
    //             })
    //             ->addColumn('status', function ($row) {
    //                 return ucfirst($row->status);
    //             })
    //             ->addColumn('action', function ($row) {
    //                 $html = '<div class="btn-group">
    //                             <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
    //                                 data-toggle="dropdown" aria-expanded="false">' .
    //                                 __("messages.actions") .
    //                                 '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
    //                             </button>
    //                             <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                    
    //                 if (auth()->user()->can("customer_request.view")) {
    //                     $html .= '<li><a href="#" data-href="' . action('PurchaseController@show', [$row->id]) . '" 
    //                                 class="btn-modal" data-container=".view_modal">
    //                                 <i class="fas fa-eye" aria-hidden="true"></i> ' . __("messages.view") . '</a></li>';
    //                 }
    //                 if (auth()->user()->can("customer_request.update")) {
    //                     $html .= '<li><a href="' . action('PurchaseController@edit', [$row->id]) . '">
    //                                 <i class="fas fa-edit"></i> ' . __("messages.edit") . '</a></li>';
    //                 }
    //                 if (auth()->user()->can("customer_request.delete")) {
    //                     $html .= '<li><a href="' . action('PurchaseController@destroy', [$row->id]) . '" 
    //                                 class="delete-request">
    //                                 <i class="fas fa-trash"></i> ' . __("messages.delete") . '</a></li>';
    //                 }
                    
    //                 $html .= '</ul></div>';
    //                 return $html;
    //             })
    //             ->rawColumns(['action'])
    //             ->make(true);
    //     }

    //     $business_locations = BusinessLocation::forDropdown($business_id);
    //     $suppliers = Contact::customersDropdown($business_id, false);
    //     $orderStatuses = $this->productUtil->orderStatuses();
    //     return view('sell.request.index')
    //         ->with(compact('requests','business_locations', 'suppliers', 'orderStatuses'));
    // }
    // public function listQuoteRequest()
    // {
    //     if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
    //         abort(403, 'Unauthorized action.');
    //     }
    //     $business_id = request()->session()->get('user.business_id');
    //     $requests = CustomerRequest::where('business_id', $business_id)
    //             ->with('contact:id,name') // Load contact name
    //             ->get();
    //     if (request()->ajax()) {
    //         if (!empty(request()->customer_id)) {
    //             $purchases->where('contacts.id', request()->customer_id);
    //         }
    //         if (!empty(request()->status)) {
    //             $requests->where('status', request()->status);
    //         }
            
    //         if (!empty(request()->start_date) && !empty(request()->end_date)) {
    //             $start = request()->start_date;
    //             $end =  request()->end_date;
    //             $requests->whereDate('created_at', '>=', $start)
    //                         ->whereDate('created_at', '<=', $end);
    //         }
    //         return Datatables::of($requests)
    //             ->addIndexColumn() // Adds Sr#
    //             ->addColumn('name', function ($row) {
    //                 return optional($row->contact)->name ?? 'N/A';
    //             })
    //             ->addColumn('ref_no', function ($row) {
    //                 return $row->request_reference;
    //             })
    //             ->addColumn('status', function ($row) {
    //                 return ucfirst($row->status);
    //             })
    //             ->addColumn('action', function ($row) {
    //                 $html = '<div class="btn-group">
    //                             <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
    //                                 data-toggle="dropdown" aria-expanded="false">' .
    //                                 __("messages.actions") .
    //                                 '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
    //                             </button>
    //                             <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                    
    //                 if (auth()->user()->can("customer_request.view")) {
    //                     $html .= '<li><a href="#" data-href="' . action('PurchaseController@show', [$row->id]) . '" 
    //                                 class="btn-modal" data-container=".view_modal">
    //                                 <i class="fas fa-eye" aria-hidden="true"></i> ' . __("messages.view") . '</a></li>';
    //                 }
    //                 if (auth()->user()->can("customer_request.update")) {
    //                     $html .= '<li><a href="' . action('PurchaseController@edit', [$row->id]) . '">
    //                                 <i class="fas fa-edit"></i> ' . __("messages.edit") . '</a></li>';
    //                 }
    //                 if (auth()->user()->can("customer_request.delete")) {
    //                     $html .= '<li><a href="' . action('PurchaseController@destroy', [$row->id]) . '" 
    //                                 class="delete-request">
    //                                 <i class="fas fa-trash"></i> ' . __("messages.delete") . '</a></li>';
    //                 }
                    
    //                 $html .= '</ul></div>';
    //                 return $html;
    //             })
    //             ->rawColumns(['action'])
    //             ->make(true);
    //     }

    //     $business_locations = BusinessLocation::forDropdown($business_id);
    //     $suppliers = Contact::customersDropdown($business_id, false);
    //     $orderStatuses = $this->productUtil->orderStatuses();
    //     return view('sell.request.index')
    //         ->with(compact('requests','business_locations', 'suppliers', 'orderStatuses'));
    // }
    // public function acceptedQuoteRequest()
    // {
    //     if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
    //         abort(403, 'Unauthorized action.');
    //     }
    //     $business_id = request()->session()->get('user.business_id');
    //     $requests = CustomerRequest::where('business_id', $business_id)
    //             ->with('contact:id,name') // Load contact name
    //             ->get();
    //     if (request()->ajax()) {
    //         if (!empty(request()->customer_id)) {
    //             $purchases->where('contacts.id', request()->customer_id);
    //         }
    //         if (!empty(request()->status)) {
    //             $requests->where('status', request()->status);
    //         }
            
    //         if (!empty(request()->start_date) && !empty(request()->end_date)) {
    //             $start = request()->start_date;
    //             $end =  request()->end_date;
    //             $requests->whereDate('created_at', '>=', $start)
    //                         ->whereDate('created_at', '<=', $end);
    //         }
    //         return Datatables::of($requests)
    //             ->addIndexColumn() // Adds Sr#
    //             ->addColumn('name', function ($row) {
    //                 return optional($row->contact)->name ?? 'N/A';
    //             })
    //             ->addColumn('ref_no', function ($row) {
    //                 return $row->request_reference;
    //             })
    //             ->addColumn('status', function ($row) {
    //                 return ucfirst($row->status);
    //             })
    //             ->addColumn('action', function ($row) {
    //                 $html = '<div class="btn-group">
    //                             <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
    //                                 data-toggle="dropdown" aria-expanded="false">' .
    //                                 __("messages.actions") .
    //                                 '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
    //                             </button>
    //                             <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                    
    //                 if (auth()->user()->can("customer_request.view")) {
    //                     $html .= '<li><a href="#" data-href="' . action('PurchaseController@show', [$row->id]) . '" 
    //                                 class="btn-modal" data-container=".view_modal">
    //                                 <i class="fas fa-eye" aria-hidden="true"></i> ' . __("messages.view") . '</a></li>';
    //                 }
    //                 if (auth()->user()->can("customer_request.update")) {
    //                     $html .= '<li><a href="' . action('PurchaseController@edit', [$row->id]) . '">
    //                                 <i class="fas fa-edit"></i> ' . __("messages.edit") . '</a></li>';
    //                 }
    //                 if (auth()->user()->can("customer_request.delete")) {
    //                     $html .= '<li><a href="' . action('PurchaseController@destroy', [$row->id]) . '" 
    //                                 class="delete-request">
    //                                 <i class="fas fa-trash"></i> ' . __("messages.delete") . '</a></li>';
    //                 }
                    
    //                 $html .= '</ul></div>';
    //                 return $html;
    //             })
    //             ->rawColumns(['action'])
    //             ->make(true);
    //     }

    //     $business_locations = BusinessLocation::forDropdown($business_id);
    //     $suppliers = Contact::customersDropdown($business_id, false);
    //     $orderStatuses = $this->productUtil->orderStatuses();
    //     return view('sell.request.index')
    //         ->with(compact('requests','business_locations', 'suppliers', 'orderStatuses'));
    // }
    // public function rejectedQuoteRequest()
    // {
    //     if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
    //         abort(403, 'Unauthorized action.');
    //     }
    //     $business_id = request()->session()->get('user.business_id');
    //     $requests = CustomerRequest::where('business_id', $business_id)
    //             ->with('contact:id,name') // Load contact name
    //             ->get();
    //     if (request()->ajax()) {
    //         if (!empty(request()->customer_id)) {
    //             $purchases->where('contacts.id', request()->customer_id);
    //         }
    //         if (!empty(request()->status)) {
    //             $requests->where('status', request()->status);
    //         }
            
    //         if (!empty(request()->start_date) && !empty(request()->end_date)) {
    //             $start = request()->start_date;
    //             $end =  request()->end_date;
    //             $requests->whereDate('created_at', '>=', $start)
    //                         ->whereDate('created_at', '<=', $end);
    //         }
    //         return Datatables::of($requests)
    //             ->addIndexColumn() // Adds Sr#
    //             ->addColumn('name', function ($row) {
    //                 return optional($row->contact)->name ?? 'N/A';
    //             })
    //             ->addColumn('ref_no', function ($row) {
    //                 return $row->request_reference;
    //             })
    //             ->addColumn('status', function ($row) {
    //                 return ucfirst($row->status);
    //             })
    //             ->addColumn('action', function ($row) {
    //                 $html = '<div class="btn-group">
    //                             <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
    //                                 data-toggle="dropdown" aria-expanded="false">' .
    //                                 __("messages.actions") .
    //                                 '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
    //                             </button>
    //                             <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                    
    //                 if (auth()->user()->can("customer_request.view")) {
    //                     $html .= '<li><a href="#" data-href="' . action('PurchaseController@show', [$row->id]) . '" 
    //                                 class="btn-modal" data-container=".view_modal">
    //                                 <i class="fas fa-eye" aria-hidden="true"></i> ' . __("messages.view") . '</a></li>';
    //                 }
    //                 if (auth()->user()->can("customer_request.update")) {
    //                     $html .= '<li><a href="' . action('PurchaseController@edit', [$row->id]) . '">
    //                                 <i class="fas fa-edit"></i> ' . __("messages.edit") . '</a></li>';
    //                 }
    //                 if (auth()->user()->can("customer_request.delete")) {
    //                     $html .= '<li><a href="' . action('PurchaseController@destroy', [$row->id]) . '" 
    //                                 class="delete-request">
    //                                 <i class="fas fa-trash"></i> ' . __("messages.delete") . '</a></li>';
    //                 }
                    
    //                 $html .= '</ul></div>';
    //                 return $html;
    //             })
    //             ->rawColumns(['action'])
    //             ->make(true);
    //     }

    //     $business_locations = BusinessLocation::forDropdown($business_id);
    //     $suppliers = Contact::customersDropdown($business_id, false);
    //     $orderStatuses = $this->productUtil->orderStatuses();
    //     return view('sell.request.index')
    //         ->with(compact('requests','business_locations', 'suppliers', 'orderStatuses'));
    // }
    // public function disputeQuoteRequest()
    // {
    //     if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
    //         abort(403, 'Unauthorized action.');
    //     }
    //     $business_id = request()->session()->get('user.business_id');
    //     $requests = CustomerRequest::where('business_id', $business_id)
    //             ->with('contact:id,name') // Load contact name
    //             ->get();
    //     if (request()->ajax()) {
    //         if (!empty(request()->customer_id)) {
    //             $purchases->where('contacts.id', request()->customer_id);
    //         }
    //         if (!empty(request()->status)) {
    //             $requests->where('status', request()->status);
    //         }
            
    //         if (!empty(request()->start_date) && !empty(request()->end_date)) {
    //             $start = request()->start_date;
    //             $end =  request()->end_date;
    //             $requests->whereDate('created_at', '>=', $start)
    //                         ->whereDate('created_at', '<=', $end);
    //         }
    //         return Datatables::of($requests)
    //             ->addIndexColumn() // Adds Sr#
    //             ->addColumn('name', function ($row) {
    //                 return optional($row->contact)->name ?? 'N/A';
    //             })
    //             ->addColumn('ref_no', function ($row) {
    //                 return $row->request_reference;
    //             })
    //             ->addColumn('status', function ($row) {
    //                 return ucfirst($row->status);
    //             })
    //             ->addColumn('action', function ($row) {
    //                 $html = '<div class="btn-group">
    //                             <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
    //                                 data-toggle="dropdown" aria-expanded="false">' .
    //                                 __("messages.actions") .
    //                                 '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
    //                             </button>
    //                             <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                    
    //                 if (auth()->user()->can("customer_request.view")) {
    //                     $html .= '<li><a href="#" data-href="' . action('PurchaseController@show', [$row->id]) . '" 
    //                                 class="btn-modal" data-container=".view_modal">
    //                                 <i class="fas fa-eye" aria-hidden="true"></i> ' . __("messages.view") . '</a></li>';
    //                 }
    //                 if (auth()->user()->can("customer_request.update")) {
    //                     $html .= '<li><a href="' . action('PurchaseController@edit', [$row->id]) . '">
    //                                 <i class="fas fa-edit"></i> ' . __("messages.edit") . '</a></li>';
    //                 }
    //                 if (auth()->user()->can("customer_request.delete")) {
    //                     $html .= '<li><a href="' . action('PurchaseController@destroy', [$row->id]) . '" 
    //                                 class="delete-request">
    //                                 <i class="fas fa-trash"></i> ' . __("messages.delete") . '</a></li>';
    //                 }
                    
    //                 $html .= '</ul></div>';
    //                 return $html;
    //             })
    //             ->rawColumns(['action'])
    //             ->make(true);
    //     }

    //     $business_locations = BusinessLocation::forDropdown($business_id);
    //     $suppliers = Contact::customersDropdown($business_id, false);
    //     $orderStatuses = $this->productUtil->orderStatuses();
    //     return view('sell.request.index')
    //         ->with(compact('requests','business_locations', 'suppliers', 'orderStatuses'));
    // }
    // public function pendingPurchaseRequest()
    // {
    //     if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
    //         abort(403, 'Unauthorized action.');
    //     }
    //     $business_id = request()->session()->get('user.business_id');
    //     $requests = CustomerRequest::where('business_id', $business_id)
    //             ->with('contact:id,name') // Load contact name
    //             ->get();
    //     if (request()->ajax()) {
    //         if (!empty(request()->customer_id)) {
    //             $purchases->where('contacts.id', request()->customer_id);
    //         }
    //         if (!empty(request()->status)) {
    //             $requests->where('status', request()->status);
    //         }
            
    //         if (!empty(request()->start_date) && !empty(request()->end_date)) {
    //             $start = request()->start_date;
    //             $end =  request()->end_date;
    //             $requests->whereDate('created_at', '>=', $start)
    //                         ->whereDate('created_at', '<=', $end);
    //         }
    //         return Datatables::of($requests)
    //             ->addIndexColumn() // Adds Sr#
    //             ->addColumn('name', function ($row) {
    //                 return optional($row->contact)->name ?? 'N/A';
    //             })
    //             ->addColumn('ref_no', function ($row) {
    //                 return $row->request_reference;
    //             })
    //             ->addColumn('status', function ($row) {
    //                 return ucfirst($row->status);
    //             })
    //             ->addColumn('action', function ($row) {
    //                 $html = '<div class="btn-group">
    //                             <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
    //                                 data-toggle="dropdown" aria-expanded="false">' .
    //                                 __("messages.actions") .
    //                                 '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
    //                             </button>
    //                             <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                    
    //                 if (auth()->user()->can("customer_request.view")) {
    //                     $html .= '<li><a href="#" data-href="' . action('PurchaseController@show', [$row->id]) . '" 
    //                                 class="btn-modal" data-container=".view_modal">
    //                                 <i class="fas fa-eye" aria-hidden="true"></i> ' . __("messages.view") . '</a></li>';
    //                 }
    //                 if (auth()->user()->can("customer_request.update")) {
    //                     $html .= '<li><a href="' . action('PurchaseController@edit', [$row->id]) . '">
    //                                 <i class="fas fa-edit"></i> ' . __("messages.edit") . '</a></li>';
    //                 }
    //                 if (auth()->user()->can("customer_request.delete")) {
    //                     $html .= '<li><a href="' . action('PurchaseController@destroy', [$row->id]) . '" 
    //                                 class="delete-request">
    //                                 <i class="fas fa-trash"></i> ' . __("messages.delete") . '</a></li>';
    //                 }
                    
    //                 $html .= '</ul></div>';
    //                 return $html;
    //             })
    //             ->rawColumns(['action'])
    //             ->make(true);
    //     }

    //     $business_locations = BusinessLocation::forDropdown($business_id);
    //     $suppliers = Contact::customersDropdown($business_id, false);
    //     $orderStatuses = $this->productUtil->orderStatuses();
    //     return view('sell.request.index')
    //         ->with(compact('requests','business_locations', 'suppliers', 'orderStatuses'));
    // }
    // public function purchaseOrderListRequest()
    // {
    //     if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
    //         abort(403, 'Unauthorized action.');
    //     }
    //     $business_id = request()->session()->get('user.business_id');
    //     $requests = CustomerRequest::where('business_id', $business_id)
    //             ->with('contact:id,name') // Load contact name
    //             ->get();
    //     if (request()->ajax()) {
    //         if (!empty(request()->customer_id)) {
    //             $purchases->where('contacts.id', request()->customer_id);
    //         }
    //         if (!empty(request()->status)) {
    //             $requests->where('status', request()->status);
    //         }
            
    //         if (!empty(request()->start_date) && !empty(request()->end_date)) {
    //             $start = request()->start_date;
    //             $end =  request()->end_date;
    //             $requests->whereDate('created_at', '>=', $start)
    //                         ->whereDate('created_at', '<=', $end);
    //         }
    //         return Datatables::of($requests)
    //             ->addIndexColumn() // Adds Sr#
    //             ->addColumn('name', function ($row) {
    //                 return optional($row->contact)->name ?? 'N/A';
    //             })
    //             ->addColumn('ref_no', function ($row) {
    //                 return $row->request_reference;
    //             })
    //             ->addColumn('status', function ($row) {
    //                 return ucfirst($row->status);
    //             })
    //             ->addColumn('action', function ($row) {
    //                 $html = '<div class="btn-group">
    //                             <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
    //                                 data-toggle="dropdown" aria-expanded="false">' .
    //                                 __("messages.actions") .
    //                                 '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
    //                             </button>
    //                             <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                    
    //                 if (auth()->user()->can("customer_request.view")) {
    //                     $html .= '<li><a href="#" data-href="' . action('PurchaseController@show', [$row->id]) . '" 
    //                                 class="btn-modal" data-container=".view_modal">
    //                                 <i class="fas fa-eye" aria-hidden="true"></i> ' . __("messages.view") . '</a></li>';
    //                 }
    //                 if (auth()->user()->can("customer_request.update")) {
    //                     $html .= '<li><a href="' . action('PurchaseController@edit', [$row->id]) . '">
    //                                 <i class="fas fa-edit"></i> ' . __("messages.edit") . '</a></li>';
    //                 }
    //                 if (auth()->user()->can("customer_request.delete")) {
    //                     $html .= '<li><a href="' . action('PurchaseController@destroy', [$row->id]) . '" 
    //                                 class="delete-request">
    //                                 <i class="fas fa-trash"></i> ' . __("messages.delete") . '</a></li>';
    //                 }
                    
    //                 $html .= '</ul></div>';
    //                 return $html;
    //             })
    //             ->rawColumns(['action'])
    //             ->make(true);
    //     }

    //     $business_locations = BusinessLocation::forDropdown($business_id);
    //     $suppliers = Contact::customersDropdown($business_id, false);
    //     $orderStatuses = $this->productUtil->orderStatuses();
    //     return view('sell.request.index')
    //         ->with(compact('requests','business_locations', 'suppliers', 'orderStatuses'));
    // }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

        $taxes = TaxRate::where('business_id', $business_id)
                        ->ExcludeForTaxGroup()
                        ->get();
        $orderStatuses = $this->productUtil->orderStatuses();
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

        $default_purchase_status = null;
        if (request()->session()->get('business.enable_purchase_status') != 1) {
            $default_purchase_status = 'received';
        }

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);

        $payment_line = $this->dummyPaymentLine;
        $payment_types = $this->productUtil->payment_types(null, true, $business_id);

        //Accounts
        $accounts = $this->moduleUtil->accountsDropdown($business_id, true);

        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];

        return view('purchase.create')
            ->with(compact('taxes', 'orderStatuses', 'business_locations', 'currency_details', 'default_purchase_status', 'customer_groups', 'types', 'shortcuts', 'payment_line', 'payment_types', 'accounts', 'bl_attributes', 'common_settings'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $business_id = $request->session()->get('user.business_id');
            $business_location = BusinessLocation::find($request->location_id);
            $currency_exchange_rate = Currency::where('id', $business_location->currency_id)->first();

            //Check if subscribed or not
            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse(action('PurchaseController@index'));
            }

            $transaction_data = $request->only([ 'ref_no', 'status', 'contact_id', 'transaction_date', 'total_before_tax', 'location_id', 'recieve_location_id',
            'discount_type', 'discount_amount','tax_id', 'tax_amount', 'shipping_details', 'shipping_charges', 'final_total', 'additional_notes', 'exchange_rate', 'pay_term_number', 'pay_term_type', 'purchase_order_ids']);
            //TODO: Check for "Undefined index: total_before_tax" issue
            //Adding temporary fix by validating
            $request->validate([
                'status' => 'required',
                'contact_id' => 'required',
                'transaction_date' => 'required',
                'total_before_tax' => 'required',
                'location_id' => 'required',
                'final_total' => 'required',
                'document' => 'file|max:'. (config('constants.document_size_limit') / 1000)
            ]);

            $user_id = $request->session()->get('user.id');
            $enable_product_editing = $request->session()->get('business.enable_editing_product_from_purchase');

            //Update business exchange rate.
            Business::update_business($business_id, ['p_exchange_rate' => ($transaction_data['exchange_rate'])]);
            $exchange_rate = $transaction_data['exchange_rate'] = $currency_exchange_rate->rate;
            $currency_details = $currency_exchange_rate;
            //unformat input values
            $transaction_data['total_before_tax'] = number_format($this->productUtil->num_uf((float)str_replace(",","", $transaction_data['total_before_tax']), $currency_details), 2);
            // If discount type is fixed them multiply by exchange rate, else don't
            if ($transaction_data['discount_type'] == 'fixed') {
                $transaction_data['discount_amount'] = $this->productUtil->num_uf((float)str_replace(",","",$transaction_data['discount_amount']) , $currency_details);
            } elseif ($transaction_data['discount_type'] == 'percentage') {
                $transaction_data['discount_amount'] = $this->productUtil->num_uf($transaction_data['discount_amount'], $currency_details);
            } else {
                $transaction_data['discount_amount'] = 0;
            }

            $transaction_data['tax_amount'] = $this->productUtil->num_uf((float)str_replace(",","",$transaction_data['tax_amount']) , $currency_details);
            $transaction_data['shipping_charges'] = $this->productUtil->num_uf((float)str_replace(",","",$transaction_data['shipping_charges']) , $currency_details);
            $transaction_data['final_total'] = $this->productUtil->num_uf(str_replace(",","", number_format(str_replace(",","",$transaction_data['final_total']), 2)), $currency_details);
            $transaction_data['business_id'] = $business_id;
            $transaction_data['created_by'] = $user_id;
            $transaction_data['type'] = 'purchase';
            $transaction_data['payment_status'] = 'due';
            $transaction_data['transaction_date'] = $this->productUtil->uf_date($transaction_data['transaction_date'], true);
            //upload document
            $transaction_data['document'] = $this->transactionUtil->uploadFile($request, 'document', 'documents');

            $transaction_data['custom_field_1'] = $request->input('custom_field_1', null);
            $transaction_data['custom_field_2'] = $request->input('custom_field_2', null);
            $transaction_data['custom_field_3'] = $request->input('custom_field_3', null);
            $transaction_data['custom_field_4'] = $request->input('custom_field_4', null);

            $transaction_data['shipping_custom_field_1'] = $request->input('shipping_custom_field_1', null);
            $transaction_data['shipping_custom_field_2'] = $request->input('shipping_custom_field_2', null);
            $transaction_data['shipping_custom_field_3'] = $request->input('shipping_custom_field_3', null);
            $transaction_data['shipping_custom_field_4'] = $request->input('shipping_custom_field_4', null);
            $transaction_data['shipping_custom_field_5'] = $request->input('shipping_custom_field_5', null);
            if ($request->input('additional_expense_value_1') != '') {
                $transaction_data['additional_expense_key_1'] = $request->input('additional_expense_key_1'); 
                $transaction_data['additional_expense_value_1'] = $this->productUtil->num_uf((float)str_replace(",","",$request->input('additional_expense_value_1')), $currency_details);
            }

            if ($request->input('additional_expense_value_2') != '') {
                $transaction_data['additional_expense_key_2'] = $request->input('additional_expense_key_2');
                $transaction_data['additional_expense_value_2'] = $this->productUtil->num_uf((float)str_replace(",","",$request->input('additional_expense_value_2')), $currency_details);
            }

            if ($request->input('additional_expense_value_3') != '') {
                $transaction_data['additional_expense_key_3'] = $request->input('additional_expense_key_3');
                $transaction_data['additional_expense_value_3'] = $this->productUtil->num_uf((float)str_replace(",","",$request->input('additional_expense_value_3')), $currency_details);
            }

            if ($request->input('additional_expense_value_4') != '') {
                $transaction_data['additional_expense_key_4'] = $request->input('additional_expense_key_4');
                $transaction_data['additional_expense_value_4'] = $this->productUtil->num_uf((float)str_replace(",","",$request->input('additional_expense_value_4')), $currency_details);
            }
            DB::beginTransaction();
            //Update reference count
            $ref_count = $this->productUtil->setAndGetReferenceCount($transaction_data['type']);
            //Generate reference number
            if (empty($transaction_data['ref_no'])) {
                $transaction_data['ref_no'] = $this->productUtil->generateReferenceNumber($transaction_data['type'], $ref_count);
            }
            $transaction = Transaction::create($transaction_data);
            $purchase_lines = [];
            $purchases = $request->input('purchases');
            for($i = 0 ; $i < count($purchases); $i++) {
                $purchases[$i]['pp_without_discount'] = number_format(str_replace(",","", $purchases[$i]['pp_without_discount']) , 2);
                $purchases[$i]['purchase_price'] = number_format(str_replace(",","",$purchases[$i]['purchase_price']) , 2);
                $purchases[$i]['purchase_price_inc_tax'] = number_format(str_replace(",","", $purchases[$i]['purchase_price_inc_tax']) , 2);
                $purchases[$i]['item_tax'] = number_format(str_replace(",", "", $purchases[$i]['item_tax']) , 2);
                $purchases[$i]['default_sell_price'] = number_format(str_replace(",","",$purchases[$i]['default_sell_price']) , 2, '.', '');
            }
            // dd($purchases);
            $this->productUtil->createOrUpdatePurchaseLines($transaction, $purchases, $currency_details, $enable_product_editing);
            //Add Purchase payments
            $this->transactionUtil->createOrUpdatePaymentLines($transaction, $request->input('payment'), null, null, true);

            //update payment status
            $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

            if (!empty($transaction->purchase_order_ids)) {
                $this->transactionUtil->updatePurchaseOrderStatus($transaction->purchase_order_ids);
            }
            
            //Adjust stock over selling if found
            $this->productUtil->adjustStockOverSelling($transaction);

            $this->transactionUtil->activityLog($transaction, 'added');
            
            DB::commit();
            
            $output = ['success' => 1,
                            'msg' => __('purchase.purchase_add_success')
                        ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        return redirect('purchases')->with('status', $output);
    }
    public function storeRequest(Request $request)
    {
        // if (!auth()->user()->can('purchase.create')) {
        //     abort(403, 'Unauthorized action.');
        // }
        
       
        try {
            $business_id = $request->session()->get('user.business_id');
            $business_location = BusinessLocation::find($request->location_id);
            $currency_exchange_rate = Currency::where('id', $business_location->currency_id)->first();

            //Check if subscribed or not
            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse(action('PurchaseController@index'));
            }
            $request->validate([
                'contact_id' => 'required',
                'location_id' => 'required',
                'recieve_location_id' => 'required|different:location_id'
            ],['recieve_location_id'=>'Foreign Business Location must be different from Business Location.']);

            $user_id = $request->session()->get('user.id');
            $currency_details = $currency_exchange_rate;
            //unformat input values
            
            DB::beginTransaction();
            //Generate reference number
            // $transaction_data['ref_no'] = $request->ref_no;
            $customerRequest=null;
            $customerRequest['business_id']=$business_id;
            $customerRequest['customer_id']=$request->contact_id;
            $customerRequest['request_reference']=$request->ref_no;
            $customerRequest['business_location_id']=$request->location_id;
            $customerRequest['foreign_business_location_id']=$request->recieve_location_id;
            $getRequest=CustomerRequest::create($customerRequest);
            // dd($getRequest);
            $purchases = $request->input('purchases');
            $purchases=array_values($purchases);
            for($i = 0 ; $i < count($purchases); $i++){
                $reqitem = new RequestItem();
                $reqitem->request_id=$getRequest['id'];
                $reqitem->product_id=$purchases[$i]['product_id'];
                $reqitem->variation_id=$purchases[$i]['variation_id'];
                $reqitem->quantity=$purchases[$i]['quantity'];
                $reqitem->purchase_price=$purchases[$i]['purchase_price'];
                $reqitem->sell_price_wot=$purchases[$i]['purchase_price'];
                $reqitem->save();
            }
            
            DB::commit();
            
            $output = ['success' => 1,
                            'msg' => __('request.request_add_success')
                        ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => $e->getMessage()
                        ];
        }

        return redirect('requests')->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->can('purchase.view')) {
            abort(403, 'Unauthorized action.');
        }
        $transaction = Transaction::where('id', $id)->first();
        
        $business_id = request()->session()->get('user.business_id');
        $business_location = BusinessLocation::where('id', $transaction->location_id)->first();
        $currency_details = Currency::where('id', $business_location->currency_id)->first();
        // dd($currency_details);
        $currency_details['rate'] = $currency_details->rate;
        $taxes = TaxRate::where('business_id', $business_id)
                            ->pluck('name', 'id');
        $purchase = Transaction::where('business_id', $business_id)
                                ->where('id', $id)
                                ->with(
                                    'contact',
                                    'purchase_lines',
                                    'purchase_lines.product',
                                    'purchase_lines.product.unit',
                                    'purchase_lines.product.second_unit',
                                    'purchase_lines.variations',
                                    'purchase_lines.variations.product_variation',
                                    'purchase_lines.sub_unit',
                                    'location',
                                    'recieveLocation',
                                    'payment_lines',
                                    'tax'
                                )
                                ->firstOrFail();
        // dd($purchase->toArray());
        foreach ($purchase->purchase_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
                $purchase->purchase_lines[$key] = $formated_purchase_line;
            }
        }
        
        $payment_methods = $this->productUtil->payment_types($purchase->location_id, true);

        $purchase_taxes = [];
        if (!empty($purchase->tax)) {
            if ($purchase->tax->is_tax_group) {
                $purchase_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($purchase->tax, $purchase->tax_amount));
            } else {
                $purchase_taxes[$purchase->tax->name] = $purchase->tax_amount;
            }
        }

        //Purchase orders
        $purchase_order_nos = '';
        $purchase_order_dates = '';
        if (!empty($purchase->purchase_order_ids)) {
            $purchase_orders = Transaction::find($purchase->purchase_order_ids);

            $purchase_order_nos = implode(', ', $purchase_orders->pluck('ref_no')->toArray());
            $order_dates = [];
            foreach ($purchase_orders as $purchase_order) {
                $order_dates[] = $this->transactionUtil->format_date($purchase_order->transaction_date, true);
            }
            $purchase_order_dates = implode(', ', $order_dates);
        }
        $activities = Activity::forSubject($purchase)
           ->with(['causer', 'subject'])
           ->latest()
           ->get();

        $statuses = $this->productUtil->orderStatuses();
        // dd($purchase->toArray());
        return view('purchase.show')
                ->with(compact('transaction','taxes', 'purchase', 'payment_methods', 'purchase_taxes', 'activities', 'statuses', 'purchase_order_nos', 'purchase_order_dates', 'currency_details'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('purchase.update')) {
            abort(403, 'Unauthorized action.');
        }
        $transaction = Transaction::where('id', $id)->first();

        $business_id = request()->session()->get('user.business_id');
        
        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action('PurchaseController@index'));
        }

        //Check if the transaction can be edited or not.
        $edit_days = request()->session()->get('business.transaction_edit_days');
        if (!$this->transactionUtil->canBeEdited($id, $edit_days)) {
            return back()
                ->with('status', ['success' => 0,
                    'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days])]);
        }
        //Check if return exist then not allowed
        if ($this->transactionUtil->isReturnExist($id)) {
            return back()->with('status', ['success' => 0,
                    'msg' => __('lang_v1.return_exist')]);
        }

        $business = Business::find($business_id);

        $taxes = TaxRate::where('business_id', $business_id)
                            ->ExcludeForTaxGroup()
                            ->get();
        $purchase = Transaction::where('business_id', $business_id)
                    ->where('id', $id)
                    ->with(
                        'contact',
                        'purchase_lines',
                        'purchase_lines.product',
                        'purchase_lines.product.unit',
                        'purchase_lines.product.second_unit',
                        //'purchase_lines.product.unit.sub_units',
                        'purchase_lines.variations',
                        'purchase_lines.variations.product_variation',
                        'location',
                        'purchase_lines.sub_unit',
                        'purchase_lines.purchase_order_line'
                    )
                    ->first();
        $business_location = BusinessLocation::find($purchase->location_id);
        $currency_details = Currency::where('id', $business_location->currency_id)->first();
        $currency_new = Currency::where('code', $currency_details->code)->where('business_id', $business_id)->first();
        if (!empty($currency_new)) {
            $currency_details = $currency_new;
        }
        foreach ($purchase->purchase_lines as $key => $value) {
            $purchase->purchase_lines[$key]['pp_without_discount'] = number_format(str_replace(",","", $value['pp_without_discount']) , 2);
            $purchase->purchase_lines[$key]['purchase_price'] = number_format(str_replace(",","", $value['purchase_price']) , 2);
            $purchase->purchase_lines[$key]['purchase_price_inc_tax'] = number_format(str_replace(",","", $value['purchase_price_inc_tax']) , 2);
            $purchase->purchase_lines[$key]['item_tax'] = number_format(str_replace(",","", $value['item_tax']) , 2);

            $purchase->purchase_lines[$key]->variations['default_purchase_price'] = number_format(str_replace(",","", $value['variations']['default_purchase_price']) , 2);
            $purchase->purchase_lines[$key]->variations['dpp_inc_tax'] = number_format(str_replace(",","", $value['variations']['dpp_inc_tax']) , 2);
            $purchase->purchase_lines[$key]->variations['default_sell_price'] = number_format(str_replace(",","", $value['variations']['default_sell_price']) , 2);
            $purchase->purchase_lines[$key]->variations['sell_price_inc_tax'] = number_format(str_replace(",","", $value['variations']['sell_price_inc_tax']) , 2);
            
            if (!empty($value->sub_unit_id)) {
                $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
                $purchase->purchase_lines[$key] = $formated_purchase_line;
            }
        }
        // dd($purchase->toArray());
        $orderStatuses = $this->productUtil->orderStatuses();

        $business_locations = BusinessLocation::forDropdown($business_id);

        $default_purchase_status = null;
        if (request()->session()->get('business.enable_purchase_status') != 1) {
            $default_purchase_status = 'received';
        }

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);

        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];

        $purchase_orders = null;
        if(!empty($common_settings['enable_purchase_order'])) {
            $purchase_orders = Transaction::where('business_id', $business_id)
                                        ->where('type', 'purchase_order')
                                        ->where('contact_id', $purchase->contact_id)
                                        ->where( function($q) use($purchase){
                                            $q->where('status', '!=', 'completed');

                                            if (!empty($purchase->purchase_order_ids)) {
                                                $q->orWhereIn('id', $purchase->purchase_order_ids);
                                            }
                                        })
                                        ->pluck('ref_no', 'id');
        }

        return view('purchase.edit')
            ->with(compact(
                'business_details',
                'transaction',
                'taxes',
                'purchase',
                'orderStatuses',
                'business_locations',
                'business',
                'currency_details',
                'default_purchase_status',
                'customer_groups',
                'types',
                'shortcuts',
                'purchase_orders',
                'common_settings'
            ));
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
        if (!auth()->user()->can('purchase.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $transaction = Transaction::findOrFail($id);
            //Validate document size
            $request->validate([
                'document' => 'file|max:'. (config('constants.document_size_limit') / 1000)
            ]);

            $before_status = $transaction->status;
            $business_id = request()->session()->get('user.business_id');
            $enable_product_editing = $request->session()->get('business.enable_editing_product_from_purchase');

            $transaction_before = $transaction->replicate();

            $business_location = BusinessLocation::find($transaction->location_id);
            $currency_details = Currency::where('id', $business_location->currency_id)->first();
            $currency_new = Currency::where('code', $currency_details->code)->where('business_id', $business_id)->first();
            if (!empty($currency_new)) {
                $currency_details = $currency_new;
            }
            $update_data = $request->only([ 'ref_no', 'status', 'contact_id',
                            'transaction_date', 'total_before_tax',
                            'discount_type', 'discount_amount', 'tax_id',
                            'tax_amount', 'shipping_details',
                            'shipping_charges', 'final_total',
                            'additional_notes', 'exchange_rate', 'pay_term_number', 'pay_term_type', 'purchase_order_ids']);

            $exchange_rate = $currency_details->rate;

            //Reverse exchage rate and save
            //$update_data['exchange_rate'] = number_format(1 / $update_data['exchange_rate'], 2);

            $update_data['transaction_date'] = $this->productUtil->uf_date($update_data['transaction_date'], true);

            //unformat input values
            // $update_data['total_before_tax'] = $this->productUtil->num_uf((float)str_replace(",","",$update_data['total_before_tax']) , $currency_details);
            $update_data['total_before_tax'] = number_format($this->productUtil->num_uf((float)str_replace(",","", $update_data['total_before_tax']), $currency_details), 2);

            // If discount type is fixed them multiply by exchange rate, else don't
            if ($update_data['discount_type'] == 'fixed') {
                $update_data['discount_amount'] = $this->productUtil->num_uf((float)str_replace(",","",$update_data['discount_amount']) , $currency_details);
            } elseif ($update_data['discount_type'] == 'percentage') {
                $update_data['discount_amount'] = $this->productUtil->num_uf($update_data['discount_amount'], $currency_details);
            } else {
                $update_data['discount_amount'] = 0;
            }

            $update_data['tax_amount'] = $this->productUtil->num_uf((float)str_replace(",","",$update_data['tax_amount']) , $currency_details);
            $update_data['shipping_charges'] = $this->productUtil->num_uf((float)str_replace(",","",$update_data['shipping_charges']) , $currency_details);
            $update_data['final_total'] = $this->productUtil->num_uf((float)str_replace(",","",$update_data['final_total']) , $currency_details);
            //unformat input values ends

            $update_data['custom_field_1'] = $request->input('custom_field_1', null);
            $update_data['custom_field_2'] = $request->input('custom_field_2', null);
            $update_data['custom_field_3'] = $request->input('custom_field_3', null);
            $update_data['custom_field_4'] = $request->input('custom_field_4', null);

            $update_data['shipping_custom_field_1'] = $request->input('shipping_custom_field_1', null);
            $update_data['shipping_custom_field_2'] = $request->input('shipping_custom_field_2', null);
            $update_data['shipping_custom_field_3'] = $request->input('shipping_custom_field_3', null);
            $update_data['shipping_custom_field_4'] = $request->input('shipping_custom_field_4', null);
            $update_data['shipping_custom_field_5'] = $request->input('shipping_custom_field_5', null);

            //upload document
            $document_name = $this->transactionUtil->uploadFile($request, 'document', 'documents');
            if (!empty($document_name)) {
                $update_data['document'] = $document_name;
            }

            $purchase_order_ids = $transaction->purchase_order_ids ?? [];

            $update_data['additional_expense_key_1'] = $request->input('additional_expense_key_1');
            $update_data['additional_expense_key_2'] = $request->input('additional_expense_key_2');
            $update_data['additional_expense_key_3'] = $request->input('additional_expense_key_3');
            $update_data['additional_expense_key_4'] = $request->input('additional_expense_key_4');

            $update_data['additional_expense_value_1'] = $request->input('additional_expense_value_1') != '' ? $this->productUtil->num_uf(str_replace(",","",$request->input('additional_expense_value_1')) , $currency_details) : 0;
            $update_data['additional_expense_value_2'] = $request->input('additional_expense_value_2') != '' ? $this->productUtil->num_uf(str_replace(",","",$request->input('additional_expense_value_2')) , $currency_details): 0;
            $update_data['additional_expense_value_3'] = $request->input('additional_expense_value_3') != '' ? $this->productUtil->num_uf(str_replace(",","",$request->input('additional_expense_value_3')) , $currency_details) : 0;
            $update_data['additional_expense_value_4'] = $request->input('additional_expense_value_4') != '' ? $this->productUtil->num_uf(str_replace(",","",$request->input('additional_expense_value_4')) , $currency_details) : 0;
            
            DB::beginTransaction();
            // dd($update_data);
            //update transaction
            $transaction->update($update_data);

            //Update transaction payment status
            $payment_status = $this->transactionUtil->updatePaymentStatus($transaction->id);
            $transaction->payment_status = $payment_status;

            $purchases = $request->input('purchases');
            $purchases = array_values($purchases);

            for($i = 0 ; $i < count($purchases); $i++) {
                $purchases[$i]['pp_without_discount'] = number_format(str_replace(",","", $purchases[$i]['pp_without_discount']) , 2);
                $purchases[$i]['purchase_price'] = number_format(str_replace(",","",$purchases[$i]['purchase_price']) , 2);
                $purchases[$i]['purchase_price_inc_tax'] = number_format(str_replace(",","", $purchases[$i]['purchase_price_inc_tax']) , 2);
                $purchases[$i]['item_tax'] = number_format(str_replace(",", "", $purchases[$i]['item_tax']) , 2);
                $purchases[$i]['default_sell_price'] = number_format(str_replace(",","",$purchases[$i]['default_sell_price']) , 2, '.', '');
            }

            $delete_purchase_lines = $this->productUtil->createOrUpdatePurchaseLines($transaction, $purchases, $currency_details, $enable_product_editing, $before_status);

            //Update mapping of purchase & Sell.
            $this->transactionUtil->adjustMappingPurchaseSellAfterEditingPurchase($before_status, $transaction, $delete_purchase_lines);

            //Adjust stock over selling if found
            $this->productUtil->adjustStockOverSelling($transaction);

            $new_purchase_order_ids = $transaction->purchase_order_ids ?? [];
            $purchase_order_ids = array_merge($purchase_order_ids, $new_purchase_order_ids);
            if (!empty($purchase_order_ids)) {
                $this->transactionUtil->updatePurchaseOrderStatus($purchase_order_ids);
            }

            $this->transactionUtil->activityLog($transaction, 'edited', $transaction_before);

            DB::commit();

            $output = ['success' => 1,
                            'msg' => __('purchase.purchase_update_success')
                        ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => $e->getMessage()
                        ];
            return back()->with('status', $output);
        }

        return redirect('purchases')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('purchase.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            if (request()->ajax()) {
                $business_id = request()->session()->get('user.business_id');

                //Check if return exist then not allowed
                if ($this->transactionUtil->isReturnExist($id)) {
                    $output = [
                        'success' => false,
                        'msg' => __('lang_v1.return_exist')
                    ];
                    return $output;
                }
        
                $transaction = Transaction::where('id', $id)
                                ->where('business_id', $business_id)
                                ->with(['purchase_lines'])
                                ->first();

                //Check if lot numbers from the purchase is selected in sale
                if (request()->session()->get('business.enable_lot_number') == 1 && $this->transactionUtil->isLotUsed($transaction)) {
                    $output = [
                        'success' => false,
                        'msg' => __('lang_v1.lot_numbers_are_used_in_sale')
                    ];
                    return $output;
                }
                
                $delete_purchase_lines = $transaction->purchase_lines;
                DB::beginTransaction();

                $log_properities = [
                    'id' => $transaction->id,
                    'ref_no' => $transaction->ref_no
                ];
                $this->transactionUtil->activityLog($transaction, 'purchase_deleted', $log_properities);

                $transaction_status = $transaction->status;
                if ($transaction_status != 'received') {
                    $transaction->delete();
                } else {
                    //Delete purchase lines first
                    $delete_purchase_line_ids = [];
                    foreach ($delete_purchase_lines as $purchase_line) {
                        $delete_purchase_line_ids[] = $purchase_line->id;
                        $this->productUtil->decreaseProductQuantity(
                            $purchase_line->product_id,
                            $purchase_line->variation_id,
                            $transaction->location_id,
                            $purchase_line->quantity
                        );
                    }
                    PurchaseLine::where('transaction_id', $transaction->id)
                                ->whereIn('id', $delete_purchase_line_ids)
                                ->delete();

                    //Update mapping of purchase & Sell.
                    $this->transactionUtil->adjustMappingPurchaseSellAfterEditingPurchase($transaction_status, $transaction, $delete_purchase_lines);
                }

                //Delete Transaction
                $transaction->delete();

                //Delete account transactions
                AccountTransaction::where('transaction_id', $id)->delete();

                DB::commit();

                $output = ['success' => true,
                            'msg' => __('lang_v1.purchase_delete_success')
                        ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' => $e->getMessage()
                        ];
        }

        return $output;
    }
    
    /**
     * Retrieves supliers list.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSuppliers()
    {
        if (request()->ajax()) {
            $term = request()->q;
            if (empty($term)) {
                return json_encode([]);
            }

            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');

            $query = Contact::where('business_id', $business_id)
                            ->active();

            $suppliers = $query->where(function ($query) use ($term) {
                $query->where('name', 'like', '%' . $term .'%')
                                ->orWhere('supplier_business_name', 'like', '%' . $term .'%')
                                ->orWhere('contacts.contact_id', 'like', '%' . $term .'%');
            })
                        ->select(
                            'contacts.id', 
                            DB::raw('IF(name="", supplier_business_name, name) as text'), 
                            'supplier_business_name as business_name', 
                            'contacts.mobile',
                            'contacts.address_line_1',
                            'contacts.address_line_2',
                            'contacts.city',
                            'contacts.state',
                            'contacts.country',
                            'contacts.zip_code',
                            'contacts.contact_id', 
                            'contacts.pay_term_type', 
                            'contacts.pay_term_number', 
                            'contacts.balance'
                        )
                        ->onlySuppliers()
                        ->get();
            return json_encode($suppliers);
        }
    }
    public function getCustomers(Request $request)
    {
        if (request()->ajax()) {
            $term = $request->input('q');
            
            if (empty($term)) {
                return json_encode([]);
            }
            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');

            $query = Contact::where('business_id', $business_id)
                            ->active();
            $suppliers = $query->where(function ($query) use ($term) {
                $query->where('name', 'like', '%' . $term .'%')
                                ->orWhere('supplier_business_name', 'like', '%' . $term .'%')
                                ->orWhere('contacts.contact_id', 'like', '%' . $term .'%');
            })
                        ->select(
                            'contacts.id', 
                            DB::raw('IF(name="", supplier_business_name, name) as text'), 
                            'supplier_business_name as business_name', 
                            'contacts.mobile',
                            'contacts.address_line_1',
                            'contacts.address_line_2',
                            'contacts.city',
                            'contacts.state',
                            'contacts.country',
                            'contacts.zip_code',
                            'contacts.contact_id', 
                            'contacts.pay_term_type', 
                            'contacts.pay_term_number', 
                            'contacts.balance'
                        )
                        ->OnlyCustomers()
                        ->get();
            return json_encode($suppliers);
        }
    }

    /**
     * Retrieves products list.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProducts()
    {
        if (request()->ajax()) {
            $term = request()->term;

            $check_enable_stock = true;
            if (isset(request()->check_enable_stock)) {
                $check_enable_stock = filter_var(request()->check_enable_stock, FILTER_VALIDATE_BOOLEAN);
            }

            $only_variations = false;
            if (isset(request()->only_variations)) {
                $only_variations = filter_var(request()->only_variations, FILTER_VALIDATE_BOOLEAN);
            }

            if (empty($term)) {
                return json_encode([]);
            }

            $business_id = request()->session()->get('user.business_id');
            $q = Product::leftJoin(
                'variations',
                'products.id',
                '=',
                'variations.product_id'
            )
                ->where(function ($query) use ($term) {
                    $query->where('products.name', 'like', '%' . $term .'%');
                    $query->orWhere('sku', 'like', '%' . $term .'%');
                    $query->orWhere('sub_sku', 'like', '%' . $term .'%');
                })
                ->active()
                ->where('business_id', $business_id)
                ->whereNull('variations.deleted_at')
                ->select(
                    'products.id as product_id',
                    'products.name',
                    'products.type',
                    // 'products.sku as sku',
                    'variations.id as variation_id',
                    'variations.name as variation',
                    'variations.sub_sku as sub_sku'
                )
                ->groupBy('variation_id');

            if ($check_enable_stock) {
                $q->where('enable_stock', 1);
            }
            if (!empty(request()->location_id)) {
                $q->ForLocation(request()->location_id);
            }
            $products = $q->get();
            $products_array = [];
            foreach ($products as $product) {
                $products_array[$product->product_id]['name'] = $product->name;
                $products_array[$product->product_id]['sku'] = $product->sub_sku;
                $products_array[$product->product_id]['type'] = $product->type;
                $products_array[$product->product_id]['variations'][]
                = [
                        'variation_id' => $product->variation_id,
                        'variation_name' => $product->variation,
                        'sub_sku' => $product->sub_sku
                        ];
            }

            $result = [];
            $i = 1;
            $no_of_records = $products->count();
            if (!empty($products_array)) {
                foreach ($products_array as $key => $value) {
                    if ($no_of_records > 1 && $value['type'] != 'single' && !$only_variations) {
                        $result[] = [ 'id' => $i,
                                    'text' => $value['name'] . ' - ' . $value['sku'],
                                    'variation_id' => 0,
                                    'product_id' => $key
                                ];
                    }
                    $name = $value['name'];
                    foreach ($value['variations'] as $variation) {
                        $text = $name;
                        if ($value['type'] == 'variable') {
                            $text = $text . ' (' . $variation['variation_name'] . ')';
                        }
                        $i++;
                        $result[] = [ 'id' => $i,
                                            'text' => $text . ' - ' . $variation['sub_sku'],
                                            'product_id' => $key ,
                                            'variation_id' => $variation['variation_id'],
                                        ];
                    }
                    $i++;
                }
            }
            
            return json_encode($result);
        }
    }
    
    /**
     * Retrieves products list.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPurchaseEntryRow(Request $request)
    {
        $intransitQuantity=0.00;
        if (request()->ajax()) {
            $product_id = $request->input('product_id');
            $variation_id = $request->input('variation_id');
            $business_id = request()->session()->get('user.business_id');
            $location_id = $request->input('location_id');
            $is_purchase_order = $request->has('is_purchase_order');
            $supplier_id = $request->input('supplier_id');
            $isRequestPage = $request->input('requestPage');
            
            $hide_tax = 'hide';
            if ($request->session()->get('business.enable_inline_tax') == 1) {
                $hide_tax = '';
            }

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            if (!empty($product_id)) {
                $row_count = $request->input('row_count');
                $product = Product::where('id', $product_id)
                                    ->with(['unit', 'second_unit'])
                                    ->first();
                
                $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit->id, false, $product_id);
                $business_location = BusinessLocation::where('id', $location_id)->first();
                $usd_currency_rate = Currency::where('code', 'USD')->first();
                $currency_exchange_rate = Currency::where('id', $business_location->currency_id)->first();

                $query = Variation::where('product_id', $product_id)
                                ->with([
                                    'product_variation', 
                                    'variation_location_details' => function ($q) use ($location_id) {
                                        $q->where('location_id', $location_id);
                                    }
                                ]);
                if ($variation_id !== '0') {
                    $query->where('id', $variation_id);
                }

                $variations =  $query->get();
                $taxes = TaxRate::where('business_id', $business_id)
                            ->ExcludeForTaxGroup()
                            ->get();
                // return json_encode($product);
                $last_purchase_line = $this->getLastPurchaseLine($variation_id, $location_id, $supplier_id);
                $viewFile='purchase.partials.purchase_entry_row';
                if($isRequestPage =="true"){
                    $getValues= $this->productUtil->getAvaliableQty($business_id,$variation_id,$location_id);
                    $stock_details = $this->productUtil->getVariationStockDetails($business_id, $variation_id, $location_id); 
                    $availableQty=$getValues['avaliabilityQty'];
                    $currentStock=$getValues['stockOnHand'];
                    $intransitQuantity=$getValues['intransitQuantity'];
                    $viewFile='purchase.partials.request_entry_row';
                    return view($viewFile)
                    ->with(compact(
                        'product',
                        'variations',
                        'row_count',
                        'variation_id',
                        'taxes',
                        'currency_details',
                        'hide_tax',
                        'sub_units',
                        'currency_exchange_rate',
                        'is_purchase_order',
                        'last_purchase_line',
                        'usd_currency_rate',
                        'intransitQuantity',
                        'availableQty',
                        'currentStock'
                    ));
                }
                else{
                    return view($viewFile)
                    ->with(compact(
                        'product',
                        'variations',
                        'row_count',
                        'variation_id',
                        'taxes',
                        'currency_details',
                        'hide_tax',
                        'sub_units',
                        'currency_exchange_rate',
                        'is_purchase_order',
                        'last_purchase_line',
                        'usd_currency_rate'
                    ));
                }
                
            }
        }
    }
    public function getRequestPurchaseEntryRow(Request $request)
    {
        if (request()->ajax()) {
            $product_id = $request->input('product_id');
            $variation_id = $request->input('variation_id');
            $business_id = request()->session()->get('user.business_id');
            $location_id = $request->input('location_id');
            $is_purchase_order = $request->has('is_purchase_order');
            $supplier_id = $request->input('supplier_id');

            $hide_tax = 'hide';
            if ($request->session()->get('business.enable_inline_tax') == 1) {
                $hide_tax = '';
            }

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            if (!empty($product_id)) {
                $row_count = $request->input('row_count');
                $product = Product::where('id', $product_id)
                                    ->with(['unit', 'second_unit'])
                                    ->first();
                
                $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit->id, false, $product_id);
                $business_location = BusinessLocation::where('id', $location_id)->first();
                $usd_currency_rate = Currency::where('code', 'USD')->first();
                $currency_exchange_rate = Currency::where('id', $business_location->currency_id)->first();

                $query = Variation::where('product_id', $product_id)
                                ->with([
                                    'product_variation', 
                                    'variation_location_details' => function ($q) use ($location_id) {
                                        $q->where('location_id', $location_id);
                                    }
                                ]);
                if ($variation_id !== '0') {
                    $query->where('id', $variation_id);
                }

                $variations =  $query->get();
                $taxes = TaxRate::where('business_id', $business_id)
                            ->ExcludeForTaxGroup()
                            ->get();
                // return json_encode($product);
                $last_purchase_line = $this->getLastPurchaseLine($variation_id, $location_id, $supplier_id);
                return view('purchase.partials.purchase_entry_row')
                    ->with(compact(
                        'product',
                        'variations',
                        'row_count',
                        'variation_id',
                        'taxes',
                        'currency_details',
                        'hide_tax',
                        'sub_units',
                        'currency_exchange_rate',
                        'is_purchase_order',
                        'last_purchase_line',
                        'usd_currency_rate'
                    ));
            }
        }
    }

    /**
     * Finds last purchase line of a variation for the supplier for a location
     *
     */
    private function getLastPurchaseLine($variation_id, $location_id, $supplier_id = null)
    {
        $query = PurchaseLine::join('transactions as t', 'purchase_lines.transaction_id', 
                        '=', 't.id')
                        ->where('t.location_id', $location_id)
                        ->where('t.type', 'purchase')
                        ->where('t.status', 'received')
                        ->where('purchase_lines.variation_id', $variation_id);

        if (!empty($supplier_id)) {
            $query = $query->where('t.contact_id', '=', $supplier_id);
        }    
        $purchase_line = $query->orderBy('transaction_date', 'desc')
                            ->select('purchase_lines.*')
                            ->first();

        return $purchase_line;                    
    }

    public function importPurchaseProducts(Request $request)
    {
       try {
            $file = $request->file('file');

            $parsed_array = Excel::toArray([], $file);
            //Remove header row
            $imported_data = array_splice($parsed_array[0], 1);

            $business_id = $request->session()->get('user.business_id');
            $location_id = $request->input('location_id');
            $row_count = $request->input('row_count');

            $formatted_data = [];
            $row_index = 0;
            $error_msg = '';
            foreach ($imported_data as $key => $value) {
                $row_index = $key + 1;
                $temp_array = [];
                
                if (!empty($value[0])) {
                    $variation = Variation::where('sub_sku', trim($value[0]))
                                        ->with([
                                            'product_variation',
                                            'variation_location_details' => 
                                                function($q) use ($location_id) {
                                                    $q->where('location_id', $location_id);
                                                }
                                        ])
                                        ->first();

                    $temp_array['variation'] = $variation;

                    if (empty($variation)) {
                        $error_msg = __('lang_v1.product_not_found_exception', ['row' => $row_index, 'sku' => $value[0]]);
                        break;
                    }

                    $product = Product::where('id', $variation->product_id)
                                    ->where('business_id', $business_id)
                                    ->with(['unit'])
                                    ->first();

                    if (empty($product)) {
                        $error_msg = __('lang_v1.product_not_found_exception', ['row' => $row_index, 'sku' => $value[0]]);
                        break;
                    }

                    $temp_array['product'] = $product;

                    $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit->id, false, $product->id);

                    $temp_array['sub_units'] = $sub_units;
                    
                } else {
                    $error_msg = __('lang_v1.product_not_found_exception', ['row' => $row_index, 'sku' => $value[0]]);
                    break;
                }

                if (!empty($value[0])) {
                    $temp_array['quantity'] = $value[1];
                } else {
                    $error_msg = __('lang_v1.quantity_required', ['row' => $row_index]);
                    break;
                }

                $temp_array['unit_cost_before_discount'] = !empty($value[2]) ? $value[2] : $variation->default_purchase_price;
                $temp_array['discount_percent'] = !empty($value[3]) ? $value[3] : 0;

                $tax_id = null;

                if (!empty($value[4])) {
                    $tax_name = trim($value[4]);
                    $tax = TaxRate::where('business_id', $business_id)
                                ->where('name', 'like', "%{$tax_name}%" )
                                ->first();

                    $tax_id =  $tax->id ?? $tax_id;
                }

                $temp_array['tax_id'] = $tax_id;
                $temp_array['lot_number'] = !empty($value[5]) ? $value[5] : null;
                $temp_array['mfg_date'] = !empty($value[6]) ? $this->productUtil->format_date($value[6]) : null;
                $temp_array['exp_date'] = !empty($value[7]) ? $this->productUtil->format_date($value[7]) : null;

                $formatted_data[] = $temp_array;
            }

            if (!empty($error_msg)) {
                return [
                    'success' => false,
                    'msg' => $error_msg
                ];
            }

            $hide_tax = 'hide';
            if ($request->session()->get('business.enable_inline_tax') == 1) {
                $hide_tax = '';
            }

            $taxes = TaxRate::where('business_id', $business_id)
                            ->ExcludeForTaxGroup()
                            ->get();

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            $html = view('purchase.partials.imported_purchase_product_rows')
                        ->with(compact('formatted_data', 'taxes', 'currency_details', 'hide_tax', 'row_count'))->render();

            return [
                    'success' => true,
                    'msg' => __('lang_v.imported'),
                    'html' => $html
                ];
       } catch (\Exception $e) {
           return [
                    'success' => false,
                    'msg' => $e->getMessage()
                ];
       }
    }

    public function getPurchaseOrderLines($purchase_order_id)
    {
        $business_id = request()->session()->get('user.business_id');
        
        $purchase_order = Transaction::where('business_id', $business_id)
                        ->where('type', 'purchase_order')
                        ->with(['purchase_lines', 'purchase_lines.variations', 
                            'purchase_lines.product', 'purchase_lines.product.unit', 'purchase_lines.variations.product_variation' ])
                        ->findOrFail($purchase_order_id);

        $taxes = TaxRate::where('business_id', $business_id)
                            ->ExcludeForTaxGroup()
                            ->get();

        $sub_units_array = [];
        foreach ($purchase_order->purchase_lines as $pl) {
            $sub_units_array[$pl->id] = $this->productUtil->getSubUnits($business_id, $pl->product->unit->id, false, $pl->product_id);
        }
        $hide_tax = request()->session()->get('business.enable_inline_tax') == 1 ? '' : 'hide';
        $business_location = BusinessLocation::where('id', $purchase_order->location_id)->first();
        $currency_details = Currency::where('id', $business_location->currency_id)->first();

        $currency_new = Currency::where('code', $currency_details->code)->where('business_id', $business_id)->first();
        if (!empty($currency_new)) {
            $currency_details = $currency_new;
        }

        $row_count = request()->input('row_count');

        $html =  view('purchase.partials.purchase_order_lines')
                ->with(compact(
                    'purchase_order',
                    'taxes',
                    'hide_tax',
                    'currency_details',
                    'row_count',
                    'sub_units_array'
                ))->render();

        return [
            'html' => $html,
            'po' => $purchase_order
        ];

    }
    
    /**
     * Checks if ref_number and supplier combination already exists.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkRefNumber(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $contact_id = $request->input('contact_id');
        $ref_no = $request->input('ref_no');
        $purchase_id = $request->input('purchase_id');

        $count = 0;
        if (!empty($contact_id) && !empty($ref_no)) {
            //check in transactions table
            $query = Transaction::where('business_id', $business_id)
                            ->where('ref_no', $ref_no)
                            ->where('contact_id', $contact_id);
            if (!empty($purchase_id)) {
                $query->where('id', '!=', $purchase_id);
            }
            $count = $query->count();
        }
        if ($count == 0) {
            echo "true";
            exit;
        } else {
            echo "false";
            exit;
        }
    }

    /**
     * Checks if ref_number and supplier combination already exists.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function printInvoice($id)
    {
        try {
            $transaction = Transaction::where('id', $id)->first();
            $business_id = request()->session()->get('user.business_id');
            $taxes = TaxRate::where('business_id', $business_id)
                                ->pluck('name', 'id');
            // $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
            $business_location = BusinessLocation::where('id', $transaction->location_id)->first();
            $currency_details = Currency::find($business_location->currency_id);

            $purchase = Transaction::where('business_id', $business_id)
                                    ->where('id', $id)
                                    ->with(
                                        'contact',
                                        'purchase_lines',
                                        'purchase_lines.product',
                                        'purchase_lines.variations',
                                        'purchase_lines.variations.product_variation',
                                        'location',
                                        'payment_lines'
                                    )
                                    ->first();
            $payment_methods = $this->productUtil->payment_types(null, false, $business_id);

            //Purchase orders
            $purchase_order_nos = '';
            $purchase_order_dates = '';
            if (!empty($purchase->purchase_order_ids)) {
                $purchase_orders = Transaction::find($purchase->purchase_order_ids);

                $purchase_order_nos = implode(', ', $purchase_orders->pluck('ref_no')->toArray());
                $order_dates = [];
                foreach ($purchase_orders as $purchase_order) {
                    $order_dates[] = $this->transactionUtil->format_date($purchase_order->transaction_date, true);
                }
                $purchase_order_dates = implode(', ', $order_dates);
            }
            
            $purchase_taxes = [];
            if (!empty($purchase->tax)) {
                if ($purchase->tax->is_tax_group) {
                    $purchase_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($purchase->tax, $purchase->tax_amount));
                } else {
                    $purchase_taxes[$purchase->tax->name] = $purchase->tax_amount;
                }
            }

            $output = ['success' => 1, 'receipt' => [], 'print_title' => $purchase->ref_no];
            $output['receipt']['html_content'] = view('purchase.partials.show_details', compact('taxes', 'purchase', 'payment_methods', 'purchase_order_nos', 'purchase_order_dates', 'currency_details', 'purchase_taxes'))->render();
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        return $output;
    }

    /**
     * Update purchase status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request)
    {
        if (!auth()->user()->can('purchase.update') && !auth()->user()->can('purchase.update_status')) {
            abort(403, 'Unauthorized action.');
        }
        //Check if the transaction can be edited or not.
        $edit_days = request()->session()->get('business.transaction_edit_days');
        if (!$this->transactionUtil->canBeEdited($request->input('purchase_id'), $edit_days)) {
            return ['success' => 0,
                    'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days])];
        }

        try {
            $business_id = request()->session()->get('user.business_id');

            $transaction = Transaction::where('business_id', $business_id)
                                ->where('type', 'purchase')
                                ->with(['purchase_lines'])
                                ->findOrFail($request->input('purchase_id'));

            $before_status = $transaction->status;
            

            $update_data['status'] = $request->input('status');


            DB::beginTransaction();

            //update transaction
            $transaction->update($update_data);

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
            foreach ($transaction->purchase_lines as $purchase_line) {
                $this->productUtil->updateProductStock($before_status, $transaction, $purchase_line->product_id, $purchase_line->variation_id, $purchase_line->quantity, $purchase_line->quantity, $currency_details);
            }

            //Update mapping of purchase & Sell.
            $this->transactionUtil->adjustMappingPurchaseSellAfterEditingPurchase($before_status, $transaction, null);

            //Adjust stock over selling if found
            $this->productUtil->adjustStockOverSelling($transaction);

            DB::commit();

            $output = ['success' => 1,
                            'msg' => __('purchase.purchase_update_success')
                        ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => $e->getMessage()
                        ];
        }

        return $output;
    }
    public function addRequest()
    {
        // if (!auth()->user()->can('purchase.create')) {
        //     abort(403, 'Unauthorized action.');
        // }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

        $taxes = TaxRate::where('business_id', $business_id)
                        ->ExcludeForTaxGroup()
                        ->get();
        $orderStatuses = $this->productUtil->orderStatuses();
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

        $default_purchase_status = null;
        if (request()->session()->get('business.enable_purchase_status') != 1) {
            $default_purchase_status = 'received';
        }

        $types = [];
        // if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        // }
        // if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        // }
        // if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        // }
        $customer_groups = CustomerGroup::forDropdown($business_id);
        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);
        $business_details = $this->businessUtil->getDetails($business_id);
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);

        $payment_line = $this->dummyPaymentLine;
        $payment_types = $this->productUtil->payment_types(null, true, $business_id);

        //Accounts
        $accounts = $this->moduleUtil->accountsDropdown($business_id, true);

        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
        return view('sell.request.create')
            ->with(compact('walk_in_customer','taxes', 'orderStatuses', 'business_locations', 'currency_details', 'default_purchase_status', 'customer_groups', 'types', 'shortcuts', 'payment_line', 'payment_types', 'accounts', 'bl_attributes', 'common_settings'));
    }
}
