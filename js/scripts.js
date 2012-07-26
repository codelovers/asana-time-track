$(function(){
    var apiKeyCookie = $.cookie('asana-api-key');
    var apiKeyForm = $('#api-key-form');
    var apiKeyFormSubmit = apiKeyForm.find('button[type=submit]');
    var apiKeyEditDelete = apiKeyForm.find('#api-key-edit, #api-key-delete, #api-key-refresh');
    var apiKeyInput = apiKeyForm.find('#api-key');
    var workspaceContainer = apiKeyForm.siblings('#workspace-container');
    var apiKeyImg = apiKeyForm.find('.ajax_img');
    var workspaceLoader = $('.container').children('#loader-wrapper').find('.ajax_img');
    var workspaceRefresh = $('.container').children('#loader-wrapper').find('#workspace-refresh');
    var modalBackdrop = $('.modal-backdrop');
    var activeWorkspaceId = null;
    
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
    
    $('#start-modal').modal({
      keyboard: false,
      backdrop: false
    });
    
    $('.modal-backdrop').on('click', function(e){
        return false;
    });
    
    if(apiKeyCookie != null) {
        apiKeyFormSubmit.hide();
        apiKeyEditDelete.show();
        apiKeyInput.attr('disabled', '').val(apiKeyCookie);
        workspaceContainer.fadeIn();
        apiKeyImg.fadeIn();
        workspacesAjaxCall();
    }
    
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

    function workspacesAjaxCall() {
        $.ajax({
          type: "GET",
          url: "request.php",
          data: "apiKey=" + apiKeyInput.val(),
          success: function( result ) {
            workspaceContainer.html(result).fadeIn();
            apiKeyImg.fadeOut();
            },
          error : function( msg ) {
            workspaceContainer.html(msg.responseText).fadeIn();
            apiKeyImg.fadeOut();
            }
        });
    }
    
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
     
    $('#change-workspace').on('click', function(){
        modalBackdrop.fadeIn();
    });
    
    $('#start-modal').on('click', '#start-modal > .modal-footer .btn', function (){
        modalBackdrop.fadeOut();
    });
    
    workspaceRefresh.on('click', function(){
        tasksAjaxCall();
        workspaceRefresh.hide();
        workspaceLoader.delay('200').show();
    });
    
    $('#workspace-container').on('click', '#workspace-container > .workspace', function(){
        var caption = $(this).find('h3').text();
        activeWorkspaceId = $(this).data('workspace-id');

        $('#start-modal').modal('hide');
        $('.workspace_caption').show().attr('data-workspace-id', activeWorkspaceId).html(caption);
        modalBackdrop.fadeOut();
        $('#start-modal').children('.modal-footer').html('<a href="#" class="btn" data-dismiss="modal">Close</a>');
        tasksAjaxCall();
        workspaceLoader.show();    });
    
    function tasksAjaxCall(projectId) {
        // clean tbody
        $('#track-table').show().find('tbody').html('<tr><td colspan="6">One moment please, your assigned tasks are loading...</td></tr>');
        
        $.ajax({
          type: "GET",
          url: "request.php",
          data: "apiKey=" + apiKeyInput.val() + "&workspaceId=" + activeWorkspaceId + "&projectId=all",
          success: function( result ) {
              $('#track-table').show().find('tbody').html(result);
              workspaceLoader.fadeOut();
              workspaceRefresh.fadeIn();
              initTimepicker();
              $('.my_label').tooltip();
            },
          error : function( msg ) {
              $('#track-table').html(msg.responseText).fadeIn();
              workspaceLoader.fadeOut();
              workspaceRefresh.fadeIn();
            }
        });
    }
    
    // Edit Time 
    function initTimepicker() {
        var hourWheel = {};
        for (var i=0;i<=100;i++) {
            hourWheel[""+i] = i;
        }
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
            wheels: [{ 'Hours': hourWheel, 'Minutes': minutesWheel}],
            headerText: false,
            rows: 5,
            onSelect: onScrollerSelect
        });
    }
    
    function onScrollerSelect(valueText) {
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
        /*
         * Progressbar
         */
        var locateProgressWrapper = parent.siblings('.my_progress').find('.progress');
        var percent = (dataEstimatedHours*60*1000 + dataEstimatedMinutes * 1000) / 100;
        percent = (dataWorkedHours*60*1000 + dataWorkedMinutes * 1000) / percent;
        
        // change progress state
        if(percent < 80){
            locateProgressWrapper.removeClass('progress-warning');
            locateProgressWrapper.removeClass('progress-danger');
            locateProgressWrapper.addClass('progress-success');
        } else if (percent >= 80 && percent < 100){
            locateProgressWrapper.removeClass('progress-success');
            locateProgressWrapper.removeClass('progress-danger');
            locateProgressWrapper.addClass('progress-warning');
        } else if (percent >= 100){
            locateProgressWrapper.removeClass('progress-success');
            locateProgressWrapper.removeClass('progress-warning');
            locateProgressWrapper.addClass('progress-danger');
        }
        
        locateProgressWrapper.find('.bar').css('width', percent + '%');
        changeTasksAjaxCall(taskId, dataEstimatedHours, dataEstimatedMinutes, dataWorkedHours, dataWorkedMinutes, taskName);
    }
    
    function changeTasksAjaxCall(getTaskId, getEstimatedHours, getEstimatedMinutes, newHours, newMinutes, getTaskName) {
        $.ajax({
              type: "GET",
              url: "request.php",
              data: "apiKey=" + apiKeyCookie + "&updateId=" + getTaskId + "&estimatedHours=" + getEstimatedHours + "&estimatedMinutes=" + getEstimatedMinutes + "&workedHours=" + newHours + "&workedMinutes=" + newMinutes + "&taskName=" + getTaskName,
              success: function( result ) {
                 //console.log('auto saved');
              },
              error : function( msg ) {
                 //console.log('something went wrong...');
              }
        });
    }
    
    $("#track-table").on('click', 'td.estimated_time', function(event){
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