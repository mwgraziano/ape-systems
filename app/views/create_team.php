<?php

$app = App::init();

$available_sports = $app->account->getTeamsToCreate();

$mascot = $app->account->getMascot();

?>

<div id="subnavbar">
	<div class="content">
		<h2>Create a New Team</h2>
	</div>
</div>
<div class="content">
	<form id="new-team-form">
		<label for="team-sport">
			Sport
			<select name="team-sport" id="team-sport" class="input input-large">
				<option value="">Select</option>
				<? foreach($available_sports as $sport=>$data) { ?>
					<option value="<?=$data["id"]?>"><?=$sport?></option>
				<? } //end foreach ?>
			</select>
		</label>
		<label for="team-gender">
			Gender
			<select name="team-gender" id="team-gender" class="input input-large">
				<option value="">Select</option>
			</select>
		</label>
		<label for="frm">
			<input type="submit" class="btn btn-large btn-action" value="Save Team" />
			
			<a href="#team/switch" class="btn btn-large btn-cancel">Cancel</a>
		</label>
	</form>
</div>

<script type="text/javascript">

    var available_sports = <?=json_encode($available_sports);?>

    $('#team-sport').on("change",function(){
        
        var sport = $(this)[0].options[$(this)[0].selectedIndex].text;
        
        $('#team-gender option').remove();
        
        console.log(sport);
        
        if(available_sports[sport]) {
            
            sports = available_sports[sport];
            
            console.log(sports);
            
            $('<option value="">Select</option>').appendTo("#team-gender");
            
            for(var i = 0; i < sports.genders.length; i++) {
                var gen = "";
                switch(sports.genders[i]) {
                    case 'i':
                        gen = 'Intramural';
                        break;
                    case 'm':
                        gen = 'Men';
                        break;
                    case 'f':
                        gen = 'Women';
                        break;
                }
                $('<option value="'+ sports.genders[i] +'">'+ gen +'</option>').appendTo("#team-gender");
            }
        }
        else {
            $('<option value="">No genders are available</option>').appendTo("#team-gender");
        }
    });

	$('#new-team-form').on("submit",function(e){
		e.preventDefault();
		var errors = [];

		App.removeFormErrors($('#new-team-form'));

		if($.trim($('#team-sport').val()) == '') errors.push('team-sport');
		if($.trim($('#team-gender').val()) == '') errors.push('team-gender');

		if(errors.length > 0) {
			for(var i=0; i<errors.length; i++) {
				App.addFormError($('#'+ errors[i]), 'You must select a value.');
			}
			return;
		}

		Api.data = $('#new-team-form').serializeObject();
		Api.done = function(data) {
			if(data.index) {
                App.refresh("#team/switch/"+ data.index);
			}
		};
		Api.call('/team','POST');
	});
</script>