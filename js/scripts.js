$(function(){
    var apiKeyCookie = $.cookie('asana-api-key');
    var apiKeyForm = $('#api-key-form');
    var apiKeyFormSubmit = apiKeyForm.find('button[type=submit]');
    var apiKeyEditDelete = apiKeyForm.find('#api-key-edit, #api-key-delete, #api-key-refresh');
    var apiKeyInput = apiKeyForm.find('#api-key');
    var workspaceContainer = apiKeyForm.siblings('#workspace-container');
    var apiKeyImg = apiKeyForm.find('.ajax_img');
    var workspaceLoader = $('.container').children('#loader-wrapper');
    var loadingTextWrapper = $('.loading_text');
    var activeWorkspaceId = null;
    
    // some loading texts
    var loadingText = new Array('One apple a day keeps the doctor away...', 
                                'If you can dream it, you can do it. Walt Disney (1901-1966)...',
                                'Before you can score, you must first have a goal...',
                                'Our team is well balanced. We have problems everywhere. Tommy Prothro (1941)...',
                                'The prize of greatness is responsibility. Winston Churchill (1874-1965)...',
                                'Nothing is paticularily hard if you divide it into small jobs. Henry Ford (1863-1947)...');
                                
    var loadingTextLength = loadingText.length;
    
      
    $('.my_timer .btn').live('click', function(event){
        var locateClickedElement = $(this);
        var locateButtonText = locateClickedElement.find('span');
        var locateButtonIcon = locateClickedElement.find('i.icon-white');
        
        if(locateClickedElement.hasClass('btn-success')){
            locateClickedElement.removeClass('btn-success');
            locateClickedElement.addClass('btn-danger');
            locateButtonText.html('Stop');
            locateButtonIcon.removeClass('icon-play').addClass('icon-stop');
        } else{
            locateClickedElement.removeClass('btn-danger');
            locateClickedElement.addClass('btn-success');
            locateButtonText.html('Start');
            locateButtonIcon.removeClass('icon-stop').addClass('icon-play');
        }

        // start time tracking
        locateClickedElement.prev('.time').stopwatch().stopwatch('toggle');
        
    });
    
    $('#start-modal').modal();
    
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
    
    $('#workspace-container').on('click', '#workspace-container > .workspace', function(){
        var caption = $(this).find('h3').text();
        activeWorkspaceId = $(this).data('workspace-id');

        $('#start-modal').modal('hide');
        $('.workspace_caption').show().attr('data-workspace-id', activeWorkspaceId).html(caption);
        // projectsAjaxCall();
        tasksAjaxCall();
        randomLoadingText();
        workspaceLoader.show();    });
    
    /*
    function projectsAjaxCall() {
        $.ajax({
          type: "GET",
          url: "request.php",
          data: "apiKey=" + apiKeyInput.val()+"&workspaceId=" + $('.workspace_caption').data('workspace-id'),
          success: function( result ) {
              $('#track-table').show().children('.nav-tabs').html(result);
              var projectId = $('#track-table').children('.nav-tabs').children('.active').data('project-id');
              tasksAjaxCall(projectId);
            },
          error : function( msg ) {
              $('.container').html(msg.responseText).fadeIn();
              workspaceLoader.fadeOut();
            }
        });
    }
    */
    $('.nav-tabs').on('click', '.nav-tabs > li:not(.active)', function(){        var projectId = $(this).data('project-id');
        $('#tab1').hide();
        randomLoadingText();
        workspaceLoader.show();
        tasksAjaxCall(projectId);    });
    
    function tasksAjaxCall(projectId) {
        
        // clean tbody
        $('#tab1').show().find('tbody').html('<tr><td colspan="6">One moment please, your assigned tasks are loading...</td></tr>');
        
        $.ajax({
          type: "GET",
          url: "request.php",
          data: "apiKey=" + apiKeyInput.val() + "&workspaceId=" + activeWorkspaceId + "&projectId=" + projectId,
          success: function( result ) {
              $('#tab1').show().find('tbody').html(result);
              workspaceLoader.fadeOut();
            },
          error : function( msg ) {
              $('.container').append(msg.responseText).fadeIn();
              workspaceLoader.fadeOut();
            }
        });
    }
    
    function randomLoadingText(){
        var randomNumber = Math.floor((Math.random()*loadingTextLength));
        loadingTextWrapper.html(loadingText[randomNumber]);
    }
      
    
});