(function( $ ) {
	'use strict';


	$(function(){

	    var selectedHandleFieldAmount = 0;
        var currentFormID = 0;

       $(".nfcformselector").on('change', function() {
       		//not a global variable
       		var formid  = $('input[name=selectfid]:checked').val();;
       		var spinner = $(".mdl-spinner.retrievefieldspinner");
       		var resultdiv = $('#formfieldresult');
       		var sformdiv = $('#selectedformtitle');


       		var data = {
                action: 'nfc_get_fields',
                fid: formid,
                security: NF_CLEANUP.security
            };
       		sformdiv.hide(100);
            resultdiv.hide(150);
       		spinner.show();

       		$.ajax({
       			url: ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: data,
				success: function(response) {
       			    //console.log(response);
                    var html = "";
                    var pf = 0;

                    $.each(JSON.parse(response['data']), function( index, obj ){
                        var type = obj['fieldtype'];
                        var key = obj['fieldkey'];
                        var name = obj['fieldlabel'];
                        var id = obj['fieldid'];
                        pf = obj['parent_form'];

                        html = html+wrapFields(type, key, name, id);
                    });
                    currentFormID = pf;

                    resultdiv.html(html);
                    updateChips();
                    checkVisibilityOptions();
                    resultdiv.show(150);
                    sformdiv.find("b").text(pf);
                    sformdiv.show(100);
                    componentHandler.upgradeDom();

                    spinner.hide();
				},
				error: function(error) {
       				console.log("error");
				}

			});

       });

        $("#savenewhandler").on('click', function(){
            var checkedids = [];
            var checkedfields = getCheckedFields();

            $.each(checkedfields, function( index, obj ){

                checkedids.push(obj.attr("fid"));
            });

            var newhandlername = $("#newhandlername").val();
            var newhandlerdescription = $("#newhandlerdescription").val();
            var newhandlerinterval = $("#intervalselector").attr("value");

            saveHandler(checkedids, newhandlername, newhandlerdescription, newhandlerinterval);

        });

        function saveHandler(checkedids, newhandlername, newhandlerdescription, newhandlerinterval)
        {
            var spinner = $(".mdl-spinner.savingspinner");
            var resultdiv = $("#addnewhandlerresponse");
            var data = {
                action: 'nfc_save_new_handler',
                handlername: newhandlername,
                handlerdescription: newhandlerdescription,
                handlerinterval: newhandlerinterval,
                checkedids: checkedids,
                security: NF_CLEANUP.security
            };


            spinner.show(125);
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function(response) {
                    //spinner.hide(125);
                    spinner.hide(125);
                    console.log(response);
                    if(response.success == false)
                    {
                        //error
                        resultdiv.html(displayError("Error!", response.data))

                    }
                    else
                    {
                        //success
                        resultdiv.html(displaySuccess("Added!", response.data))
                        refreshHandlerCards();
                    }


                },
                error: function(error) {
                    spinner.hide(125);
                    resultdiv.html(displayError("Error!", "An unknown error occurred"))
                }

            });

        }
        //nfc_bulk_remove_dupes

        $(".delalldupes").click(function(){
            var spinner = $(".mdl-spinner.reloadsubmissiondata");
            spinner.show(125);
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'text',
                data: {action: 'nfc_bulk_remove_dupes', security: NF_CLEANUP.security},
                success: function (response) {
                    spinner.hide(125);
                    reloadDuplicates();
                },
                error: function (error) {
                    spinner.hide(125);
                    resultdiv.html(displayError("Error!", "An unknown error occurred"))
                }
            });
            componentHandler.upgradeDom();
        });

        function refreshHandlerCards()
        {
            var spinner = $(".mdl-spinner.fetchhandlersspinner");
            var resultdiv = $("#savedhandles");
            spinner.show(125);
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'text',
                data: {action: 'nfc_fetch_handlers', security: NF_CLEANUP.security},
                success: function (response) {
                    spinner.hide(125);
                    resultdiv.html(response)
                },
                error: function (error) {
                    spinner.hide(125);
                    resultdiv.html(displayError("Error!", "An unknown error occurred"))
                }
            });
            componentHandler.upgradeDom();
        }

        $(".runhandlers").on('click', '.delsubbtn', function(){
            var sid = $(this).attr("pid");
            deleteSubmission(sid)

        });

        function reloadDuplicates()
        {
            var resultdiv = $(".runhandlers");

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'text',
                data: {action: 'nfc_load_duplicate_subs', security: NF_CLEANUP.security},
                success: function (response) {

                    resultdiv.hide(150);
                    resultdiv.html(response);
                    resultdiv.show(150);
                }
            });
            componentHandler.upgradeDom();
        }

        function deleteSubmission(sid)
        {
            $(".reloadsubmissiondata").show(150);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {action: 'nfc_submission_delete', security: NF_CLEANUP.security, sid: sid},
                success: function (response) {

                    if(response.success == false)
                    {
                        //error
                        $('.runhandlers').html(displayError("Error!", response.data)+$('.runhandlers').html());
                    }
                    else
                    {
                        //success
                        //modal.find('b').text(data[2]);

                        //var data = $.parseJSON(response.data);
                        reloadDuplicates();

                        $(".reloadsubmissiondata").hide(100);



                    }
                },
                error: function (error) {
                    $('.runhandlers').html(displayError("Error!", "An unknown error occurred!")+$('.runhandlers').html());
                }
            });

        }


        $(".stackedpane#savedhandles").on('click', '.deletehandlerbtn', function(){
            var handlerid = $(this).attr("value");
            var modal = $('dialog.confirmhandledelete');
            var modalwrapper = modal.find('div.dialogwrapper');
            var spinner = $('.confirmhandledeletespinner');
            spinner.show();
            modalwrapper.hide();

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {action: 'nfc_confirm_handler_deletion', security: NF_CLEANUP.security, hid: handlerid},
                success: function (response) {

                    if(response.success == false)
                    {
                        //error
                        $('#savedhandles').html(displayError("Error!", response.data)+$('#savedhandles').html());
                    }
                    else
                    {
                        //success
                        //modal.find('b').text(data[2]);

                        var data = $.parseJSON(response.data);
                        var name = data.HandlerName;
                        var id = data.HandlerID;
                        modal.find('b').text(name);
                        modal.find('.btndeletehandler').attr("value", id);
                        spinner.hide(100);
                        modalwrapper.show(150);


                    }
                },
                error: function (error) {
                    $('#savedhandles').html(displayError("Error!", "An unknown error occurred")+$('#savedhandles').html());
                }
            });
            console.log("test"+handlerid);
        });

        $("#formfieldresult").on('change', 'label input', function(){


            updateChips();
            checkVisibilityOptions();

        });

        $(".intervalselectoritem").on('click', function(){
            var prefix = "Current interval: ";
            var button = $('#intervalselector');
            var value = $(this).attr("value");
            var slug = $(this).text();

            button.text(prefix+slug);
            button.attr("value", value);
        });


        $("#fieldchips").on("click", "span a", function(){
            var fieldid = $(this).attr("value");
            $('#'+fieldid).parent().removeClass("is-checked");
            updateChips();
            checkVisibilityOptions();

        });


        function checkVisibilityOptions()
        {
            if(selectedHandleFieldAmount >= 1)
            {
                $('#saveoptions').show(100);
                componentHandler.upgradeDom();
            }
            else
            {
                $('#saveoptions').hide(100);
            }
        }

        function updateChips()
        {
            var checkedfields = getCheckedFields();
            var setChips = $('#fieldchips');
            var html = "";

            $.each(checkedfields, function(index, field){
                html = html + wrapChip(field.attr("fid"), field.attr("name"));
            });

            setChips.html(html);
        }
        function getCheckedFields()
        {
            var sum = 0;
            var checkedFields = [];
            $(".fieldhandlecheckbox").each(function()
            {
                if($(this).hasClass("is-checked"))
                {
                    sum += 1;
                    checkedFields.push($(this));
                }
            });
            selectedHandleFieldAmount = sum;
            return checkedFields;
        }

        $('dialog.confirmhandledelete').on('click', '.btndeletehandler', function(){
            var hid = $(this).attr('value');
            var modal = $('dialog.confirmhandledelete');
            var modalwrapper = modal.find('div.dialogwrapper');
            var modalbody = modal.find('div.modalcontent');
            var spinner = $('.deletehandlerspinner');
            //alert("HID: " + hid);
            spinner.show(125);
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {action: 'nfc_final_delete_handler', security: NF_CLEANUP.security, hid: hid},
                success: function (response) {
                    if(response.success == false)
                    {
                        //error
                        spinner.hide(125);
                        modalbody.html(displayError("Error!", response.data)+modalbody.html());
                    }
                    else
                    {
                        //success
                        //modal.find('b').text(data[2]);
                        spinner.hide(125);
                        var data = $.parseJSON(response.data);

                        modalbody.html(displaySuccess("Success!", "Successfully removed: <b>"+data.HandlerName+"</b>"));
                        refreshHandlerCards();
                        $('.btndeletehandler').hide(125);
                        $('.closemodalbtn').text("Close");


                    }
                },
                error: function (error) {
                    $('#savedhandles').html(displayError("Error!", "An unknown error occurred")+$('#savedhandles').html())
                }
            });


        });

        $(document).ready(function(){
            var dialog = document.querySelector('dialog.confirmhandledelete');
            var showDialogButton = document.querySelectorAll('.deletehandlerbtn');
            if (! dialog.showModal) {
                dialogPolyfill.registerDialog(dialog);
            }
            /*
             showDialogButton.addEventListener('click', function() {
             dialog.showModal();
             })*/

            for (var i = 0; i != showDialogButton.length; i++) {
                showDialogButton[i].addEventListener('click', function() {
                    dialog.showModal();
                });
            }

            dialog.querySelector('.close').addEventListener('click', function() {
                dialog.close();
            });
        });
	});


	function wrapFields(type, key, name, id)
    {
        var prep = '<label for="nfc-field-'+id+'" name="'+name+'" fid="'+id+'" class="mdl-switch mdl-js-switch fieldhandlecheckbox">';
        var prep = prep + '<input type="checkbox" id="nfc-field-'+id+'" class="mdl-switch__input">';
        var prep = prep + '<span class="mdl-switch__label fieldlabel">'+name+' - <i>'+type+'</i></span>';
        var prep = prep + '</label>';

        return prep;
    }

    function wrapChip(id, name)
    {
        var prep = '<span class="mdl-chip mdl-chip--contact mdl-chip--deletable">';
        var prep = prep + '<span class="mdl-chip__text">'+name+'</span>';
        var prep = prep + '<a value="nfc-field-'+id+'" class="mdl-chip__action uncheckfield"><i class="material-icons">cancel</i></a>';
        var prep = prep + '</span>';

        return prep;
    }

    function displayError(prefix, value)
    {
        if(prefix == null)
        {
            var prep = '<div class="custom-alert danger nomargin">'+value+'</div>';
        }
        else
        {
            var prep = '<div class="custom-alert danger nomargin"><b>'+prefix+'</b> '+value+'</div>';
        }
        return prep;
    }

    function displaySuccess(prefix, value)
    {
        if(prefix == null)
        {
            var prep = '<div class="custom-alert success nomargin">'+value+'</div>';
        }
        else
        {
            var prep = '<div class="custom-alert success nomargin"><b>'+prefix+'</b> '+value+'</div>';
        }
        return prep;
    }



})( jQuery );

