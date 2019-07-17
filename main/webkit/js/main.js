$(document).ready(function () {

    //private process
    $('#client-index').keyup(function () {
        var index = $('#client-index').val();
        if (index === ''){
            $('#client-result-display').html('');
        }
        $.ajax({
            url:"/private/findClient",
            method:"post",
            data:{index : index},
            dataType:"text",
            success:function (data) {
                $('#client-result-display').html(data);
            },
            error:function (data) {
                alert(data);
            }
        })
    });


    //admin process
    $('#client-index-a').keyup(function () {
        var index = $('#client-index-a').val();
        if (index === ''){
            $('#client-result-display').html('');
        }
        $.ajax({
            url:"/admin/findClient",
            method:"post",
            data:{index : index},
            dataType:"text",
            success:function (data) {
                $('#client-result-display').html(data);
            },
            error:function (data) {
                //alert(data);
                alert(index);
            }
        })
    });

    $('#expense-index').keyup(function () {
        var index = $('#expense-index').val();
        $.ajax({
            url:"/accounts/findExpense",
            method:"POST",
            data:{index:index},
            dataType:"text",
            success:function (data) {
                console.log("All is good");
                $('#expense-result-display').html(data);
            },
            error:function (data) {
                alert("Failed"+data);
            }
        })
    })

    $('.editc').on('click',function (e) {
        e.preventDefault();
        alert("good");

    });
    $.ajax({
        url: '/private/charts',
        method: "POST",
        success: function (data) {
            console.log(data);
            var obj = JSON.parse(data);
            var loan = {
                date: []
            };

            var paid = obj.paid;
            var exp = obj.expectation;
            var balance = obj.balance;
            loan.date.push(balance,paid);

          //drawing the pie

            var ctx1 = $("#daily-summary-canvas");
            var data1 = {
                labels : ["Balance","Collections"],
                datasets : [{
                    label: "Daily Summary",
                    data : loan.date,
                    backgroundColor: [
                        "#E55451",
                        "#387C44"
                    ],
                    borderColor : [
                        "#CDA776",
                        "#989898"
                    ],
                    borderWidth : [1,1]
                }]
            };

            var chart = new Chart(ctx1,{
                type: "pie",
                data : data1,
            });

        },
        error: function (data) {
           console.log(data);

        }
    })
    $('#repay-form').submit(function (e) {
        var amount = $('#payback').val();
        alert(amount);
    })
    $(document).on('click','.edit_data',function () {
        var user_id = $(this).attr("id");
        //$('#name').val(id);
        //$('#edit-data-modal').modal();

        $.ajax({
            url:'/admin/zone',
            type: "POST",
            data:{user_id:user_id},
            dataType: 'json',
            success: function (data) {
                $("#label1").text("Zone Name: ");
                $("#label2").text("Zone location:");
                $("#label3").text("Zone Chair:");
                $("#label4").text("Cell:");
                $('#name').val(data[1]);
                $('#id').val(data[0]);
                $('#name2').val(data[2]);
                $('#name3').val(data[4]);
                $('#name4').val(data[3]);
                $('#edit-data-modal').modal();
            },
            error:function (results) {
                console.log(results);
                alert(results);
            }

        });

    });
    $('#client-form').submit(function (e) {

        var submit = true;
        var firstname   = $('#name').val();
        var radioValue  = $("input[name='gender']:checked").val();
        var maritalValue= $("input[name='marital']:checked").val();
        var group       = $("#group").val();

        $(".error").remove();
        if (group === "none"){
            $('#group').after('<span class="error"> Please select Client Group </span>');
            submit = false;
        }
        if(!radioValue){
            $('#gender').after('<span class="error"> Please select gender </span>');
            submit = false;
        }
        if (!maritalValue){
            $('#marital').after('<span class="error"> Please select Marital Status </span>');
            submit = false;

        }

        if (submit === false){
            e.preventDefault();
        }

    })
    $('#upload-client-form').submit(function (e) {
        var extension = $('#file').val().split('.').pop().toLowerCase();
        $(".error").remove();

        if (extension !== 'csv'){
            $('#file').after('<span class="error"> The File is not in <b>CSV</b> format </span>');
            e.preventDefault();
        }
    });
    $('#message-form').submit(function (e) {
        var message = $('#message').val();
        var title = $('#title').val();
        var type = $('#type').val();

        $(".error").remove();
        if (title.length < 5){
            $('#title').after('<span class="error"> Title should exceed five characters !! </span>')
            e.preventDefault();
            return;
        }
        if (message.length < 8){
            $('#message').after('<span class="error"> Message should exceed eight characters !! </span>')
            e.preventDefault();
            return;
        }

        if (type > 3 || type < 1){
            $('#type').after('<span class="error">Please specify the type of the message!! </span>')
            e.preventDefault();
            return;
        }

    });

    $('#pchange-form').submit(function (e) {
        var password = $('#pass').val();
        var password2 = $('#pass2').val();

        $(".error").remove();
        if (password.length < 6){
            $('#pass2').after('<span class="error"> Password should contain at least 6 characters </span>');
            e.preventDefault();
            return;
        }
        if (password !== password2){
            $('#pass2').after('<span class="error"> Password Mismatch, please check input </span>');
            e.preventDefault();
            return;
        }
    })
    $('#create-user-form').submit(function (e) {
        var submit = true;
        var password = $('#pass').val();
        var passwor2 = $('#pass2').val();

        $(".error").remove();
        if (password !== passwor2){
            $('#pass2').after('<span class="error"> Passwords Mismatch </span>');
            submit = false;
        }
        if (password.length < 8){
            $('#pass2').after('<span class="error"> Password should contain minimum of 8 characters </span>');
           submit = false;
        }

        if (submit === false){
            e.preventDefault();
        }
    })
    $('.delete').click(function () {
        var el = this;
        var id = this.id;
        var splitid = id.split("_");

        //Delete id
        var deleteid = splitid[1];

        bootbox.confirm("Are You Sure want to delete?",function (result) {
            if (result){
                $.ajax({
                    url:'/admin/administration',
                    type:'POST',
                    data:{id:deleteid},
                    success:function (response) {

                        //Removing row from html table
                        $(el).closest('tr').css('background','tomato');
                        $(el).closest('tr').fadeOut(800,function () {
                            $(this).remove();
                        })
                    }
                })
            }
        })
    })
    $('.delete_holiday').click(function () {
        var el = this;
        var id = this.id;
        var splitid = id.split("_");

        //Delete id
        var deleteid = splitid[1];

        bootbox.confirm("Are You Sure want to delete?"+ deleteid,function (result) {
            if (result){
                $.ajax({
                    url:'/admin/setup',
                    type:'POST',
                    data:{id:deleteid},
                    success:function (response) {

                        //Removing row from html table
                        $(el).closest('tr').css('background','tomato');
                        $(el).closest('tr').fadeOut(800,function () {
                            $(this).remove();
                        })
                    }
                })
            }
        })
    })
})

function botConfirm() {
    $(document).ready(function (e) {
        bootbox.confirm("Proceed ?",function (result) {
            if (!result){
                e.preventDefault();
            }
        });
    })
}

