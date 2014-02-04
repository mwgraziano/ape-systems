<?php

$app = App::init();

$teams = Team::findId_Name_SportId(array("account_id"=>$app->account->id));

?>

<div id="subnavbar">
	<div class="content">
		<h2>Select A Team</h2>
		<a class="btn actionbutton" href="#team">Create New Team</a>
	</div>
</div>
<div class="content">
    <? foreach($teams as $team) { ?>
    <li><a href="#team/switch/<?=$team['id']?>"><?=$team['name']?></a></li>
    <? } ?>

</div>