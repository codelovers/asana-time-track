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

    $('.my_timer .btn').live('click', function(event){
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
        console.log(e.target);
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
            },
          error : function( msg ) {
              $('#track-table').html(msg.responseText).fadeIn();
              workspaceLoader.fadeOut();
              workspaceRefresh.fadeIn();
            }
        });
    }
      
    
});