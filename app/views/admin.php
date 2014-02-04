<?
$app = App::init();
// $login_data = ReportingService::getUserLoginsByDate(30);
// $hip_usage = ReportingService::getHipCodeUsage();
// $text_messages = ReportingService::getTextMessaging(30);
// $reg_users = ReportingService::getUsersRegistered(30);

$orgs = Account::findId_Name_Mascot_Active(array("ORDER"=>"name"));

?>

<div id="subnavbar">
    <div class="content">
        <h2>APE Systems Admin Tool</h2>
    </div>
</div>
<div class="content">
    <h2>Organizations &nbsp; <a href="#org" class="btn">Add New<a></h2>
    <? foreach($orgs as $org) { ?>
    <li><a href="javascript:void();"  idx="<?=$org["id"]?>" class=" <?=$org['active'] == 0 ? 'inactive' : ''?> action-switch-org"><?=$org['name']?> - <?=$org['mascot']?></a></li>
    <? } ?>
</div>

<script type="text/javascript">
    $('.action-switch-org').on("click",function(e) {
        e.preventDefault();
        
        var id = $(this).attr('idx');
        
        Api.data = {};
        Api.done = function(data) {
            if(data.switch) {
                App.refresh('org/'+ data.switch);
            }
        }
        Api.call("/org/switch/"+ id);
    });
</script>
