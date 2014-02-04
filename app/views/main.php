<?php

$app = App::init();

$account = $app->account;
$user = $app->user;

$logo = $account->getLogo();

$teams = Team::findId_Name_SportId(array("account_id"=>$account->id));

$current_team = Team::load(Session::getTeamId());
if($current_team) $team_data = $current_team->getData();
else $team_data = false;

include("head.php");

?>
    <div id="navbar">
        <div class="full-content">
            <div id="left-panel-discover">
                <button id="panel-open" class="btn btn-small">
                    <span class="nav-btn-line"></span>
                    <span class="nav-btn-line"></span>
                    <span class="nav-btn-line"></span>
                </button>
            </div>

            <img src="<?=$logo?>" />
            
            <div class="team-selected">
                <a href="#roster" class="nav-link nav-link-large nav-item team-title" data-nav="true" hidden-no-team="true"></a>
            </div>

            <div class="org-selected">
                <a href="#org/edit" class="nav-link nav-link-large nav-item org-title" data-nav="true"></a>
            </div>
        </div>
    </div>
    <div id="page-wrapper">
        <div id="left-panel">
            <div id="nav-panel">
                <ul>
                    
                    <!--<li class="nav-item title" hidden-no-team="true" data-nav="true"><a href="#team/switch" class="team-title"></a></li>
                    <li class="nav-item" hidden-no-team="true" data-nav="true"><a href="#roster">Roster</a></li>
                    <li class="nav-item" hidden-no-team="true" data-nav="true"><a href="#training">Training</a></li>
                    <li class="nav-item" hidden-no-team="true" data-nav="true"><a href="#team/<?=Session::getTeamId()?>">Edit Team</a></li>
                    <li class="separator" hidden-no-team="true"></li> -->
                    
                    <li class="nav-static">
                        <input type="text" id="athlete-search" placeholder="Search Athletes" />
                        <button class="btn invisible" id="athlete-search-clear">x</button>
                    </li>
                        <ul id="athlete-search-results" class="hidden">
                            
                        </ul>
                    
                    <li class="nav-item" data-nav="true"><a href="javascript:void(0);" id="team-expand" class="contracted">Teams</a></li>
                        <ul id="team-expand-menu" class="hidden">
                            <li class="nav-item nav-item-light nav-item-small"><a href="#team">Add New</a></li>
                            <? foreach($teams as $team) { ?>
                                <li class="nav-item nav-item-light nav-item-small"><a href="#team/switch/<?=$team['id']?>"><?=$team['name']?></a></li>
                            <? } ?>
                        </ul>
                        
                    <li class="nav-item" data-nav="true"><a href="javascript:void(0)" id="metrics-expand" class="contracted">Metrics</a></li>
                        <ul id="metrics-expand-menu" class="hidden">
                            <!--<li class="nav-item nav-item-light nav-item-small"><a href="#metrics">Test</a></li>-->
                            <li class="nav-item nav-item-light nav-item-small"><a href="#metrics/manage">Management</a></li>
                        </ul>
                        
                    <li class="nav-item" data-nav="true"><a href="#org/edit">Admin</a></li>
                    
                    <li class="nav-item"><a href="/logout">Logout</a></li>
                    <? if($app->isAuth() >= AUTH_LEVEL_SUPERADMIN) { ?>
                        <li class="nav-item" data-nav="true"><a href="#admin">Super Admin</a></li>
                    <? } ?>
                </ul>
            </div>
            
        </div>
        <div id="page-container">
            
            <div id="page">
                
                <? include_once(VIEW_PATH . "switch_team.php"); ?>
                
            </div>
            
            
            <div id="page-overlay" class="page-overlay hidden">
                <div id="page-overlay-contents">
                    <img src="/img/loader.gif" />
                </div>
            </div>
            
            <div id="gray-overlay" class="gray-overlay hidden">
                <div class="modal">
                    <a href="javascript:void(0);" class="modal-close-button close-modal">x</a>
                    <div class="contents" id="modal-contents">
                    </div>
                </div>
            </div>
            
            
            
        </div>
        
    </div>
    
    <script type="text/javascript">
        
        $('#panel-open').on('click', function() {
            if($('#left-panel').hasClass('active')) App.hidePanel();
            else App.showPanel();
        });
        
        $('.nav-item[data-nav]').on("click", function(e) {
            if($(this).hasClass('active')) return App.hidePanel();
            
            setTimeout(function() { App.hidePanel(e); }, 100);
        });
        
        $('#team-expand').on('click',function(e) {
            e.preventDefault();
            e.stopPropagation();
            if($('#team-expand-menu').hasClass('hidden')) {
                $('#team-expand-menu').removeClass('hidden');
                $(this).removeClass('contracted');
                $(this).addClass('expanded');
            }
            else {
                $('#team-expand-menu').addClass('hidden');
                $(this).addClass('contracted');
                $(this).removeClass('expanded');
            }
        });
        
        $('.nav-item-light').on('click',function() {
            $(this).closest('ul').addClass('hidden');
        });
        
        $('#metrics-expand').on('click',function(e) {
            e.preventDefault();
            e.stopPropagation();
            if($('#metrics-expand-menu').hasClass('hidden')) {
                $('#metrics-expand-menu').removeClass('hidden');
                $(this).removeClass('contracted');
                $(this).addClass('expanded');
            }
            else {
                $('#metrics-expand-menu').addClass('hidden');
                $(this).addClass('contracted');
                $(this).removeClass('expanded');
            }
        });
        
        $('#athlete-search-clear').on("click",function(){
            App.showPanel();
            $('#athlete-search').val("");
            $('#athlete-search').keyup();
            $('#athlete-search')[0].focus();
        });
        
        var athlete_req_timer = null;
        
        $('#athlete-search').on('keyup',function() {
            
            if(athlete_req_timer) {
                clearTimeout(athlete_req_timer);
                athlete_req_timer = null;
            }
            
            if($.trim($(this).val()) == '') {
                $('#athlete-search-results li').remove();
                $('#athlete-search-results').addClass('hidden');
                $('#athlete-search-clear').addClass('invisible');
                return;
            }
            
            Api.data = {'q':$.trim($(this).val())};
            Api.done = function(data) {
                if($.trim($('#athlete-search').val()) == "") return;
                if(data.search && data.search.length) {
                    $('#athlete-search-results li').remove();
                    for(var i = 0; i < data.search.length; i++) {
                        $('<li class="nav-item nav-item-light"><a href="#athlete/profile/'+ data.search[i].id +'">'+ data.search[i].name +'</a></li>').appendTo($('#athlete-search-results'));
                    }
                    $('#athlete-search-results').removeClass('hidden');
                    $('#athlete-search-clear').removeClass('invisible');
                }
                else {
                    $('#athlete-search-results li').remove();
                    $('#athlete-search-results').addClass('hidden');
                    $('#athlete-search-clear').addClass('invisible');
                }
            }
            
            athlete_req_timer = setTimeout(function(){
                Api.call('/athlete/search/','GET');
            }, 300);
        });
        
        App.selectOrg(<?=json_encode($app->account->getData())?>);
        
        App.selectTeam(<?=json_encode($team_data)?>);
       
    </script>


<? include("foot.php"); ?>
