//Add products


if ($('#search_purchase').length > 0) {
  
    $('#search_purchase')
        .autocomplete({
            source: function(request, response) {
                $.getJSON(
                    '/expenses/get_purchases',
                    { location_id: $('#location_id').val(), term: request.term },
                    response
                );
            },
            minLength: 2,
            response: function(event, ui) {
                if (ui.content.length == 1) {
                    ui.item = ui.content[0];
                    console.log(ui.item);
                    $(this)
                        .data('ui-autocomplete')
                        ._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                } else if (ui.content.length == 0) {
                    var term = $(this).data('ui-autocomplete').term;
                    swal({
                        title: 'No Purcase found',
                        text: 'No Purchase found with saearched term: '+term ,
                        buttons: [LANG.cancel, LANG.ok],
                    })
                }
            },
            select: function(event, ui) {
                $(this).val(null);
                append_purchase_lines(ui.item);
                // get_purchase_entry_row(ui.item);
            },
        })
        .autocomplete('instance')._renderItem = function(ul, item) {
        return $('<li>')
            .append('<div>' + item.text + '</div>')
            .appendTo(ul);
    };
}
// Remove ROw
$(document).on('click', '.remove_purchase_entry_row', function() {
    swal({
        title: LANG.sure,
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    }).then(value => {
        if (value) {
            $(this)
                .closest('tr')
                .remove();
            update_table();
        }
    });
});



function append_purchase_lines(data) {
   
    console.log(data);
    row_count=$('#row_count').val();
    new_count=parseInt(row_count)+1;
    $('#row_count').val(new_count);
    $('#total_quantity').text(new_count);


    var currency=$('#__symbol').val();
    var rate=$('#__rate').val();
    var final_total= data.final_total;
    var foriegn_currency= currency;
    if(currency=="HNL"){
        final_total= (data.final_total/rate).toFixed(2);
        foriegn_currency= '$';
    }
    var after_tax=calAfterTax(data.final_total);

    var row=`<tr>
        <td class="sr_number">
        ${new_count}</td>
        <td><input type="hidden" name="purchases[]" value="${data.id}"> 
        ${data.contact.name}</td>
        <td>${data.text}</td>
        <td >${currency}  <input type="number" step="0.01" name="sub_total[]" class="sub_total" value="${data.final_total}"></td>
        <td class="tax_total" data-tax-total="${after_tax}">${currency} ${after_tax}</td>
        <td >${foriegn_currency} ${final_total}</td>
        <td> <button type="button" class="btn btn-danger btn-sm remove_purchase_entry_row"><i class="fas fa-trash"></i> </td>
        </tr>`;

    $('#purchase_entry_table tbody').append(row);
    updateTotal();
    // final_total=$('#final_total').val();
    // if(final_total){
    //     new_total=parseFloat(final_total)+parseFloat(data.final_total);
    // }else{
    //     new_total=data.final_total;
    // }
    
}

function update_table() {
    row_count=$('#row_count').val();
    new_count=parseInt(row_count)-1;
    $('#row_count').val(new_count);
    $('#total_quantity').text(new_count);
    update_table_sr_number();

}

function update_table_sr_number() {
    var sr_number = 1;
    $('table#purchase_entry_table tbody')
        .find('.sr_number')
        .each(function() {
            $(this).text(sr_number);
            sr_number++;
        });

        final_total=0;
        $('table#purchase_entry_table tbody')
        .find('.sub_total')
        .each(function() {
           total= $(this).val();
            final_total+=parseFloat(total);
        });

        var currency=$('#__symbol').val();
        $('#final_total').val(final_total);
        $('#total_subtotal').text(currency+' '+final_total);
}

$(document).ready(function() {
    $('table#purchase_entry_table tbody').on('change', '.sub_total', function() {
        // Get the current value of the sub_total
        var currentValue = $(this).val();
        updateTotal();
    });
});

function updateTotal() {
    var currency=$('#__symbol').val();
    var total = 0;
    $('table#purchase_entry_table tbody .sub_total').each(function(index) {
        // Add the value of each .sub_total to the total
        total += parseFloat($(this).val()) || 0; // Convert value to float and handle NaN
        var after_tax=calAfterTax($(this).val());
        // console.log($('.tax_total')[index].getAttribute('data-tax-total'));
       $('.tax_total').eq(index).attr('data-tax-total', after_tax);
       $('.tax_total')[index].textContent=currency+' '+after_tax;

    });
    
    $('#final_total').val(total);
    $('#total_subtotal').text(currency+' '+total);

}

function calAfterTax(final_total) {
    var tax_id=$('#tax_id').val();
    var tax_amount=0;
    if(tax_id==5){
        tax_amount=(final_total/100)*15;
    }
    else if(tax_id==7){
        tax_amount=(final_total/100)*18;
    }
    return (parseFloat(final_total)+parseFloat(tax_amount));
}