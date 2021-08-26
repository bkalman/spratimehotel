function addBill(s) {
    $('#bills').append(`<div class="form-row"><div class="form-group col-sm-8"><label for="bill" class="labelUp">Számla</label><input type="file" name="bill[${s}]" id="bill" class="form-control-file" style="font-size: 15px;margin-top:4px"></div><div class="form-group col-10 col-sm-3"><label for="price" class="labelUp">Ár</label><input type="number" name="price[${s}]" id="price" class="form-control"></div><div class="form-group col-2 col-sm-1"><svg id="newBill" xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-plus-circle" viewBox="-4 -4 25 25"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/></svg></div></div>`);
}
function arr_diff (a1, a2) {

    let a = [], diff = [];

    for (let i = 0; i < a1.length; i++) {
        a[a1[i]] = true;
    }

    for (let i = 0; i < a2.length; i++) {
        if (a[a2[i]]) {
            delete a[a2[i]];
        } else {
            a[a2[i]] = true;
        }
    }

    for (let k in a) {
        diff.push(k);
    }

    return diff;
}

$(document).ready(function(){
    $(document).on('click','#add_button',function(){
        $('#errorReportsModal').modal('show');
        $('#errorReports_form')[0].reset();
        $('.modal-title').text("Hibabejelentés");
        $('#action').val("Bejelentés");
        $('#operation').val("Bejelentés");

        $('#errorReports_form .form-group > input + label').removeClass('labelUp labelColor');
    });


    let dataTable = $('#errorReports_data').DataTable({
        "processing":true,
        "serverSide":true,
        "order":[],
        "ajax":{
            url:"index.php?controller=errorReports&action=fetch",
            type:"POST"
        },
        "columnDefs":[
            {
                "targets":[4,6],
                "orderable":false,
            },
        ],

    });

    $(document).on('submit', '#errorReports_form', function(event){
        event.preventDefault();

        let emptyBoolean = true;


        if ($('#room_id').val() != '') {
            emptyBoolean = true;
        } else {
            if ($('#report').val() != ''){
                emptyBoolean = true;
            } else {
                emptyBoolean = false;
                alert('Töltse ki legalább az egyik mezőt!');
            }
        }

        if (emptyBoolean == true) {
            $.ajax({
                url:"index.php?controller=errorReports&action=insert",
                method:'POST',
                data:new FormData(this),
                contentType:false,
                processData:false,
                success:function()
                {
                    $('#errorReports_form')[0].reset();
                    $('#errorReportsModal').modal('hide');
                    dataTable.ajax.reload();
                }
            });
        }
    });

    $(document).on('click', '.update', function(){
        $('#errorReportsModal').modal('show');
        $('#errorReports_form .form-group input, #errorReports_form .form-group select').attr('class','form-control');

        $('#errorReports_form .form-group > label').removeClass('labelColor');
        $('#errorReports_form .form-group > label').attr('class','labelUp');


        $('.modal-title').text('Foglalás szerkesztése');

        $.ajax({
            url:"index.php?controller=errorReports&action=fetchSingle",
            method:"POST",
            data:{
                report_id:$(this).attr('id'),
            },
            dataType:"json",
            success:function(data)
            {
                $('#errorReportsModal').modal('show');

                $('#room_id').val(data.room_id);
                $('#place').val(data.place);
                $('#storey').val(data.storey);
                $('#status').val(data.status);
                $('#report').val(data.report);
                $('#report_id').val(data.report_id);

                $('#action').val("Változtat");
                $('#operation').val("Változtat");
            }
        })
    });

    $(document).on('click', '.finished', function(){
        let report_id = $(this).attr("id");
        $.ajax({
            url:"index.php?controller=errorReports&action=finished",
            method:"POST",
            data:{report_id:report_id},
            success:function()
            {
                dataTable.ajax.reload();
            }
        });
    });


    $(document).on('click','.diary-insert',function(){
        $('#diary_form')[0].reset();
        $('.modal-title').text("Naplózás");
        $('#action_diary').val("Hozzáad");
        $('#operation_diary').val("Hozzáad");
        $('#bills > *').remove();
        addBill(1);

        $.ajax({
            url:"index.php?controller=errorReports&action=fetchSingleDate",
            method:"POST",
            data:{
                report_id:$(this).attr('id'),
            },
            dataType:"json",
            success:function(data)
            {
                $('#diaryModal').modal('show');

                $('#started').val(data.started);
                $('#report_id_diary').val(data.report_id);
            }
        })
    });

    $(document).on('click','.diary-update',function(){
        $('#diary_form')[0].reset();
        $('.modal-title').text("Naplózás");
        $('#action_diary').val("Szerkesztés");
        $('#operation_diary').val("Szerkesztés");
        $('#comment').siblings('label').attr('class','labelUp');
        $('#bills > *').remove();

        $.ajax({
            url:"index.php?controller=errorReports&action=fetchSingleDiary",
            method:"POST",
            data:{
                report_id:$(this).attr("id")
            },
            dataType:"json",
            success:function(data)
            {
                $('#diaryModal').modal('show');

                $('#report_id').val(data.report_id);
                $('#employee_id').val(data.employee_id);
                $('#started').val(data.started);
                $('#finished').val(data.finished);
                $('#comment').val(data.comment);

                let price = 0;

                data.bills.forEach(e => {
                    price += parseInt(e.price);

                    $('#bills').append(`
                        <div class="form-row">
                            <div class="form-group col-sm-8">
                                <label for="bill" class="labelUp">Számla</label>
                                <img id="${e.bill}" src="./src/app/view/errorReports/img/${e.bill}" alt="${e.bill.split('.')[0]}" title="${e.bill.split('.')[0]}" class="img-fluid" style="width:100px;">
                            </div>
                            <div class="form-group col-10 col-sm-3">
                                <label for="price" class="labelUp">Ár</label>
                                <input type="number" name="price[${e.bill.split('-')[2]}]" id="price" class="form-control" value="${e.price}">
                            </div>
                            <div class="form-group col-2 col-sm-1">
                                <svg id="deleteBill" xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-x-circle" viewBox="-4 -4 25 25"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
                            </div>
                        </div>`);
                });
                let dbBills = [];
                document.querySelectorAll('#bills img').forEach(e => {
                    dbBills[dbBills.length] = e.id.split('-')[2];
                });

                let s = Math.max(...dbBills)+1;

                addBill(Number.isInteger(s) ? s : 1);
                $('#price_all').val(`Összesen: ${price} Ft`);

                $('#report_id_diary').val(data.report_id);
            }
        });
    });

    $(document).on('click','#deleteBill',function() {
        $(this).parent().parent().remove();
    });

    $(document).on('click','#newBill',function() {
        let bills = [];
        document.querySelectorAll('#bills img').forEach(e => {
            bills[bills.length] = e.id.split('-')[2];
        });
        document.querySelectorAll('#bill').forEach(e => {
            bills[bills.length] = e.name.split('[')[1].replace(']','');
        });

        let s = Math.max(...bills)+1;

        $('#newBill').parent().html(`<svg id="deleteBill" xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-x-circle" viewBox="-4 -4 25 25"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>`);
        $('#bills').append(`
            <div class="form-row">
                <div class="form-group col-sm-8">
                    <label for="bill" class="labelUp">Számla</label>
                    <input type="file" name="bill[${s}]" id="bill" class="form-control-file" style="font-size: 15px;margin-top:4px">
                </div>
                <div class="form-group col-10 col-sm-3">
                    <label for="price" class="labelUp">Ár</label>
                    <input type="number" name="price[${s}]" id="price" class="form-control">
                </div>
                <div class="form-group col-2 col-sm-1">
                    <svg id="newBill" xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-plus-circle" viewBox="-4 -4 25 25">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                    </svg>
                </div>
            </div>`);
    });

    $(document).on('change','#price',function() {
        let price = 0;
        document.querySelectorAll('#price').forEach(e => {
            price += parseInt(e.value == '' ? 0 : e.value);
        });
        $('#price_all').val(`Összesen: ${price} Ft`);
    });

    $(document).on('submit', '#diary_form', function(event){
        event.preventDefault();

        let emptyBoolean = true;


        if (emptyBoolean == true) {
            if ($('#operation_diary').val() == 'Hozzáad') {

                $.ajax({
                    url: "index.php?controller=errorReports&action=insertDiary",
                    method: 'POST',
                    data: new FormData(this),
                    contentType: false,
                    processData: false,
                    success: function () {
                        $('#diary_form')[0].reset();
                        $('#diaryModal').modal('hide');
                        dataTable.ajax.reload();
                    }
                });

            } else if ($('#operation_diary').val() == 'Szerkesztés') {

                let dbBills = ['bill-0-0'];
                document.querySelectorAll('#bills img').forEach(e => {
                    dbBills[dbBills.length] = e.id;
                });

                let prices = [];
                document.querySelectorAll('#bills #price').forEach(e => {
                    prices[prices.length] = e.value;
                });

                let priceValid = true;

                document.querySelectorAll('#bills > .form-row').forEach(e => {
                    if (e.children[0].children[1].tagName == 'INPUT') {
                        if (e.children[0].children[1].value != '') {
                            if (e.children[1].children[1].value == '') {
                                console.log($('input#price[name="'+e.children[1].children[1].name+'"]').addClass('border border-danger'));
                                priceValid = false;
                            }
                        }
                    } else if (e.children[0].children[1].tagName == 'IMG') {
                        if (e.children[1].children[1].value == '') {
                            console.log($('input#price[name="'+e.children[1].children[1].name+'"]').addClass('border border-danger'));
                            priceValid = false;
                        }
                    }
                });

                if (priceValid == true) {
                    $.ajax({
                        url: "index.php?controller=errorReports&action=insertDiary",
                        method: 'POST',
                        data: {
                            report_id:$('#report_id').val(),
                            operation_diary:'Törlés',
                            bills: dbBills == [] ? [0] : dbBills,
                            prices: prices == [] ? [0] : prices,
                        }
                    });
                    $.ajax({
                        url: "index.php?controller=errorReports&action=insertDiary",
                        method: 'POST',
                        data: new FormData(this),
                        contentType: false,
                        processData: false,
                        success: function () {
                            $('#diary_form')[0].reset();
                            $('#diaryModal').modal('hide');
                            dataTable.ajax.reload();
                        }
                    });
                }

            }
        }
    });

    ['room_id','report','comment'].forEach(e => {
        let input = '#'+e;
        let label = 'label[for='+e+']';

        $(document).on('focus',input,function (){
            if($(input).val() == '') $(label).addClass('labelUp labelColor');
        });
        $(document).on('blur',input,function (){
            if($(input).val() == '') {
                $(label).removeClass('labelUp labelColor');
            } else {
                $(label).removeClass('labelColor');
                $(label).addClass('labelUp');
            }
        });
    });
});