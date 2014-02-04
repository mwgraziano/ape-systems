<?php 

$app = App::init();

$account = $app->account;

$t = Team::load(Session::getTeamId());

if(!$t) $app->redirect("/app#switch-team");

$athletes = $t->getAthletes();

$categories = $account->getMetricCategories();

$metrics = $t->getMetrics();

foreach($metrics as $met) {
    $c = $account->getMetricById($met['account_metric_id'], $met['metric_id']);
    
    if(!$c) continue;
    
    $cn = $c['account_metric_category_id'] .','. $c['metric_category_id'];
    
    $mets[$cn][] = $met;
    $units[$met['account_metric_id'].','. $met['metric_id']] = Unit::load($c['unit_id'])->getData();
}

?>

<script type="text/javascript">
    var categories = <?=(empty($categories) ? '[]' : json_encode($categories));?>;
    var metrics = <?=(empty($mets) ? '[]' : json_encode($mets));?>;
    var units = <?=(empty($units) ? '[]' : json_encode($units));?>;
</script>
<div id="subnavbar">
    <div class="content">
        <h2>Test Athletes</h2>
    </div>
</div>
<div class="content">
<div class="page-top-editor">
    <select id="test-category" class="input input-inline test-input">
        <? foreach($categories as $category) { ?>
        <option value="<?=$category['account_metric_category_id'].','.$category['metric_category_id']?>"><?=$category['name']?></option>
        <? } ?>
    </select>
    &nbsp;
    <select id="test-metric" class="input input-inline test-input">
        <?  if(!empty($categories)) {
                $cat = $categories[0]['account_metric_category_id'].','.$categories[0]['metric_category_id'];
                foreach($mets[$cat] as $met) { ?>
        <option value="<?=$met['account_metric_id'].','.$met['metric_id']?>"><?=$met['name']?></option>
        <?      }//foreach
            }//if ?>
    </select>
    &nbsp;
    <select id="test-unit" class="input input-inline test-input">
        
    </select>
    &nbsp;
    <input type="date" id="test-date" class="input input-inline test-input" value="<?=date('Y-m-d')?>"/> 
</div>
<table cellspacing="0" cellpadding="0" border="0" class="test-table">
    <tr>
        <th>Name</th>
        <th>Position</th>
        <th>Measurement</th>
    </tr>
<? foreach($athletes as $athlete) { ?>
    
    <tr idx="<?=$athlete->id?>">
        <td><?=$athlete->name?></td>
        <td class="center"><?=$athlete->position?></td>
        <td class="center">
            <span class="test-athlete-input-container"><input type="number" step="any" min="0" class="input input-inline test-athlete-input" idx="<?=$athlete->id?>" /> 
                <span class="test-athlete-unit-label"></span>
            </span>
            <span class="test-athlete-value-container hidden"></span>
        </td>
        
    </tr>
    
<? } ?>
</table>
</div>

<script type="text/javascript">
    var current_category, current_metric, current_unit;
    $('#test-category').on("change",function(e){
        e.preventDefault();
        var val = $(this).val();
        if(!metrics[val]) {
            $('#test-metric').addClass("hidden");
            $('#test-unit').addClass("hidden");
        }
        else {
            $('#test-metric').removeClass("hidden");
            $('#test-unit').removeClass("hidden");
        }
        $('#test-metric option').remove();
        $('#test-unit option').remove();
        
        $('.test-athlete-input').each(function(i,el){
            if($(el).val() > 0) {
                $(el).change();
            }
        });
        
        current_category = val;
        
        for(var i=0;i<metrics[val].length;i++) {
            $('<option value="'+ metrics[val][i].account_metric_id +','+ metrics[val][i].metric_id +'">'+ metrics[val][i].name +'</option>').appendTo($('#test-metric'));
        }
        
        $('#test-metric').change();
    });
    
    $('#test-metric').on("change",function(){
        
        var val = $(this).val();
        if(!units[val]) return;
        
        $('#test-unit option').remove();
        
        $('<option value="'+ units[val].id +'">'+ units[val].name +'</option>').appendTo($('#test-unit'));
        
        $('.test-athlete-unit-label').html(units[val].label);
        
        current_unit = units[val].id;
        current_metric = val;
        
    });
    
    $('.test-athlete-input').on("change",function(e){
        submitData($(this), $(this).attr('idx'), $(this).val());
    });
    
    function submitData(el, ath_id, val, orig_date) {
        Api.data = {'ath-id': ath_id,
                    'ath-new-value': val,
                    'ath-category': current_category,
                    'ath-metric': current_metric,
                    'ath-unit': current_unit};
        
        if((typeof orig_date == "undefined") || orig_date == null) Api.data['ath-new-date'] = $('#test-date').val();
        else Api.data['orig_date'] = orig_date;
        
        Api.done = function(data) {
            console.log(data);
        }
        
        Api.call('/athlete/measurement/'+ ath_id, 'POST');
    }
    
    $(function(){
        $('#test-metric').change();
    });
</script>
