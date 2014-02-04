<?

$app = App::init();

$can_add = false;
$is_admin = false;

if($app->isAuth() >= AUTH_LEVEL_ADMIN) {
	$can_add = true;
	$is_admin = true;
}

$acct = $args[0] ? Account::load($args[0]) : (($can_add === true) ? false : $app->account);

if(!$acct) {
	$acct = new stdClass();
	$no_acct = true;
}

$style = $app->getStyle();

?>
<div id="subnavbar">
	<div class="content">
		<h2><?=$no_acct ? "New Organization" : $acct->getName(); ?> - Admin</h2>
	</div>
</div>
<div class="content">

	<? if($no_acct == false) { ?>
	<div id="org-detail">
		<h2>
			<?=$acct->name?> - <?=$acct->mascot?> 
			<button href="javascript:void(0)" id="edit-org-action" class="btn btn-small">Edit</button>
		</h2>
	<? if($is_admin) { ?>

		<p>
			<?=$acct->contact ? $acct->contact ."<br/>" : "" ?>
			<?=$acct->phone ? $acct->phone ."<br/>" : "" ?>
			<?=$acct->address ? $acct->address ."<br/>" : "" ?>
			<?=$acct->city ? $acct->city."," : ""?> <?=$acct->state?> <?=$acct->zip?>
		</p>

	<? } ?>

		<h2>
			Coaches
			<button href="javascript:void(0)" id="add-coach-action" class="btn btn-small">Add New</button>
		</h2>
		<div id="coach-detail-0" class="well hidden"></div>
	<?
		$coaches = $acct->getCoaches();

		foreach($coaches as $coach) {
	?>
        <div class="coach-container" id="coach-<?=$coach->getId()?>-container">
            <div id="coach-<?=$coach->getId()?>-info">
                <a href="javascript:void(0);" class="edit-coach btn-link-dark btn-tall" idx="<?=$coach->getId()?>"><strong><?=$coach->name?></strong> <?=($coach->title == "") ? "" : "- ". $coach->title ?></a>
            </div>
            <div id="coach-<?=$coach->getId()?>-form" class="hidden well">
            <form id="coach-detail-frm-<?=$coach->id?>" onsubmit="return false" class="coach-detail-frm">
                <a href="javascript:void(0)" class="action-delete-coach btn btn-action right" idx="<?=$coach->id?>">Delete Coach</a>
                <h2>Editing Coach <?=$coach->name?></h2>
                <label>
                    <input type="hidden" id="coach-delete-err-<?=$coach->id?>" />
                </label>
                <input type="hidden" name="coach-id" value="<?=$coach->id?>" />
                <input type="hidden" name="coach-edit-key" id="coach-edit-key-<?=$coach->id?>" value="<?=Session::generateNonce("Coach",$coach->id)?>" />
                <label>
                    Name
                    <input type="text" name="coach-name" class="input input-large" value="<?=$coach->name?>" />
                </label>
                <label>
                    Position / Title
                    <input type="text" name="coach-title" class="input input-large" value="<?=$coach->title?>" />
                </label>
                <label>
                    Email (Username)
                    <input type="email" name="coach-email" class="input input-large" value="<?=$coach->email?>" />
                </label>
                <label>
                    Password (Leave blank for no change)
                    <input type="password" name="coach-pass" class="input input-large" />
                </label>
                <label>
                    Confirm Password
                    <input type="password" name="coach-pass-confirm" class="input input-large" />
                </label>
                <label>
                    <input type="hidden" name="coach-error" />
                </label>
                <input type="submit" class="btn btn-large btn-action" value="Save Coach" />
                <a href="javascript:void(0)" class="btn btn-large btn-cancel edit-coach" idx="<?=$coach->getId()?>">Cancel</a>
            </form>
            </div>
        </div>
	<?
		} //foreach coach
	?>
	</div>
	<? } ?>
	<form id="org-form" <? if($no_acct == false) { ?> class="hidden" <? } ?> method="POST" action="/org/<?=$acct->id?>" enctype="multipart/form-data" target="upload-frame">
		<input type="hidden" value="<?=Session::generateNonce("Account",$acct->id)?>" name="req-key" />
		<label for="org-name">
			Organization
			<input type="text" name="org-name" id="org-name" class="input input-large" value="<?=$acct->name?>">
		</label>
		<label for="org-mascot">
			Sports Name
			<input type="text" name="org-mascot" id="org-mascot" class="input input-large" value="<?=$acct->mascot?>">
		</label>
		<label for="org-logo">
			Logo (Max size 5MB)
			<br/>
			<input type="file" name="org-logo" id="org-logo" />
		</label>
		<? if($is_admin) { ?>
		<label for="remove-image" style="border-bottom:1px solid silver;">
            <input type="checkbox" name="remove-image" id="remove-image" value="on" /> Check this box to remove the current image
        </label>
        <? } ?>
		<label for="org-colors">
			Custom Colors

			<div class="well">
				<div class="org-color-select">Backgrounds</div>
				<div class="org-color-select"><input type="text" class="color {hash:true}" idx="@back-color-1" name="back-color-1" value="<?=$style["{back-color-1}"]?>"/></div>
				<div class="org-color-select"><input type="text" class="color {hash:true}" idx="@back-color-2" name="back-color-2" value="<?=$style["{back-color-2}"]?>"/></div>
				<div class="org-color-select"><input type="text" class="color {hash:true}" idx="@back-color-3" name="back-color-3" value="<?=$style["{back-color-3}"]?>"/></div>
				<div class="org-color-select"><input type="text" class="color {hash:true}" idx="@back-color-4" name="back-color-4" value="<?=$style["{back-color-4}"]?>"/></div>
				<div class="org-color-select"><input type="text" class="color {hash:true}" idx="@back-color-error" name="back-color-error" value="<?=$style["{back-color-error}"]?>"/></div>
				<div class="both"></div>
				<div class="org-color-select">Fonts</div>
				<div class="org-color-select"><input type="text" class="color {hash:true}" idx="@font-color-1" name="font-color-1" value="<?=$style["{font-color-1}"]?>"/></div>
				<div class="org-color-select"><input type="text" class="color {hash:true}" idx="@font-color-2" name="font-color-2" value="<?=$style["{font-color-2}"]?>"/></div>
				<div class="both"></div>
			</div>
			<script type="text/javascript">
				$('input.color').on("change",function(){
					App.liveColorChange();
				})
			</script>
		</label>
		
	<? if($is_admin) { ?>
		<label for="org-contact">
			Contact Name
			<input type="text" name="org-contact" id="org-contact" class="input input-large" value="<?=$acct->contact?>">
		</label>
		<label for="org-phone">
			Contact Phone
			<input type="text" name="org-phone" id="org-phone" class="input input-large" value="<?=$acct->phone?>">
		</label>
		<label for="org-address">
			Address
			<input type="text" name="org-address" id="org-address" class="input input-large" value="<?=$acct->address?>">
		</label>
		<label for="org-city">
			City
			<input type="text" name="org-city" id="org-city" class="input input-large" value="<?=$acct->city?>">
		</label>
		<label for="org-state">
			State
			<input type="text" name="org-state" id="org-state" class="input input-large" value="<?=$acct->state?>">
		</label>
		<label for="org-zip">
			Zip Code
			<input type="text" name="org-zip" id="org-zip" class="input input-large" value="<?=$acct->zip?>">
		</label>
	<? } //end admin only fields ?>
	    <label for="org-error">
            <input type="hidden" name="org-error" id="org-error">
        </label>
		<label for="frm">
			<input type="submit" class="btn btn-large btn-action" value="Save Organization" />
			
			<? if(is_numeric($acct->id)) { ?>
				<a href="javascript:void(0)" class="btn btn-large btn-cancel" id="org-form-cancel">Cancel</a>
			<? } ?>
		</label>
	</form>


