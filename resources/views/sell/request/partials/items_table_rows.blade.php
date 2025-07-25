@php
    $row_count = 0;
@endphp
@foreach($items as $item)
    @php
        $row_count = $row_count + 1;
    @endphp
    <tr @if(!empty($purchase_order_line)) data-purchase_order_id="{{$purchase_order_line->transaction_id}}" @endif @if(!empty($purchase_requisition_line)) data-purchase_requisition_id="{{$purchase_requisition_line->transaction_id}}" @endif>
        <td><span class="sr_number">{{$row_count}}</span></td>
        <td>
            {{ $item->product->name }} ({{$item->variation->sub_sku}})
            @if( $item->product->type == 'variable' )
                <br/>
                (<b>{{ $item->variation->product_variation->name }}</b> : {{ $item->variation->name }})
            @endif
        </td>
        <td>
            {{ $item->product->weight==null? 'N/A': $item->product->weight}}
        </td>
        <td>
            @if(!empty($purchase_order_line))
                {!! Form::hidden('purchases[' . $row_count . '][purchase_order_line_id]', $purchase_order_line->id ); !!}
            @endif
            @php
                $check_decimal = 'false';
                if($item->product->unit->allow_decimal == 0){
                    $check_decimal = 'true';
                }
                $quantity_value = !empty($purchase_order_line) ? $purchase_order_line->quantity : 1;

                $quantity_value = !empty($purchase_requisition_line) ? $purchase_requisition_line->quantity - $purchase_requisition_line->po_quantity_purchased : $quantity_value;
                $max_quantity = !empty($purchase_order_line) ? $purchase_order_line->quantity - $purchase_order_line->po_quantity_purchased : 0;

                $max_quantity = !empty($purchase_requisition_line) ? $purchase_requisition_line->quantity - $purchase_requisition_line->po_quantity_purchased : $max_quantity;

                $quantity_value = !empty($imported_data) ? $imported_data['quantity'] : $quantity_value;
            @endphp
            <span>{{$item->quantity}}</span>
        </td>
        <td>
            @php
            $getValues = $productUtil->getAvaliableQty($business_id,$item->variation_id,$request->business_location_id);
            $avaliabilityQty=$getValues['avaliabilityQty'];
            @endphp
            {{$avaliabilityQty}}
        </td>
        <td>
            @switch($item->status)
                @case('Rejected')
                    <span class="label bg-red">{{$item->status}}</span>
                @break
                @default()
                    <span class="label bg-info">{{$item->status}}</span>
                @break
            @endswitch
        </td>
        <td>
            <div class="assigned-user-section" data-item-id="{{$item->id}}">
                @if($item->assignedUser)
                    <span class="assigned-user-name">
                        {{ trim($item->assignedUser->first_name . ' ' . $item->assignedUser->last_name) ?: $item->assignedUser->username }}
                    </span>
                    <button class="btn btn-xs btn-default change-assignment" title="Change Assignment">
                        <i class="fa fa-edit"></i>
                    </button>
                @else
                    <button class="btn btn-xs btn-info assign-user" title="Assign User">
                        <i class="fa fa-user-plus"></i> Assign
                    </button>
                @endif
            </div>
        </td>
        <td>
            @switch($item->status)
                @case('Rejected')
                    <a href="{{route('request.item.edit',$item->id)}}" class="btn btn-primary">Edit</a>
                @break
                @case('Pending')
                    <a href="{{route('request.item.edit',$item->id)}}" class="btn btn-primary">Edit</a>
                    <a href="{{route('request.item.reject',$item->id)}}" class="btn btn-danger">Reject</a>
                @break
                @case('stock')
                    <a href="{{route('request.item.edit',$item->id)}}" class="btn btn-primary">Edit</a>
                    <a href="{{route('request.item.reject',$item->id)}}" class="btn btn-danger">Reject</a>
                    break;
                @default()
                    <a href="{{route('request.item.edit',$item->id)}}" class="btn btn-primary">Edit</a>
                @break
            @endswitch
        </td>
        <?php $row_count++ ;?>
    </tr>
@endforeach