<?php
Header("content-type: application/x-javascript");
require_once("../inc/config_settings.inc.php");
$langCode = $_GET['lang'];
include_once('../inc/language_controller.php');
?>
//<script>
//Dinner
function toggleDinner(obj) {
    var $input = $(obj);
    if ($input.prop('checked'))
    {
        $('.csDescription').show();
        $('.onoffswitch-checkbox').prop('checked', true);
    }
else $('.csDescription').hide();
}
$(document).ready(function(){
    $('.double-scroll').doubleScroll();
	
	var winHeight = $('.header').height();
	$('.normal-content').css('margin-top',winHeight);
	$('.splash-container').css('margin-top',winHeight);
	// for the window resize
	$(window).resize(function() {
		var winHeight = $('.header').height();
		$('.normal-content').css('margin-top',winHeight);		
		$('.splash-container').css('margin-top',winHeight);
	});

});

// ---------- Homepage
$("#btn-costs").click(function(){
    $(this).toggleClass('selected');
});
$("#btn-dinnerplanner").click(function(){
    $(this).toggleClass('selected');
});
$("#btn-tasklist").click(function(){
    $(this).toggleClass('selected');
});
// ---------- Feedback
$("#feedback a").click(function(){
    if($('#slidebox').css('right') == "0px")
    {
        $('#slidebox').stop(true).animate({'right':'-250px'},300);
    }
    else
    {
        $('#slidebox').stop(true).animate({'right':'0px'},300);
    }
});

$("#cancel-feedback").bind('click',function(){
    $('#slidebox').stop(true).animate({'right':'-250px'},300);
    return false;
});

$("#send-feedback").click(function() {
    // validate and process form here
    $('.error').hide();
    var email = $("input#feedback-email").val().trim();
    if (email == "") {
        $("input#feedback-email").css('border-color','#ee1111')
        $("input#feedback-email").focus();
        return false;
    }
    var message = $("textarea#feedback-message").val().trim();
    if (message == "") {
        $("textarea#feedback-message").css('border-color','#ee1111')
        $("textarea#feedback-message").focus();
        return false;
    }
    var dataString = 'email='+ email + '&message=' + message + '&path=' + window.location.pathname;
    //alert (dataString);return false;
    $.ajax({
        type: "POST",
        url: "<?php echo $settings['site_url']; ?>ajax/send_feedback.php",
        data: dataString,
        success: function(result) {
            if (result == 'true') {
                $('#feedback-form').html("<div id='message'></div>");
                $('#message').html("<h2><?php echo $lang['FEEDBACK_SEND']; ?></h2>")
                    .append("<p><?php echo $lang['THANKS_FEEDBACK']?></p>");
                setTimeout(function(){ $('#slidebox').stop(true).animate({'right':'-384px'},300)}, 3000);
            } else {
                alert("<?php echo $lang['FEEDBACK_NOT_SEND']?>");
            }
        },
        error: function() {
            alert("<?php echo $lang['FEEDBACK_WRONG']?>");
        }
    });
    return false;
});

//Dinner time
$(".dinnertime").click(function() {
    if($(this).find('span').is(':visible')){
        var time = $(this).find('span').text();
        $(this).find('input').toggle();
        $(this).find('span').toggle();
        if($(this).find('input').is(':visible')){
            $(this).find('input').focus();
            $(this).find('input').val(time);
        }
    }
    else{
        $(this).find('input').focus();
    }
});

$('.dinnertime input.replace').keyup(function(e){
    if(e.keyCode == 13)
    {
        $(this).toggle();
		var span = $(this).parents("td").find('span');
		span.toggle();
		var temp = span.text();
		span.text($(this).val());

		var tijd = $(this).val();
		var datum = $(this).attr('name');
		var gid = span.attr('name');
		var dataString = 'time='+ tijd + '&date=' + datum + '&gid=' + gid;
		$.ajax({
			type: "POST",
			url: "<?php echo $settings['site_url']; ?>ajax/set_dinner-time.php",
			data: dataString,
			success: function(result) {
				if (result == 'true') {
					span.text(tijd);
				}
				else{
					span.text(temp);
				}
			},
			error: function() {
				span.text(temp);
			}
		});
		return false;
    }
});

$(".dinnertime").dblclick(function(){
    $(this).find('input').toggle();
    $(this).find('span').toggle();
    var temp = $(this).find('span').text();
    $(this).find('span').text($(this).find('input').val());

    var tijd = $(this).find('input').val();
    var datum = $(this).find('input').attr('name');
    var gid = $(this).find('span').attr('name');
    var dataString = 'time='+ tijd + '&date=' + datum + '&gid=' + gid;
    $.ajax({
        type: "POST",
        url: "<?php echo $settings['site_url']; ?>ajax/set_dinner-time.php",
        data: dataString,
        success: function(result) {
            // alert(result);
            if (result == 'true') {
                $(this).find('span').text(tijd);
            }
            else{
                $(this).find('span').text(temp);
            }
        },
        error: function() {
            $(this).find('span').text(temp);
        }
    });
    return false;
});

/*$(".done").click(function(){
    var data = $(this).find('a').attr('name');
    var dataString = 'data='+ data;
    $.ajax({
        type: "POST",
        url: "<?php //echo $settings['site_url']; ?>ajax/set_task.php",
        data: dataString,
        success: function(result) {
            if (result == 'true') {
                $(this).attr('done', 'not-done');
            }
        },
        error: function() {

        }
    });
    return false;
});

$(".not-done").click(function(){
    var data = $(this).find('span').attr('name');
    var dataString = 'data='+ data;
    $.ajax({
        type: "POST",
        url: "<?php //echo $settings['site_url']; ?>ajax/set_task.php",
        data: dataString,
        success: function(result) {
            if (result == 'true') {
                $(this).removeClass('not-done').addClass('done');
            }
            else{
                $(this).removeClass('not-done').addClass('done');
            }
        },
        error: function() {
            $(this).removeClass('not-done').addClass('done');
        }
    });
    return false;
}); */


