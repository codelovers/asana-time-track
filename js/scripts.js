$(function(){

    var apiKeyCookie = $.cookie('asana-api-key');
    var apiKeyForm = $('#api-key-form');
    var apiKeyFormSubmit = apiKeyForm.find('button[type=submit]');
    var apiKeyEditDelete = apiKeyForm.find('#api-key-edit, #api-key-delete, #api-key-refresh');
    var apiKeyInput = apiKeyForm.find('#api-key');
    var workspaceContainer = apiKeyForm.siblings('#workspace-container');
    var apiKeyImg = apiKeyForm.find('.ajax_img');
    var workspace = $('.container').children('#loader-wrapper');
    var workspaceLoader = workspace.find('.ajax_img');
    var workspaceRefresh = workspace.find('#workspace-refresh');
    var modalBackdrop = $('.modal-backdrop');
    var activeWorkspaceId = null;
    modalInit();

    // ##############################################################################################
    // Config Modal
    // ##############################################################################################
    function modalInit(){
        if(apiKeyCookie != null){
            apiKeyFormSubmit.hide();
            apiKeyEditDelete.show();
            apiKeyInput.attr('disabled', '').val(apiKeyCookie);
            workspaceContainer.fadeIn();
            apiKeyImg.fadeIn();
            workspacesAjaxCall();
        }
    }

    function workspacesAjaxCall(){
        $.ajax({
          type: "GET",
          url: "request.php",
          data: "apiKey=" + apiKeyInput.val(),
          timeout: 30000,
          success: function( result ) {
            workspaceContainer.html(result).fadeIn();
            apiKeyImg.fadeOut();
            },
          error : function( msg, time ) {
            if(time === 'timeout'){
                workspaceContainer.html("Timeout, no response from Server. We're sorry...");
            }
            workspaceContainer.html(msg.responseText);
            apiKeyImg.fadeOut();
            }
        });
    }

    // EVENT HANDLERS
    
    // Config Modal-Module from Twitter Bootstrap
    $('#start-modal').modal({
      keyboard: false,
      backdrop: false
    });
    
    // stop user from exiting Modal by clicking aside the Modal
    $('.modal-backdrop').on('click', function(e){
        return false;
    });
    
    // look for the Workspaces of the user
    $('#start-send').on('click', function(){
        if($('#api-key-form')[0].checkValidity()){
            $.cookie('asana-api-key', apiKeyInput.val(), { expires: 7 });
            apiKeyInput.attr('disabled', '');
            apiKeyImg.fadeIn();
            apiKeyFormSubmit.fadeOut();
            workspacesAjaxCall();
            apiKeyEditDelete.show();
        }
        return false;
    });
    
    $('#api-key-delete').on('click', function(){
        $.cookie('asana-api-key', null);
        apiKeyFormSubmit.show();
        apiKeyEditDelete.hide();
        workspaceContainer.hide();
        apiKeyInput.removeAttr('disabled').val('');
    });
    
    $('#api-key-edit').on('click', function(){
        apiKeyFormSubmit.show();
        apiKeyEditDelete.hide();
        workspaceContainer.hide();
        apiKeyInput.removeAttr('disabled').val(apiKeyCookie);
        return false;
    });
    
    $('#api-key-refresh').on('click', function(){
        workspaceContainer.hide();
        apiKeyImg.fadeIn();
        workspacesAjaxCall();
        return false;
    });
    
    // we kinda hacked the Modal of bootstrap -> manual show
    $('#change-workspace').on('click', function(){
        modalBackdrop.show();
    });
    
    $('#start-modal').on('click', '#start-modal > .modal-footer .btn', function (){
        modalBackdrop.hide();
    });
    
    // change workspaces on click (ajaxCall + changing Headlines etc. )
    $('#workspace-container').on('click', '#workspace-container > .workspace', function(){
        var caption = $(this).find('h3').text();
        activeWorkspaceId = $(this).data('workspace-id');
        $('#start-modal').modal('hide');
        $('.workspace_caption').show().attr('data-workspace-id', activeWorkspaceId).html(caption);
        modalBackdrop.fadeOut();
        $('#start-modal').children('.modal-footer').html('<a href="#" class="btn" data-dismiss="modal">Close</a>');
        tasksAjaxCall();
        workspaceLoader.show();
    });
    
    // ##############################################################################################
    // Tasks table & track time
    // ##############################################################################################
    
    function tasksAjaxCall(projectId){
        // clean tbody
        $('#track-table').show().find('tbody').html('<tr><td colspan="6">One moment please, your assigned tasks are loading...</td></tr>');
        
        $.ajax({
          type: "GET",
          url: "request.php",
          data: "apiKey=" + apiKeyInput.val() + "&workspaceId=" + activeWorkspaceId + "&projectId=all",
          timeout: 90000,
          success: function( result ) {
              $('#track-table').show().find('tbody').html(result);
              workspaceLoader.fadeOut();
              workspaceRefresh.fadeIn();
              initTimepicker();
              $('.my_label').tooltip();
            },
          error : function( msg, time ) {
              if(time === 'timeout'){
                  $('#track-table').append("Timeout, no response from Server. We're sorry...");
              }
              $('#track-table').append(msg.responseText);
              workspaceLoader.fadeOut();
              workspaceRefresh.fadeIn();
            }
        });
    }
    
    // updates the Taks with the new data in Asana
    function changeTasksAjaxCall(getTaskId, getEstimatedHours, getEstimatedMinutes, newHours, newMinutes, getTaskName){
        $.ajax({
              type: "GET",
              url: "request.php",
              data: "apiKey=" + apiKeyCookie + "&updateId=" + getTaskId + "&estimatedHours=" + getEstimatedHours + "&estimatedMinutes=" + getEstimatedMinutes + "&workedHours=" + newHours + "&workedMinutes=" + newMinutes + "&taskName=" + getTaskName,
              timeout: 30000,
              success: function( result ) {
                 // update was successful
                 workspaceRefresh.removeClass('disabled');
              },
              error : function( msg, time ) {
                 if(time === 'timeout'){
                     $('#track-table').append("Timeout, no response from Server. We're sorry... Please try it again.");
                 }
                 $('#track-table').append(msg.responseText);
              }
        });
    }
    
    // init the nice jquery-plugin from http://mobiscroll.com/
    function initTimepicker(){
        // custom Hour Wheel with 100h
        var hourWheel = {};
        for (var i=0;i<=100;i++) {
            hourWheel[""+i] = i;
        }
        // custom minutes Wheel with 59m
        var minutesWheel = {};
        for (var i=0;i<60;i++) {
            minutesWheel[""+i] = i;
        }
        
        $('.date-picker-et').scroller({
            theme: 'default',
            display: 'modal',
            mode: 'mixed',
            width: 100,
            wheels: [{ 'Hours': hourWheel, 'Minutes': { '0': 0, '15': 15 , '30' : 30, '45' : 45 }}],
            headerText: false,
            rows: 5,
            onSelect: onScrollerSelect
        });
        
        $('.date-picker-wt').scroller({
            theme: 'default',
            display: 'modal',
            mode: 'mixed',
            width: 100,
            wheels: [{ 'Hours': hourWheel, 'Minutes': minutesWheel }],
            headerText: false,
            rows: 5,
            onSelect: onScrollerSelect
        });
    }
    
    // Callback for onSelect Event of the Timepicker-Module (plugin)
    function onScrollerSelect(valueText){
        
        // set refresh button disabled while request
        workspaceRefresh.addClass('disabled');
        
        var dataEstimatedHours = null;
        var dataEstimatedMinutes = null;
        var dataWorkedHours = null;
        var dataWorkedMinutes = null;
        var taskId = null;
        var taskName = null;
        var sibling = null;
        var parent = null;
        var value = valueText.split(" ");
        
        if($(this).hasClass('date-picker-et')){
            parent = $(this).parents('.estimated_time');
            sibling = parent.siblings('.worked_time');
            
            parent.attr('data-estimated-hours', value[0]);
            parent.attr('data-estimated-minutes', value[1]);
            parent.find('.my_label').html(value[0] + 'h ' + value[1] + 'm');
            
            dataEstimatedHours = value[0];
            dataEstimatedMinutes = value[1];
            dataWorkedHours = sibling.attr('data-worked-hours');
            dataWorkedMinutes = sibling.attr('data-worked-minutes');
            taskId = sibling.attr('data-task-id');
            taskName = sibling.attr('data-task-name');
        } else {
            parent = $(this).parents('.worked_time');
            sibling = parent.siblings('.estimated_time');
            
            parent.attr('data-worked-hours', value[0]);
            parent.attr('data-worked-minutes', value[1]);
            parent.find('.my_label').html(value[0] + 'h ' + value[1] + 'm');
            
            dataEstimatedHours = sibling.attr('data-estimated-hours');
            dataEstimatedMinutes = sibling.attr('data-estimated-minutes');
            dataWorkedHours = value[0];
            dataWorkedMinutes = value[1];
            taskId = parent.attr('data-task-id');
            taskName = parent.attr('data-task-name');
        }

        // Progressbar
        var locateProgressWrapper = parent.siblings('.my_progress').find('.progress');
        var percent = (dataEstimatedHours*60*1000 + dataEstimatedMinutes * 1000) / 100;
        
        if(percent != 0){
            percent = (dataWorkedHours*60*1000 + dataWorkedMinutes * 1000) / percent;
        } else {
            percent = 101;
        }
        
        // change progress state
        if(percent < 80){
            locateProgressWrapper.removeClass('progress-warning', 'progress-danger');
            locateProgressWrapper.addClass('progress-success');
        } else if (percent >= 80 && percent < 100){
            locateProgressWrapper.removeClass('progress-success', 'progress-danger');
            locateProgressWrapper.addClass('progress-warning');
        } else if (percent >= 100){
            locateProgressWrapper.removeClass('progress-success', 'progress-warning');
            locateProgressWrapper.addClass('progress-danger');
        }
        
        locateProgressWrapper.find('.bar').css('width', percent + '%');
        changeTasksAjaxCall(taskId, dataEstimatedHours, dataEstimatedMinutes, dataWorkedHours, dataWorkedMinutes, taskName);
    }
    
    // EVENT HANDLERS
    
    // start & stop the watch
    $("#track-table").on('click', 'td.my_timer > .btn', function(event){
        var locateClickedElement = $(this);
        var locateAllButtons = $('.my_timer .btn');
        
        // no more activity is possible, 
        // only one task can be active
        if(locateClickedElement.hasClass('disabled')) return;
        
        var locateButtonText = locateClickedElement.find('span');
        var locateButtonIcon = locateClickedElement.find('i.icon-white');
        
        if(locateClickedElement.hasClass('btn-success')){
            locateClickedElement.removeClass('btn-success');
            locateClickedElement.addClass('btn-danger');
            locateButtonText.html('Stop');
            locateButtonIcon.removeClass('icon-play').addClass('icon-stop');
            locateAllButtons.addClass('disabled');
            locateClickedElement.removeClass('disabled');
        } else{
            locateClickedElement.removeClass('btn-danger');
            locateClickedElement.addClass('btn-success');
            locateButtonText.html('Start');
            locateButtonIcon.removeClass('icon-stop').addClass('icon-play');
            locateAllButtons.removeClass('disabled');
        }

        // start time tracking
        locateClickedElement.prev('.time').stopwatch().stopwatch('toggle');
        
    });
    
    // refresh the Workspace manual
    workspaceRefresh.on('click', function(){
        
        if(!workspaceRefresh.hasClass('disabled')){
            tasksAjaxCall();
            workspaceRefresh.hide();
            workspaceLoader.delay('200').show();
        } 

    });
    
    $("#track-table").on('click', 'td.estimated_time', function(event){
        // the time is only editable when its not tracked at the moment
        if($(this).siblings('.my_timer').find('button.btn').hasClass('btn-danger')){
            return false;
        }
        var hours = $(this).attr('data-estimated-hours');
        var minutes = $(this).attr('data-estimated-minutes');
        var input = $(this).children('input');
        input.scroller('setValue', [hours, minutes], true);
        input.scroller('show');
    });

    $("#track-table").on('click', 'td.worked_time', function(event){
        // the time is only editable when its not tracked at the moment
        if($(this).siblings('.my_timer').find('button.btn').hasClass('btn-danger')){
            return false;
        }
        var hours = $(this).attr('data-worked-hours');
        var minutes = $(this).attr('data-worked-minutes');
        var input = $(this).children('input');
        input.scroller('setValue', [hours, minutes], true);
        input.scroller('show');
    });
});