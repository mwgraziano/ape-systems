<?

$app = App::init();

$categories = $app->account->getMetricCategories();


?>

<div id="subnavbar">
    <div class="content">
        <h2>Metrics Management</h2>
    </div>
</div>
<div class="content half-content">
    <div id="category-management-form" class="page-top-editor pad-bottom-10">
        <h3>Metric Library</h3>
        <button class="btn create-category-action">Create New Category</button>
        <button class="btn create-metric-action">Create New Metric</button>
    </div>
    <div id="edit-category-form" class="page-top-editor pad-bottom-10 hidden">
        <h3 id="edit-category-title">Edit Category</h3>
        <form id="edit-category">
            <input type="hidden" name="req-key" value="" />
            <input type="hidden" name="category-id" value="" />
            <label for="category-name">
                Category Name
                <input type="text" name="category-name" id="category-name" class="input input-large" value="" />
            </label>
            <label for="frm">
                <input type="submit" class="btn btn-large btn-action" value="Save Category" />
                
                <a href="javascript:void(0)" class="btn btn-large category-form-cancel">Cancel</a>
                
            </label>
        </form>
    </div>
    <div id="edit-metric-form" class="page-top-editor pad-bottom-10 hidden">
        <h3 id="edit-metric-title">Edit Category</h3>
        <form id="edit-metric">
            <input type="hidden" name="req-key" value="" />
            <input type="hidden" name="metric-id" value="" />
            <label for="metric-name">
                Metric Name
                <input type="text" name="metric-name" id="metric-name" class="input input-large" value="" />
            </label>
            <label for="metric-unit">
                Measurement Unit
                <select name="metric-unit" id="metric-unit" class="input input-large">
                    
                </select>
            </label>
            <label for="metric-category">
                Category
                <select name="metric-category" id="metric-category" class="input input-large">
                    <? foreach($categories as $category) { ?>
                        <option value="<?=$category['account_metric_category_id'] .",". $category['metric_category_id']?>"><?=$category['name']?></option>
                    <? } ?>
                </select>
            </label>
            <label for="frm">
                <input type="submit" class="btn btn-large btn-action" value="Save Metric" />
                
                <a href="javascript:void(0)" class="btn btn-large metric-form-cancel">Cancel</a>
                
            </label>
        </form>
    </div>
    <div id="delete-category-form" class="page-top-editor  pad-bottom-10 hidden">
        <h3 id="delete-category-title"></h3>
        <form id="delete-category">
            <input type="hidden" name="req-key" value="" />
            <input type="hidden" name="category-id" value="" />
            
            <p id="delete-category-message">Are you sure you want to delete this category?</p>
            
            <label for="frm">
                <input type="submit" class="btn btn-large btn-action" value="Delete Category" />
                
                <a href="javascript:void(0)" class="btn btn-large category-form-cancel">Cancel</a>
                
            </label>
        </form>
    </div>
<?
    foreach($categories as $category) {
        
        $metrics = $app->account->getMetricCategoryMetrics($category['account_metric_category_id'],$category['metric_category_id']);
        
?>
    <div class="expandable-header contracted" idx="<?=$category['account_metric_category_id']?>,<?=$category['metric_category_id']?>">
        <?=$category['name']?>
    </div>
    
    <div class="expandable-content hidden">
        <div class="expandable-content-toolbar">
            <div class="contents">
                <button class="btn btn-small btn-xsmall active edit-category-action" idx="<?=$category['account_metric_category_id']?>,<?=$category['metric_category_id']?>">Edit Category</button> &nbsp;
                <? if(!empty($category['account_metric_category_id']) && empty($metrics)) { ?> 
                    <button class="btn btn-small btn-xsmall delete-category-action" idx="<?=$category['account_metric_category_id']?>,<?=$category['metric_category_id']?>">Delete Category</button>
                <? } ?>
            </div>
            <div class="both"></div>
        </div>
<?
        foreach((array)$metrics as $metric) {
            
            if(is_a($metric, "AccountMetric")) {
                $amid = $metric->getId();
                $mid = $metric->getMetricId();
            }
            else {
                $amid = "";
                $mid = $metric->getId();
            }
?>
        <div class="metric-manage">
            <?=$metric->getName()?>
            <button class="btn btn-small btn-xsmall right edit-metric-action" idx="<?=$amid .','. $mid?>">Edit Metric</button>
            <div class="both"></div>
        </div>

<?      } //foreach metrics?>
    </div>
    
<? } //foreach categories ?>
</div>
<div class="content half-content">
    <div class="page-top-editor pad-bottom-10">
        <h3>Team Metric Management</h3>
    </div>
    