//Dinner
$(document).ready(function(){
    $( ".dinnerDate" ).change(function() {
        $('#uid').val($( ".dinnerName").val());
        this.form.submit();
    });
    $( ".dinnerName" ).change(function() {
        $('#uid').val($( ".dinnerName").val());
        this.form.submit();
    });

    $(".dinnerClick").click(function(event) {
        // validate and process form here
        event.preventDefault();
        event.stopPropagation();
        var clicked = $(this);
        var classesBackup = clicked.attr("class")
        clicked.removeClass().addClass('loadingimg').addClass('dinnerClick');

        var data = JSON.parse(clicked.attr("name").replace(/&quot;/ig,'"'));

        var dataString = 'gid='+ data.gid + '&uid=' + data.uid + '&date=' +data.date+'&role='+data.role;
        if(data.hasOwnProperty('nopersons'))
        {
            dataString += '&persons='+data.nopersons;
        }
        //alert (dataString);return false;
        $.ajax({
            type: "POST",
            url: "<?php echo $settings['site_url']; ?>ajax/set_dinner.php",
            data: dataString,
            success: function(result) {
                var resultData = JSON.parse(result);
                if ((resultData.role >= -1 && resultData.role <= 2) || resultData.role == "X") {
                    $('#dinnerDate').val(resultData.dateDinner);
                    //$('#dinnerName').val(resultData.userName);
                    $('#dinnerName').val(resultData.userId);
                    $('#uid').val(resultData.userId);
                    $('#nrOfPersons').val(resultData.persons);
                    $('#howIsChef').text(resultData.whoCooksString);
                    $('#inputDescription').val('');
                    switch(resultData.role)
                    {
                        case -1:
                            data.role = "1";
                            delete data.nopersons;
                            clicked.removeClass().addClass('nothingimg').addClass('dinnerClick').attr('name',JSON.stringify(data)).html("&nbsp;");
                            $('#dinnerSwitch').prop('checked', false);
                            $('#chefSwitch').prop('checked', false);
                            $('.csDescription').hide();
                            break;
                        case "0":
                            data.role = "-1";
                            delete data.nopersons;
                            clicked.removeClass().addClass('noeatimg').addClass('dinnerClick').attr('name',JSON.stringify(data)).html("&nbsp;");
                            $('#dinnerSwitch').prop('checked', false);
                            $('#chefSwitch').prop('checked', false);
                            $('.csDescription').hide();
                            break;
                        case "1":
                            data.role = "2";
                            data.nopersons=resultData.persons;
                            clicked.removeClass().addClass('eatimg').addClass('dinnerClick').attr('name',JSON.stringify(data)).html("&nbsp;");
                            $('#dinnerSwitch').prop('checked', true);
                            $('#chefSwitch').prop('checked', false);
                            $('.csDescription').hide();
                            break;
                        case "2":
                            data.role = "0";
                            delete data.nopersons;
                            var noPersons = "&nbsp;";
                            if(resultData.persons > 1)
                            {
                                noPersons = resultData.persons+"x";
                            }
                            clicked.removeClass().addClass('chefimg').addClass('dinnerClick').attr('name',JSON.stringify(data)).html(noPersons);
                            $('#dinnerSwitch').prop('checked', true);
                            $('#chefSwitch').prop('checked', true);
                            $('.csDescription').show();
                            break;
                        case "X":
                            clicked.attr('class', classesBackup);
                            break;
                        default:
                            clicked.attr('class', classesBackup);
                            break;
                    }
                } else {
                    clicked.attr('class', classesBackup);
                }
            },
            error: function() {
                clicked.attr('class', classesBackup);
                alert("Error");
            }
        });
        return false;
    });
});
/*
case 0:   role:-1 | noeatimg
case -1:  role:1  | nothingimg
case 1:   role:2  | eatimg
case 2:   role:0  | chefimg
*/


$( document ).ready(function() {
    var ogColor = $("#s").css("border-left-color");
    $("#l").click(function(){
        var inpt = $("#s");
        var delay = 1000;

        inpt.animate({ borderColor: "red", borderWidth: "3px" }, delay,function(){
            //revert after completing
            inpt.animate({ borderColor: ogColor }, delay);
        });
    });

    $(document).click(function(e) {
        var target = e.target;
        if (!$(target).is('nav') && !$(target).parents().is('nav')) {
            if(!$(this).is(":hidden"))
            {
                $('#subSelect').slideUp('slow');
            }

        }
    });

});
var linkGroup = "group";

function dropdown(obj)
{
    if(obj == null)
    {
        linkGroup = "group";
    }
    else
    {
        linkGroup = obj;
    }
    $('#subSelect').slideToggle('slow');
}

function goToGroup(groupId) {
    window.location.href = '<?php echo $settings['site_url']; ?>'+linkGroup+'/'+groupId;
}

$("a#toggle-stickynote").click(function(){
    $("#add-stickynote").slideToggle("slow");
});


//AddCost -1 +1
$("#btn-everyone-1").click( function() {
    $(".who-pays :input").each(function(){
        var context = $(this);
        context.val('1');
    });
});
$("#btn-everyone-0").click( function() {
    $(".who-pays :input").each(function(){
        var context = $(this);
        context.val('0');
    });
});