</div>
<script type="text/x-j-tmpl" id="coach-frm-tmpl">
	<form id="coach-detail-frm-{#id}" onsubmit="return false" class="coach-detail-frm">
		<h2>{#form-title}</h2>
		<input type="hidden" name="coach-id" value="{#id}" />
		<input type="hidden" name="coach-edit-key" value="{#edit_key}" />
		<label>
			Name
			<input type="text" name="coach-name" class="input input-large" value="{#name}" />
		</label>
		<label>
			Position / Title
			<input type="text" name="coach-title" class="input input-large" value="{#title}" />
		</label>
		<label>
			Email (Username)
			<input type="email" name="coach-email" class="input input-large" value="{#email}" />
		</label>
		<label>
			Password
			<input type="password" name="coach-pass" class="input input-large" />
		</label>
		<label>
			Confirm Password
			<input type="password" name="coach-pass-confirm" class="input input-large" />
		</label>
		<label>
		    <input type="hidden" name="coach-error" id="coach-error" />
		</label>
		<input type="submit" class="btn btn-large btn-action" value="Save Coach" />
		<a href="javascript:void(0)" class="btn btn-large btn-cancel btn-cancel-coach-edit" idx="{#id}">Cancel</a>
	</form>
</script>

<iframe src="false" id="upload-frame" name="upload-frame" style="visibility:hidden;width:1px;height:1px;" border="0"></iframe>

<script type="text/javascript">

	$('#edit-org-action').on("click",function(){
		$('#org-detail').addClass("hidden");
		$('#org-form').removeClass("hidden");
	});

	$('#org-form-cancel').on('click',function(){
		$('#org-detail').removeClass('hidden');
		$('#org-form').addClass('hidden');
		$('#org-form')[0].reset();
	});


	$('#org-form').on('submit',function(e){
		
		var errors = [];

		App.removeFormErrors($('#org-form'));

		if($.trim($('#org-name').val()) == '') errors.push($('#org-name'));

		if(errors.length > 0) {
			for(var i=0; i<errors.length; i++) {
				App.addFormError(errors[i], 'You must enter a value.');
			}
			return false;
		}
		
		if($('#page-overlay')) {
            $('#page-overlay').removeClass('hidden');
        }
        
        document.getElementById('org-form').target = 'upload-frame';
        
        $('#upload-frame').one('load', function(){
            
            var html = $(frames[0].document.body).text();
            
            if($('#page-overlay')) {
                $('#page-overlay').addClass('hidden');
            }
            
            try {
                var data = $.parseJSON(html);
                
                if(data && data.index && data.index.error) {
                    App.addFormError($('#org-error'), data.index.error);
                    return;
                }
                else if(data && data.index){
                    App.refresh('org/'+ data.index);
                    return;
                }
                else {
                    App.addFormError($('#org-error'), "There was an unexpected error when saving.");
                    return;
                }
                
            } catch (e) {
                App.addFormError($('#org-error'), "There was an unexpected error when saving.");
                return;
            }
        });
	});

	jscolor.init();

	$('#add-coach-action').on("click",function() {
		
		if(!$('#coach-detail-0').hasClass("hidden")) {
			$('#coach-detail-0').addClass("hidden")
			$('#coach-detail-0').html("");
			return;
		}

		$('#coach-frm-tmpl').parseTmpl({'form-title':'New Coach','id':0}).appendTo($('#coach-detail-0'));
		$('#coach-detail-0').removeClass('hidden');
		$('#coach-detail-0').find('input[type="text"]')[0].focus();
	});

    $(document.body).off('click','.btn-cancel-coach-edit');
	$(document.body).on('click','.btn-cancel-coach-edit',function() {
		var id = $(this).attr('idx');
		if(id && $('#coach-detail-'+ id).length) {
			$('#coach-detail-'+ id).addClass('hidden');
			$('#coach-detail-'+ id).html("");
		}
	});
	
	$(document.body).off('click','.action-delete-coach');
	$(document.body).on('click','.action-delete-coach', function(e) {
	    
	    e.preventDefault();
	    
	    var id = $(this).attr('idx');
	    if(!id) return;
	    
	    if($('#coach-edit-key-'+ id).length) var key = $('#coach-edit-key-'+id).val();
	    
	    if(!key) reutrn;
	    
	    Api.data = {'coach_id': id, 'coach-edit-key': key};
        Api.done = function(data) {
            if(data.delete.error) {
                App.addFormError($('#coach-delete-err-'+ id), data.delete.error);
                return;
            }
            else App.reload();
        }
        Api.call('/coach/delete/'+ id, "POST");
	});

    $(document.body).off("submit",".coach-detail-frm")
	$(document.body).on("submit",".coach-detail-frm",function(e) {
		
		e.preventDefault();

		var errors = [];
		App.removeFormErrors($(this));

		var id = $(this).find("input[name='coach-id']");
		var pass = $(this).find("input[name='coach-pass']");
		var passc = $(this).find("input[name='coach-pass-confirm']");
		var email = $(this).find("input[name='coach-email']");
		var name = $(this).find("input[name='coach-name']");
		var ierr = $(this).find("input[name='coach-error']");

		if($.trim(id.val()) == 0) {
			if($.trim(pass.val()).length <= 0 || $.trim(pass.val()) != $.trim(passc.val())) {
				//Error password
				errors.push([pass,'Passwords must be non-blank and match the confirmation']);
			}
		}
		else {
			if($.trim(pass.val()) != $.trim(passc.val())) {
				//Error password
				errors.push([pass,'Password does not match the confirmation']);
			}
		}

		if($.trim(email.val()) == '') {
			//error email
			errors.push([email,'You must enter an email address']);
		}

		if($.trim(name.val()) == '') {
			//error name
			errors.push([name, 'You must provide a name']);
		}

		if(errors.length > 0) {
			for(var i=0; i<errors.length; i++) {
				App.addFormError(errors[i][0], errors[i][1]);
			}
			return;
		}

		Api.data = $(this).serializeObject();
		Api.done = function(data) {
		    if(data.index.error) {
				App.addFormError(ierr, data.index.error);
				return;
			}
			else App.reload();
		}
		Api.call('/coach/'+ id.val(), "POST");
	});
	
	
	$('.edit-coach').on("click",function(e) {
	    var id = $(this).attr('idx');
	    if(id) {
	        
	        var frm = $('#coach-'+ id +'-form');
	        if(frm.length) {
	            if(frm.hasClass('hidden')) frm.removeClass('hidden');
	            else frm.addClass('hidden');
	        }
	    }
	});
	
	$('#org-photo').on('change', function(event) {
            
        $('#org-photo-error').addClass('hidden');
        
        var ext = $(this).val().substr($(this).val().lastIndexOf(".")).toLowerCase();
        
        if (ext != ".jpeg" && ext != ".jpg" && ext != ".png" && ext != ".gif") {
            $('#org-photo-error').html("That file type is not supported. Please select a JPG, PNG, or GIF image.");
            $('#org-photo-error').removeClass("hidden");
            $(this).val("");
            return;
        }
    });

<? if($acct->id) { ?>
	App.selectOrg(<?=json_encode($acct->getData())?>);
<? } ?>

</script>