<?
    $teams = Team::findId_Name_SportId(array("account_id"=>$app->account->id));
    
    foreach($teams as $team) {
        $metric_ids = TeamMetric::findMetricId_AccountMetricId(array("team_id"=>$team['id']));
        
?>
    <div class="expandable-header contracted" idx="<?=$team['id']?>">
        <?=$team['name']?>
    </div>
    
    <div class="expandable-content hidden">
<?
        foreach((array)$metric_ids as $metric) {
            
            if(!empty($metric['account_metric_id'])) $m = AccountMetric::load($metric['account_metric_id']);
            else $m = Metric::load($metric['metric_id']);
            
            if(empty($m)) continue;
            
?>
        <div class="metric-manage">
            <?=$m->getName()?>
            <button class="btn btn-small btn-xsmall right remove-metric-action" tidx="<?=$team['id']?>" idx="<?=$metric['account_metric_id'] .','. $metric['metric_id']?>">Remove Metric</button>
            <div class="both"></div>
        </div>

<?      } //foreach metric_ids?>

        <div class="metric-manage" style="border:2px dashed silver">
            <div class="assoc-metric-label" idx="<?=$team['id']?>">
                Associate Metric
            </div>
            <div class="assoc-metric-form hidden">
                <form class="assoc-metric">
                    <input type="hidden" name="team-id" value="<?=$team['id']?>" />
                    <select name="metric-category" class="assoc-metric-category input"></select>
                    <span class="assoc-metric-form-2 hidden">
                        <select name="metric-id" class="input"></select>
                        <input type="submit" class="btn btn-small btn-xsmall btn-action" value="Save" />
                        &nbsp;
                        <button class="btn btn-small btn-xsmall assoc-metric-cancel">Cancel</button>
                    </span>
                </form>
            </div>
        </div>
        <div class="metric-manage" style="border:2px dashed silver">
            <div class="assoc-metric-label">
                Copy another team's metrics
            </div>
            <div class="assoc-metric-form hidden">
                <form class="copy-team-form">
                    This will remove any metrics you already have associated to this team.
                    <input type="hidden" name="team-id" value="<?=$team['id']?>" />
                    <select name="copy-team-id" class="input">
                        <option value="">Select a team...</option>
                    <? foreach($teams as $_team) { 
                        if($_team['id'] == $team['id']) continue; ?>
                        <option value="<?=$_team['id']?>"><?=$_team['name']?></option>
                    <? } ?>
                    </select>
                    <input type="submit" class="btn btn-small btn-xsmall btn-action" value="Save" />
                    &nbsp;
                    <button class="btn btn-small btn-xsmall assoc-metric-cancel">Cancel</button>
                </form>
            </div>
        </div>
        
        <div class="metric-manage" style="border:2px dashed silver">
            <div class="assoc-metric-label" idx="<?=$team['id']?>" data-action="key-metric">
                Key Metrics
            </div>
            <div class="assoc-metric-form hidden">
                <form class="key-metric-form">
                    Choose up to 4 key metrics
                    <input type="hidden" name="team-id" value="<?=$team['id']?>" />
                    <select name="key-metric-1" class="input">
                        
                    </select>
                    <select name="key-metric-2" class="input">
                        
                    </select>
                    <select name="key-metric-3" class="input">
                        
                    </select>
                    <select name="key-metric-4" class="input">
                        
                    </select>
                    <input type="submit" class="btn btn-small btn-xsmall btn-action" value="Save" />
                    &nbsp;
                    <button class="btn btn-small btn-xsmall key-metric-cancel">Cancel</button>
                </form>
            </div>
        </div>
    </div>
    
<? } //foreach teams ?>
    
