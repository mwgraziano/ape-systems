<?php

$app = App::init();
$acct = $app->account;

$team_id = Session::getTeamId();

if(empty($team_id)) $app->hredirect("#switch-team");

$a = new stdClass();
$title = "Create an Athlete";

if($args[0] && is_numeric($args[0])) {
    $a = Athlete::load($args[0], $app->account->id);
    $title = "Edit Athlete ". $a->name;
}

if(Session::getDataEntryMode() == 1) {
    $add_another = true;
}

?>

<div id="subnavbar">
    <div class="content">
        <h2><?=$title?></h2>
    </div>
</div>
<div class="content">
    <form id="ath-form" action="/athlete/<?=$a->id?>" method="POST" enctype="multipart/form-data" target="upload-frame" >
        <input type="hidden" value="<?=Session::generateNonce("Athlete",$athlete->id)?>" name="req-key" />
        <input type="hidden" value="<?=$a->id?>" id="ath-id" />
        <label for="ath-name">
            Full Name
            <input type="text" name="ath-name" id="ath-name" class="input input-large" value="<?=$a->name?>" />
        </label>
        <label for="ath-position">
            Position
            <input type="text" name="ath-position" id="ath-position" class="input input-large" value="<?=$a->position?>" />
        </label>
        <label for="ath-start">
            Year Started
            <input type="number" name="ath-start" id="ath-start" class="input input-large" value="<?=$a->start?>" min="1980" max="<?=(date(Y)+1)?>" step="1"/>
        </label>
        <label for="ath-hometown">
            Hometown
            <input type="text" name="ath-hometown" id="ath-hometown" class="input input-large" value="<?=$a->hometown?>" />
        </label>
        <label for="ath-position">
            Height (inches)
            <input type="number" name="ath-height" id="ath-height" class="input input-large" value="<?=$a->height?>" step="any" min="0" max="150" />
        </label>
        <label for="ath-position">
            Email
            <input type="email" name="ath-email" id="ath-email" class="input input-large" value="<?=$a->email?>" />
        </label>
        <label for="ath-photo">
            Photo (Max size of 5MB)
            <input type="file" name="ath-photo" id="ath-photo" accept=".jpeg,.jpg,.png,.gif" />
        </label>
        <label for="ath-error">
            <input type="hidden" id="ath-error" />
        </label>
        <label>
            <br/>
            <input type="checkbox" name="ath-add-another" id="ath-add-another" <?=$add_another ? 'checked' : ''?>> Add another after saving
        </label>
        <label for="frm">
            <input type="submit" class="btn btn-large btn-action" value="Save Athlete" />
            
            <? if(is_numeric($acct->id)) { ?>
                <a href="javascript:void(0)" class="btn btn-large btn-cancel" id="ath-form-cancel">Cancel</a>
            <? } ?>
        </label>
    </form>
</div>

<iframe src="false" id="upload-frame" name="upload-frame" style="visibility:hidden;width:1px;height:1px;" border="0"></iframe>

<script type="text/javascript">

    $(function() {
        $('#ath-form input[type=text]')[0].focus();
    });

    $('#ath-form-cancel').on("click",function() {
        App.back();
    });
    
    $('#ath-form').on("submit",function(e) {
        
        var errors = [];
        
        var name = $('#ath-name');
        var ierr = $('#ath-error');
        var id = $('#ath-id');
        add_another = false;
        
        if($('#ath-add-another').prop('checked')) {
            add_another = true;
        }
        
        if($.trim(name.val()) == '') {
            errors.push([pass,'Passwords must be non-blank and match the confirmation']);
        }
        
        if(errors.length > 0) {
            for(var i=0; i<errors.length; i++) {
                App.addFormError(errors[i][0], errors[i][1]);
            }
            return false;
        }
        
        if($('#page-overlay')) {
            $('#page-overlay').removeClass('hidden');
        }
        
        document.getElementById('ath-form').target = 'upload-frame';
        
        $('#upload-frame').one('load', function(){
            
            var html = $(frames[0].document.body).text();
            
            if($('#page-overlay')) {
                $('#page-overlay').addClass('hidden');
            }
            
            try {
                var data = $.parseJSON(html);
                
                if(data && data.index && data.index.error) {
                    App.addFormError(ierr, data.index.error);
                    return;
                }
                else if(data && data.index){
                    if(add_another) App.loadContent('athlete/new');
                    else {
                        App.refresh('athlete/profile/'+ data.index);
                        return;
                    }
                }
                else {
                    App.addFormError(ierr, "There was an unexpected error when saving.");
                    return;
                }
                
            } catch (e) {
                App.addFormError(ierr, "There was an unexpected error when saving.");
                return;
            }
        });

    });
    
    $('#ath-photo').on('change', function(event) {
            
        $('#ath-photo-error').addClass('hidden');
        
        var ext = $(this).val().substr($(this).val().lastIndexOf(".")).toLowerCase();
        
        if (ext != ".jpeg" && ext != ".jpg" && ext != ".png" && ext != ".gif") {
            $('#ath-photo-error').html("That file type is not supported. Please select a JPG, PNG, or GIF image.");
            $('#ath-photo-error').removeClass("hidden");
            $(this).val("");
            return;
        }
    });
</script>
