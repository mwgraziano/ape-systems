<?php 

$t = Team::load(Session::getTeamId());

if(!$t) $app->redirect("/app#switch-team");

$athletes = $t->getAthletes();

?>

<div id="subnavbar">
    <div class="content">
        <h2>Team Roster</h2>
        
        <a class="btn actionbutton" href="#athlete/new">Add Athlete</a>
        
    </div>
</div>
<div class="content">
<div id="athlete-profile" class="hidden">
    <div id="athlete-profile-photo">
        <img src="" id="athlete-profile-photo-img" />
        <a href="#athlete/profile/" id="athlete-profile-edit" class="btn">View Profile</a>
    </div>
    <div id="athlete-profile-detail">
        <strong id="athlete-profile-name"></strong>
        <p>
            Pos: <span id="athlete-profile-position"></span><br/>
            Year: <span id="athlete-profile-start"></span><br/>
            Height: <span id="athlete-profile-height"></span><br/>
            Hometown: <span id="athlete-profile-hometown"></span>
        </p>
    </div>
    <div id="athlete-data-detail" class="athlete-data-detail">
        
    </div>
    <div class="both"></div>
</div>
<ul class="roster-athletes">
<? foreach($athletes as $athlete) { ?>
    
    <li><a href="javascript:void(0);" idx="<?=$athlete->id?>" class="action-athlete-profile"><?=$athlete->name?><?=($athlete->position ? " - ". $athlete->position : "") ?></a></li>
    
<? } ?>
</ul>
</div>

<script type="text/template" id="player-metrics">
    <div class="metric">
        <h4>{#metric_name}</h4>
        <p>
            {#pretty_data_value}<span class="metric-label">{#label}</span>
        </p>
    </div>
</script>

<script type="text/javascript">
    $(function(){
        App.selectTeam(<?=json_encode($t->getData())?>);
    });
    
    function fillAthleteProfile(athlete) {
        $('#athlete-data-detail').html("");
        (athlete.name ? $('#athlete-profile-name').html(athlete.name) : $('#athlete-profile-name').html(""));
        (athlete.position ? $('#athlete-profile-position').html(athlete.position) : $('#athlete-profile-position').html(""));
        (athlete.start ? $('#athlete-profile-start').html(athlete.start) : $('#athlete-profile-start').html(""));
        (athlete.height ? $('#athlete-profile-height').html(athlete.pretty_height) : $('#athlete-profile-height').html(""));
        (athlete.hometown ? $('#athlete-profile-hometown').html(athlete.hometown) : $('#athlete-profile-hometown').html(""));
        ($('#athlete-profile-edit').attr('href','#athlete/profile/'+athlete.id));
        
        if(athlete.key_metrics) {
            for(var i=0; i<athlete.key_metrics.length; i++) {
                $('#player-metrics').parseTmpl(athlete.key_metrics[i]).appendTo($('#athlete-data-detail'));
            }
        }
        
        ($('#athlete-profile-photo-img').attr('src',athlete.photo));
        $('#athlete-profile').removeClass('hidden');
    }
    
    $('.action-athlete-profile').on("click",function(e){
        e.preventDefault();
        
        var idx = $(this).attr('idx');
        
        Api.data = {'id':idx};
        Api.done = function(data) {
            if(data.index.error) {
                console.log(data.index.error);
            }
            else {
                fillAthleteProfile(data.index);
            }
        }
        Api.call('/athlete/'+ idx, 'GET');
    })
</script>
