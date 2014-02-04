<?php 

$app = App::init();

$athlete = $args;

$ath = Athlete::load($athlete['id']);

$team = $ath->getTeam();

Session::setTeamId($team->getId());

$key_metrics = $team->getKeyMetrics();
$metrics = $team->getMetrics();

$key_metric_values = $ath->getMostRecentMetricMeasurements($key_metrics);

$all_metric_values = $ath->getMostRecentMeasurements();

$history = $ath->getMeasurements();

?>

<script type="text/javascript">
    var chart_data = [];
</script>
<div id="subnavbar">
    <div class="content">
        <h2>Athlete Profile</h2>
        
        <a class="btn actionbutton" href="#athlete/edit/<?=$athlete['id']?>">Edit Athlete</a>
        
    </div>
</div>
<div class="content">
    <div id="athlete-profile">
        <div id="athlete-profile-photo">
            <img src="<?=$athlete['photo']?>" id="athlete-profile-photo-img" />
            <a href="#athlete/edit/<?=$athlete['id']?>" id="athlete-profile-edit" class="btn">Edit Profile</a>
        </div>
        <div id="athlete-profile-detail">
            <strong id="athlete-profile-name"><?=$athlete['name']?></strong>
            <p>
                Pos: <span id="athlete-profile-position"><?=$athlete['position']?></span><br/>
                Year: <span id="athlete-profile-start"><?=$athlete['start']?></span><br/>
                Height: <span id="athlete-profile-height"><?=$athlete['pretty_height']?></span><br/>
                Hometown: <span id="athlete-profile-hometown"><?=$athlete['hometown']?></span>
            </p>
        </div>
        <div id="athlete-data-detail">
    <? foreach($key_metrics as $metric) { ?>
            <div class="metric">
                <h4><?=$metric['name']?></h4>
                <p>
                <?
                    $m = $key_metric_values->getMeasurementsByMetricIds($metric['account_metric_id'], $metric['metric_id']);
                    if(!empty($m)) {
                        echo $m[0]['pretty_data_value'] .'<span class="metric-label">'. $m[0]['label'] .'</span>';
                    }
                    else echo "N/A";
                ?>
                </p>
            </div>
    <? } //foreach key_metrics ?>
        </div>
        <div class="both"></div>
    </div>
    
    <div id="athlete-categories">
        <a href="javascript:void(0)" class="athlete-category active" idx="all">All</a>
    <?
        $categories = $app->account->getMetricCategories();
        foreach($categories as $cat) { ?>
        <a href="javascript:void(0)" class="athlete-category" idx="<?=$cat['account_metric_category_id'].','. $cat['metric_category_id']?>"><?=$cat['name']?></a>
    <?  } ?>
    </div>
    
    <div class="athlete-metrics">
        
    <?
        $mets = array();
        $units = array();
        foreach($metrics as $metric) {
                
            $m = $all_metric_values->getMeasurementsByMetricIds($metric['account_metric_id'],$metric['metric_id']);
            
            if(!isset($mets[$metric['account_metric_id'].','.$metric['metric_id']])) {
                $mets[$metric['account_metric_id'].','.$metric['metric_id']] = $app->account->getMetricById($metric['account_metric_id'],$metric['metric_id']);
            }
            
            $mid = $metric['account_metric_id'].','.$metric['metric_id'];
 
            if(empty($mets[$mid])) continue;
            
            $delta = "--";
            $r_count = 0;
            $r = array();
            if(empty($m[0])) {
                $tracked_date = 'Not yet';
                $last = "N/A";
                $last_label = "";
                $last_long_label = "";
                
            } 
            else {
                
                $r = $history->getMeasurementsByMetricIds($metric['account_metric_id'],$metric['metric_id']);
                
                $r_count = count($r);
                
                if($r_count > 1) {
                    $delta = ($r[0]['data_value']) - ($r[1]['data_value']);
                    
                    if($delta > 0) $delta = '<span class="delta-good">+'. abs($delta) ."</span>";
                    else if($delta < 0) $delta = '<span class="delta-bad">-'. abs($delta) ."</span>";
                    else $delta = "0";
                }
                
                $tracked_date = $m[0]['pretty_data_date'];
                $last = $m[0]['pretty_data_value'];
                $last_label = $m[0]['label'];
                $last_long_label = $m[0]['long_label'];
            }
            
            $team_values = Measurement::getMostRecentTeamMetricMeasurements($team->getId(), array($metric));
            
            if(empty($team_values)) {
                $team_avg = "N/A";
                $team_avg_label = "";
            }
            else {
                
                if(!isset($units[$team_values[0]['unit_id']])) $units[$team_values[0]['unit_id']] = Unit::load($team_values[0]['unit_id']);
                
                $team_avg = round($team_values[0]['avg_data_value'],1);
                $team_avg_label = ($units[$team_values[0]['unit_id']]) ? $units[$team_values[0]['unit_id']]->getLabel() : "";
                
            }

            
            
        ?>
        
        <div class="athlete-metric" catidx="<?=$mets[$mid]['account_metric_category_id'] .','. $mets[$mid]['metric_category_id']?>">
            <div class="athlete-metric-title">
                <div class="title"><?=$mets[$mid]['name']?></div>
                <div class="last-tracked">Last tracked: <?=$tracked_date?></div>
                <div class="both"></div>
            </div>
            <div class="athlete-metric-data-container">
                <div class="athlete-metric-data">
                    <div class="data">
                        <?=$last?>
                        <span class="label"><?=$last_label?></span>
                    </div>
                    <div class="title">
                        Latest Record
                    </div>
                </div>
                
                <div class="athlete-metric-data">
                    <div class="data">
                        <?=$team_avg?>
                        <span class="label"><?=$team_avg_label?></span>
                    </div>
                    <div class="title">
                        Latest Team Avg.
                    </div>
                </div>
                
                <div class="athlete-metric-data">
                    <div class="data">
                        <?=$delta?>
                    </div>
                    <div class="title">
                        Delta Since Last
                    </div>
                </div>
                
                <? if($r_count > 1) { ?>
                <div class="athlete-metric-data athlete-metric-data-chart" style="cursor:pointer" idx="<?=$mid?>">
                    <div class="data">
                        <img src="/img/chart.png" />
                    </div>
                    <div class="title">
                        View Graph
                    </div>
                    <script type="text/javascript">
                        chart_data["<?=$mid?>"] = {
                            _y_label: '<?=$last_long_label?>',
                            _y_label_short: '<?=$last_label?>',
                            name: '<?=$mets[$mid]['name']?>',
                            data: [
                            <?  foreach($r as $id=>$dt) {
                                    $td = strtotime($dt['pretty_data_date']);
                                    $cm = ($id > 0) ? "," : "";
                                    echo $cm. "[Date.UTC(". date('Y',$td) .",". (date('m',$td)-1) .",". date('d',$td) .",". date('H',$td) .",". date('i',$td) .",". date('s',$td) ."), ". $dt['pretty_data_value'] ."]\n";
                                } ?>
                            ]
                        };
                    </script>
                </div>
                <? } //history count ?>
                
                <div class="athlete-metric-data athlete-metric-data-edit" style="cursor:pointer" idx="<?=$mid?>">
                    <div class="data">
                        <img src="/img/edit.png" />
                    </div>
                    <div class="title">
                        Edit Data
                    </div>
                </div>
            </div>
        </div>
        
    <?  } //foreach metrics ?>
        <div class="athlete-metric-none hidden">
            <h3>No metrics have been chosen from this category. <a href="#metrics/manage">Manage Metrics</a></h3>
            
        </div>
    </div>
    