</div>

<script type="text/javascript">

    function loadForm(frm, data) {
        var inputs = frm.find('input, select');
        for(var i =0; i<inputs.length;i++) {
            if($(inputs[i]).attr('type') != 'submit') {
                if(data.hasOwnProperty($(inputs[i]).attr('name'))) $(inputs[i]).val(data[$(inputs[i]).attr('name')]);
                else $(inputs[i]).val("");
            }
        }
    }

    $('.expandable-header').on("click",function(){
        
        var content = $(this).next('.expandable-content');
        
        if(content) {
            if(content.hasClass('hidden')) {
                content.removeClass('hidden');
                $(this).removeClass('contracted');
                $(this).addClass('expanded');
            }
            else {
                content.addClass('hidden');
                $(this).addClass('contracted');
                $(this).removeClass('expanded');
            }
        }
    });
    
    $('.edit-category-action').on('click',function() {
        
        if($('#page-overlay')) {
            $('#page-overlay').removeClass("hidden");
        }
        
        Api.data = {'id': $(this).attr('idx')};
        Api.done = function(data) {
            if($('#page-overlay')) {
                $('#page-overlay').addClass('hidden');
            }
            
            if(data && data.category) {
                if(data.category.error){
                    //error
                }
                else {
                    $('#edit-category-title').html("Edit Category: "+ data.category['category-name']);
                    loadForm($('#edit-category'), data.category);
                }
            }
            else {
                //error
            }
        };
        
        Api.call('/metrics/category','GET');
        
        $('#edit-category-form').removeClass('hidden');
        $('#delete-category-form').addClass('hidden');
        App.scrollTop();
    });
    
    $('.delete-category-action').on('click',function(){
        
        if($('#page-overlay')) {
            $('#page-overlay').removeClass("hidden");
        }
        
        Api.data = {'id': $(this).attr('idx')};
        Api.done = function(data) {
            if($('#page-overlay')) {
                $('#page-overlay').addClass('hidden');
            }
            
            if(data && data.category) {
                if(data.category.error){
                    //error
                }
                else {
                    $('#delete-category-title').html("Delete Category: "+ data.category['category-name']);
                    loadForm($('#delete-category'), data.category);
                }
            }
            else {
                //error
            }
        };
        
        Api.call('/metrics/category','GET');
        
        $('#delete-category-form').removeClass('hidden');
        $('#edit-category-form').addClass('hidden');
        App.scrollTop();
    });
    
    $('.create-category-action').on('click',function(){
        $('#edit-category-title').html("Create New Category");
        $('#edit-category-form').removeClass('hidden');
        loadForm($('#edit-category'),{});
    });
    
    
    $('.category-form-cancel').on("click",function(){
        $('#edit-category-form').addClass('hidden');
        $('#delete-category-form').addClass('hidden');
    });
    
    $('.create-metric-action').on('click',function(){
        App.showOverlay();
        
        $('#metric-unit').find("option").remove();
        
        Api.data = {'id': $(this).attr('idx')};
        Api.done = function(data) {
            
            App.hideOverlay();
            
            if(data && data.units) {
                if(data.units.error){
                    //error
                }
                else {
                    var units = data.units;
                    
                    for(var i=0;i<units.length;i++) {
                        $('<option value="'+ units[i].id +'">'+ units[i].name +'</option>').appendTo($('#metric-unit'));
                    }
                    
                    $('#edit-metric-title').html("Create New Metric");
                    
                    loadForm($('#edit-metric'), {});
                    $('#edit-metric-form').removeClass('hidden');
                    $('#delete-metric-form').addClass('hidden');
                    App.scrollTop();
                }
            }
            else {
                //error
            }
        };
        
        Api.call('/metrics/units','GET');
        
    });
    
    $('.metric-form-cancel').on("click",function(){
        $('#edit-metric-form').addClass('hidden');
        $('#delete-metric-form').addClass('hidden');
    });
    
    $('#edit-category').on('submit',function(e) {
        e.preventDefault();
        
        var data = $(this).serializeObject();
        
        if($.trim(data['category-name']) == "") {
            App.addFormError($(this).find('input[name="category-name"]'), "You must enter a category name.");
            return false;
        }
        
        if($('#page-overlay')) {
            $('#page-overlay').removeClass('hidden');
        }
        
        Api.data = $(this).serializeObject();
        
        Api.done = function(data){
            
            if(data) {
                if(data.category.error) {
                    //error
                    if($('#page-overlay')) {
                        $('#page-overlay').addClass('hidden');
                    }
                    
                }
                else App.reload();
            }
            else {
                //error
                if($('#page-overlay')) {
                    $('#page-overlay').addClass('hidden');
                }
            }
        }
        
        Api.call('/metrics/category','POST');
    });
    
    $('#delete-category').on('submit',function(e) {
        e.preventDefault();
        
        var data = $(this).serializeObject();
        
        if($('#page-overlay')) {
            $('#page-overlay').removeClass('hidden');
        }
        
        Api.data = $(this).serializeObject();
        
        Api.done = function(data){
            
            if(data) {
                if(data.category.error) {
                    //error
                    if($('#page-overlay')) {
                        $('#page-overlay').addClass('hidden');
                    }
                    
                }
                else App.reload();
            }
            else {
                //error
                if($('#page-overlay')) {
                    $('#page-overlay').addClass('hidden');
                }
            }
        }
        
        Api.call('/metrics/category/delete','POST');
    });
    
    $('.edit-metric-action').on("click", function() {
        App.showOverlay();
        
        $('#metric-unit').find("option").remove();
        
        Api.data = {'id': $(this).attr('idx')};
        Api.done = function(data) {
            
            App.hideOverlay();
            
            if(data && data.edit) {
                if(data.edit.error){
                    //error
                }
                else {
                    var metric = data.edit.metric;
                    var units = data.edit.units;
                    
                    for(var i=0;i<units.length;i++) {
                        $('<option value="'+ units[i].id +'">'+ units[i].name +'</option>').appendTo($('#metric-unit'));
                    }
                    
                    $('#edit-metric-title').html("Edit Metric: "+ metric['metric-name']);
                    loadForm($('#edit-metric'), metric);
                }
            }
            else {
                //error
            }
        };
        
        Api.call('/metrics/edit','GET');
        
        $('#edit-metric-form').removeClass('hidden');
        $('#delete-metric-form').addClass('hidden');
        App.scrollTop();
    });
    
    $('#edit-metric').on('submit',function(e) {
        e.preventDefault();
        
        var data = $(this).serializeObject();
        
        if($.trim(data['metric-name']) == "") {
            App.addFormError($(this).find('input[name="metric-name"]'), "You must enter a metric name.");
            return false;
        }
        
        if($.trim(data['metric-unit']) == "") {
            App.addFormError($(this).find('select[name="metric-unit"]'), "You must select a unit.");
            return false;
        }
        
        if($.trim(data['metric-category']) == "") {
            App.addFormError($(this).find('select[name="metric-category"]'), "You must select a category.");
            return false;
        }
        
        App.showOverlay();
        
        Api.data = $(this).serializeObject();
        
        Api.done = function(data){
            
            if(data) {
                if(data.edit.error) {
                    //error
                    App.hideOverlay();
                    
                }
                else App.reload();
            }
            else {
                //error
                App.hideOverlay();
            }
        }
        
        Api.call('/metrics/edit','POST');
    });
    
    $('.assoc-metric-label').on('click',function(){
        $('.assoc-metric-label').next(".assoc-metric-form").addClass('hidden');
        
        var frm = $(this).next(".assoc-metric-form");
        
        if(!frm.hasClass("hidden")) return;
        
        App.showOverlay();
        
        if(!$(this).attr('idx')) {
            frm.removeClass('hidden');
            App.hideOverlay();
            return;
        }
        
        if($(this).attr('data-action') == 'key-metric') {
            
            var sel1 = frm.find('select[name="key-metric-1"]');
                sel1.find('option').remove();
            var sel2 = frm.find('select[name="key-metric-2"]');
                sel2.find('option').remove();
            var sel3 = frm.find('select[name="key-metric-3"]');
                sel3.find('option').remove();
            var sel4 = frm.find('select[name="key-metric-4"]');
                sel4.find('option').remove();
                
            
            Api.data = {};
            Api.done = function(data) {
                App.hideOverlay()
                
                if(data && data.key_metrics) {
                    m = data.key_metrics;
                    
                    if(m.error) {
                        //error
                        console.log("error");
                        return;
                    }
                    
                    if(!m.metrics || !m.metrics.length) {
                        //error
                        console.log("no metrics available");
                        return;
                    }
                    
                    $('<option value="">Select key metric 1...</option>').appendTo(sel1);
                    $('<option value="">Select key metric 2...</option>').appendTo(sel2);
                    $('<option value="">Select key metric 3...</option>').appendTo(sel3);
                    $('<option value="">Select key metric 4...</option>').appendTo(sel4);
                    
                    for(var i=0; i<m.metrics.length; i++) {
                        $('<option value="'+ m.metrics[i].account_metric_id +','+ m.metrics[i].metric_id +'">'+ m.metrics[i].name +'</option>').appendTo(sel1);
                        $('<option value="'+ m.metrics[i].account_metric_id +','+ m.metrics[i].metric_id +'">'+ m.metrics[i].name +'</option>').appendTo(sel2);
                        $('<option value="'+ m.metrics[i].account_metric_id +','+ m.metrics[i].metric_id +'">'+ m.metrics[i].name +'</option>').appendTo(sel3);
                        $('<option value="'+ m.metrics[i].account_metric_id +','+ m.metrics[i].metric_id +'">'+ m.metrics[i].name +'</option>').appendTo(sel4);
                    }
                    
                    if(m.values && m.values.length) {
                        if(m.values[0]) {
                            sel1.val(m.values[0]);
                        }
                        if(m.values[1]) {
                            sel2.val(m.values[1]);
                        }
                        if(m.values[2]) {
                            sel3.val(m.values[2]);
                        }
                        if(m.values[3]) {
                            sel4.val(m.values[3]);
                        }
                    }
                    
                    frm.removeClass("hidden");
                }
            }
            Api.call("/team/key_metrics/"+ $(this).attr("idx"), "GET");
            
        }
        else {
            
            var sel = frm.find('select[name="metric-category"]');
                sel.find('option').remove();
        
            Api.data = {};
            Api.done = function(data) {
                if($('#page-overlay')) {
                    $('#page-overlay').addClass('hidden');
                }
                
                if(data && data.available_metrics_categories) {
                    m = data.available_metrics_categories;
                    
                    if(m.error) {
                        //error
                        console.log("error");
                        return;
                    }
                    
                    if(!m.length) {
                        //error
                        console.log("no metrics available");
                    }
                    
                    $('<option value="">Select a Category</option>').appendTo(sel);
                    
                    for(var i=0; i<m.length; i++) {
                        $('<option value="'+ m[i].account_metric_category_id +','+ m[i].metric_category_id +'">'+ m[i].name +'</option>').appendTo(sel);
                    }
                    
                    frm.removeClass("hidden");
                }
            }
            Api.call("/team/available_metrics_categories/"+ $(this).attr("idx"), "GET");
        }
        
    });
    
    $('.assoc-metric-category').on('change',function(){
        
        if($(this).val() == "") return;
        
        var frm = $(this).closest(".assoc-metric-form");
        
        var sel = frm.find('select[name="metric-id"]');
            sel.find('option').remove();
            
        var idx = $(this).prev('input[name="team-id"]').val();
        
        var sect = $(this).next('.assoc-metric-form-2');
        
        if($('#page-overlay')) {
            $('#page-overlay').removeClass('hidden');
        }
        
        Api.data = {'id': $(this).val() };
        Api.done = function(data) {
            if($('#page-overlay')) {
                $('#page-overlay').addClass('hidden');
            }
            
            if(data && data.available_metrics) {
                m = data.available_metrics;
                
                if(m.error) {
                    //error
                    console.log("error");
                    return;
                }
                
                if(!m.length) {
                    //error
                    console.log("no metrics available");
                }
                
                for(var i=0; i<m.length; i++) {
                    $('<option value="'+ m[i].account_metric_id +','+ m[i].metric_id +'">'+ m[i].name +'</option>').appendTo(sel);
                }
                
                sect.removeClass("hidden");
            }
        }
        Api.call("/team/available_metrics/"+ idx, "GET");
        
    });
    
    $('.assoc-metric-cancel').on("click",function(e){
        e.preventDefault();
        var frm = $(this).closest('.assoc-metric-form');
            frm.find('.assoc-metric-form-2').addClass("hidden");
            frm.addClass("hidden");
    });
    
    $('.key-metric-cancel').on("click",function(e){
        e.preventDefault();
        var frm = $(this).closest('.assoc-metric-form');
            frm.addClass("hidden");
    });
    
    $('.assoc-metric').on("submit",function(e){
        e.preventDefault();
        
        var data = $(this).serializeObject();
        
        var containr = $(this).closest('.metric-manage');
        
        if(data['metric-id'] == "") {
            App.addFormError($(this).find('select[name="metric-id"]'), "You must select a metric.");
            return false;
        }
        
        App.showOverlay();
        
        Api.data = data;
        
        var mid = data['metric-id'];
        var sel = $(this).find('select[name="metric-id"]');
        var tid = data['team-id'];
        
        Api.done = function(ret) {
            App.hideOverlay();
            
            if(ret && ret.add_metric) {
                if(ret.add_metric.error) {
                    //error
                    console.log(ret.add_metric.error);
                    return false;
                }
                
                sel.find('option[value="'+ mid+'"]').remove();
                
                $('<div class="metric-manage">'+ ret.add_metric.name +' \
                   <button class="btn btn-small btn-xsmall right remove-metric-action" tidx="'+ tid +'" idx="'+ ret.add_metric.account_metric_id +','+ ret.add_metric.metric_id +'">Remove Metric</button> \
                   <div class="both"></div></div>').insertBefore(containr);
                
                listenRemoveMetric();
            }
        }
        
        Api.call("/team/add_metric/"+ tid, "POST");
    });
    
    $('.copy-team-form').on('submit',function(e){
        e.preventDefault();
        
        var data = $(this).serializeObject();
        
        if(data['copy-team-id'] == "") {
            App.addFormError($(this).find('select[name="copy-team-id"]'), "Please select a team");
            return;
        }
        
        Api.data = data;
        Api.done = function(ret) {
            if(ret && ret.copy_metrics) {
                if(ret.copy_metrics.error) {
                    //error
                    console.log(ret.copy_metrics.error);
                    return;
                }
                else {
                    App.reload();
                    return;
                }
            }
            
            //error
            console.log("major error");
        }
        
        Api.call("/team/copy_metrics/"+ data['team-id'],"POST");
    });
    
    $('.key-metric-form').on('submit',function(e){
        e.preventDefault();
        
        var data = $(this).serializeObject();
        
        Api.data = data;
        Api.done = function(ret) {
            if(ret && ret.key_metrics) {
                if(ret.key_metrics.error) {
                    //error
                    console.log(ret.key_metrics.error);
                    return;
                }
                
                App.reload();
                return;
            }
            
            //error
            console.log("major error");
        }
        Api.call("/team/key_metrics/"+ data['team-id'], "POST");
    });
    
    function listenRemoveMetric() {
        $('.remove-metric-action').off("click");
        $('.remove-metric-action').on("click",function(e){
            e.preventDefault();
            
            var id = $(this).attr('idx');
            if(!id) return;
            
            var tid = $(this).attr('tidx');
            if(!tid) return;
            
            var mm = $(this).closest('.metric-manage');
            
            $('.assoc-metric-cancel').click();
            
            App.showOverlay();
            Api.data = {"metric-id": id};
            Api.done = function(ret) {
                App.hideOverlay();
                if(ret && ret.remove_metric){
                    if(ret.remove_metric.error) {
                        //error
                        console.log(ret.remove_metric.error);
                        return;
                    }
                    
                    mm.remove();
                }
            }
            
            Api.call("/team/remove_metric/"+ tid, "POST");
        });
    }
    
    $(function(){
        listenRemoveMetric();
    });
</script>