</div>
<script type="text/template" id="ath-data-entry-template">
    <div>
    <form class="ath-data-form" method="post">
        <input type="hidden" name="ath-id" value="<?=$ath->getId()?>" />
        <input type="hidden" name="ath-metric" value="{#metric}" />
        <input type="hidden" name="ath-orig-date" value="{#data_date}" />
        <label class="data-label data-label-detail">
            <span class="lbl">{#pretty_data_date}</span>
            <span class="lbl value-update">{#pretty_data_value}</span>
            <span class="lbl">
                <button class="btn btn-small btn-xsmall edit-data-action">Edit</button> &nbsp;
                <button class="btn btn-small btn-xsmall delete-data-action">Delete</button> 
            </span>
        </label>
        <label class="data-label-edit hidden">
            <span class="lbl">{#pretty_data_date}</span>
            <span class="lbl"><input type="number" step="any" name="ath-new-value" value="{#data_value}" class="input input-small input-inline value-update" /></span>
            <span class="lbl">
                <input type="submit" class="btn btn-small btn-xsmall btn-action" value="Save"> &nbsp;
                <button class="btn btn-small btn-xsmall cancel-data-action">Cancel</button> 
            </span>
        </label>
        <label for="frm" class="data-label-confirm hidden data-label-highlight">
            <span class="lbl">{#pretty_data_date}</span>
            <span class="lbl value-update">{#pretty_data_value}</span>
            <span class="lbl">
                Are you sure?<br/>
                <button class="btn btn-small btn-xsmall delete-data-action-confirm">Yes</button>
                &nbsp;
                <button class="btn btn-small btn-xsmall delete-data-action-cancel">No</button> 
            </span>
        </label>
    </form>
    </div>
</script>
<script type="text/template" id="ath-data-form-template">
    <h3>Edit {#label} Entries</h3>
    <form class="ath-data-form" method="post" style="padding-bottom:15px;">
        <input type="hidden" name="ath-id" value="<?=$ath->getId()?>" />
        <input type="hidden" name="ath-metric" value="{#metric}" />
        <label for="ath-new-data">
            New Entry <br/>
            <input type="date" name="ath-new-date" placeholder="Date: YYYY-MM-DD" class="input input-inline" />
            <input type="time" name="ath-new-time" placeholder="Time: HH:MM:SS" class="input input-inline" />
            <input type="number" step="any" name="ath-new-value" placeholder="Value" class="input input-inline" />
        </label>
        <label for="frm">
            <input type="submit" value="Save" class="btn btn-action" />
            &nbsp;
            <input type="button" value="Cancel" class="btn btn-cancel" />
        </label>
    </form>
    <h4>Historic Entries</h4>
    <div class="historic-entries">
    {#data_entries}
    </div>
    
</script>
<script type="text/javascript">


    $('.athlete-metric-data-edit').on("click",function(){
        App.showOverlay();
        
        var metric = $(this).attr('idx');
        
        Api.data = {'metric': metric};
        Api.done = function(data) {
            App.hideOverlay();
            if(data && data.measurements) {
                if(data.measurements.error) {
                    //error
                    console.log(data.measurements.error);
                    return;
                }
                
                var m = data.measurements.metrics;
                var ents = [];
                if(data.measurements.metrics != null) {
                    for(var i=0; i < m.length; i++) {
                        m[i]['metric'] = metric;
                        ents.push($('#ath-data-entry-template').parseTmpl(m[i]).html());
                    }
                }
                
                var frm = $('#ath-data-form-template').parseTmpl({"metric":metric,"label":data.measurements.category,"data_entries":ents.join("\n")});
                
                App.showModal(frm);
                
            }
        }
        
        Api.call('/athlete/measurements/<?=$ath->getId()?>', 'GET');
    });
    
    $('.athlete-metric-data-chart').on("click",function(){
        
        var id = $(this).attr('idx');
        var series_data = chart_data[id];
        
        if(series_data) {
            
            $('#modal-contents').highcharts({
                chart: {
                    type: 'line',
                    width: 550,
                    height: 300
                },
                credits: {
                    enabled:false
                },
                title: {
                    text: series_data.name
                },
                xAxis: {
                    type: 'datetime'
                },
                yAxis: {
                    title: {
                        text: series_data._y_label
                    },
                    min: 0
                },
                tooltip: {
                    formatter: function() {
                            return '<b>'+ this.series.name +'</b><br/>'+
                            Highcharts.dateFormat('%b %e', this.x) +': <b>'+ this.y +' '+ series_data._y_label_short +'</b>';
                    }
                },
                series: [series_data]
            });
            
            App.showModal();
            return;
        }
        
    });
    
    $(document.body).off("submit",".ath-data-form");
    $(document.body).on("submit",".ath-data-form",function(e){
        e.preventDefault();
        
        var row = $(this);
        
        var edit = $(this).find('.data-label-edit');
        var detail = $(this).find('.data-label-detail');
        
        var _data = row.serializeObject();
        
        Api.data = _data;
        Api.done = function(data){
            if(data && data.measurement && data.measurement.data_value) {
                if(data.measurement.error) {
                    console.log(data.measurement.error);
                    return;
                }
                
                if(!_data['ath-orig-date']) {
                    $('.historic-entries').prepend(
                        $('#ath-data-entry-template').parseTmpl({
                            'pretty_data_date':data.measurement.pretty_data_date, 
                            'data_date':data.measurement.data_date, 
                            'pretty_data_value':data.measurement.pretty_data_value,
                            'data_value': data.measurement.data_value,
                            'metric': _data['ath-metric']}));
                    row[0].reset();
                }
                else {
                    row.find('.value-update').each(function(i,el){
                        if($(el).is('input')) {
                            $(el).val(data.measurement.pretty_data_value);
                        }
                        else $(el).html(data.measurement.pretty_data_value);
                    });
                }
                
                App.onModalClose = App.reload;
                
                if(detail.length) detail.removeClass('hidden');
                if(edit.length) edit.addClass('hidden');
                
            }
        }
        Api.call('/athlete/measurement','POST');
    });
    
    $(document.body).off("click",".ath-data-form .btn-cancel");
    $(document.body).on("click",".ath-data-form .btn-cancel",function(e){
        e.preventDefault();
        
        App.hideModal();
        
    });
    
    $(document.body).off("click",".edit-data-action");
    $(document.body).on("click",".edit-data-action",function(e) {
        e.preventDefault();
        
        var detail = $(this).closest('.data-label-detail');
        
        var edit = detail.next('.data-label-edit');
        edit.find('input[name="ath-new-value"]')[0].focus();
        
        detail.addClass('hidden');
        edit.removeClass('hidden');
        
    });
    
    $(document.body).off("click",".cancel-data-action");
    $(document.body).on("click",".cancel-data-action",function(e) {
        e.preventDefault();
        
        
        var edit = $(this).closest('.data-label-edit');
        var detail = edit.prev('.data-label-detail');
        
        detail.removeClass('hidden');
        edit.addClass('hidden');
        
    });

    $(document.body).off("click",".delete-data-action");
    $(document.body).on("click",".delete-data-action",function(e) {
        e.preventDefault();
        
        var detail = $(this).closest('.data-label-detail');
        var edit = detail.nextAll('.data-label-confirm');
        
        detail.addClass('hidden');
        edit.removeClass('hidden');
        
    });
    
    $(document.body).off("click",".delete-data-action-cancel");
    $(document.body).on("click",".delete-data-action-cancel",function(e) {
        e.preventDefault();
        
        var detail = $(this).closest('.data-label-confirm');
        var edit = detail.prevAll('.data-label-detail');
        
        detail.addClass('hidden');
        edit.removeClass('hidden');
        
    });
    
    $(document.body).off("click",".delete-data-action-confirm");
    $(document.body).on("click",".delete-data-action-confirm",function(e) {
        e.preventDefault();
        
        var row = $(this).closest('.ath-data-form');
        
        Api.data = row.serializeObject();
        Api.done = function(data){
            if(data && data.measurement) {
                if(data.measurement.error) {
                    console.log(data.measurement.error);
                    return;
                }
                
                row.remove();
                
                App.onModalClose = App.reload;
            }
        }
        Api.call('/athlete/measurement/delete','POST');
        
    });

    $('.athlete-category').on("click",function(e){
        e.preventDefault();
        
        $('.athlete-category').removeClass("active");
        $(this).addClass('active');
        
        $('.athlete-metric-none').addClass("hidden");
        
        var id = $(this).attr("idx");
        
        if(id == 'all') {
            $('.athlete-metric').removeClass("hidden");
            if($('.athlete-metric').length == 0) {
                $('.athlete-metric-none').removeClass("hidden");
            }
        }
        else {
            $('.athlete-metric').addClass("hidden");
            $('.athlete-metric[catidx="'+ id +'"]').removeClass("hidden");
            
            if($('.athlete-metric[catidx="'+ id +'"]').length == 0) {
                $('.athlete-metric-none').removeClass("hidden");
            }
        }
    });

    App.selectTeam(<?=json_encode($team->getData())?>);
</script>

