var App = { //Main App object

	section: null,
	
	init: function()
	{
	    App.noTeam();
	    
		//Routing function
        $(window).on('hashchange',function() {
            App.checkHash();
        });
        
        App.checkHash();
	}, 
	
	validateEmails: function(emails)
	{
		var ret = [];
		var patt = /^[a-z0-9\.\-\_\'\$\+]{1,}@[a-z0-9\-]{2,}\.([a-z]{1,5}\.?){1,}$/i;
		for(var i=0; i<emails.length; i++)
		{
			if(patt.test(emails[i])) ret.push(emails[i]);
		}
		
		return ret;
	},
	
	hidePanel: function(e) {
	    if(e && !$(e.target).closest('li').hasClass('nav-item') && $(e.target).closest('#left-panel').length >= 1) {
	        $(document).off('click',App.hidePanel);
	        $(document).one('click',App.hidePanel);
	    }
        else {
            $('#left-panel').removeClass('active');
            $('#panel-open').removeClass('active');
        }
	},
	
	showPanel: function() {
	    $('#left-panel').addClass('active');
        $('#panel-open').addClass('active');
        
        $(document).off('click',App.hidePanel);
        
        setTimeout(function(){ $(document).one('click',App.hidePanel); }, 100);
	},
	
	loadContent: function(section) {
	    if(!section) return;
	    if(this.current_request) this.current_request.abort();

	    if($('#login-form').length) window.location.href = "/logout";

	    Api.done = function(data) {
	        if(typeof data == 'Object' || data == '') {
	            //Shouldn't have an object, this should be content
	            $('#page').html("Error loading content: "+ section);
	        }
	        else {
	        	App.section = section;
	        	if($(data).find('#login-form').length) window.location.href = "/logout";
	            else $('#page').html(data);
	        }
	        
	        if($('#page-overlay')) {
                $('#page-overlay').addClass("hidden");
            }
            
            window.location.href = '#'+ section;
	    };
	    
	    Api.data = {};
	    
	    if($('#page-overlay')) {
	        $('#page-overlay').removeClass("hidden");
	    }
	    
	    this.current_request = Api.call('/'+ section,'GET');
	},
	reload: function() {
		App.loadContent(App.section);
	},
	refresh: function(section) {
	    window.location.hash = section;
	    window.location.reload();
	},
	back: function() {
	    window.history.back();
	},
	checkHash: function() {
	    var link = $("a[href='"+ window.location.hash +"']");
	    //if(link.length > 0) {
            App.loadContent(window.location.hash.replace("#",""));
            $('.nav-item[data-nav]').removeClass('active');
            //if(link.closest('li').length) link.closest('li').addClass('active');
        //}
	},
	
	noTeam: function() {
	    $('[hidden-no-team=true]').addClass("hidden");
	    $('[show-no-team=true]').removeClass("hidden");
	},
	
	selectTeam: function(newteam) {
	    this.team = newteam;
	    if(!this.team) return this.noTeam();
	    
	    setTimeout(function(){
	        $('[hidden-no-team=true]').removeClass("hidden");
            $('[show-no-team=true]').addClass("hidden");
	    }, 200);
        
        $('.team-title').html(this.team.name);
	},

	selectOrg: function(org) {
		this.org = org;
		if(!this.org) return;

		$('.org-title').html(this.org.name);
	},
	
	liveColorChange: function() {
	    var colors = {};
	    $('input.color').each(function(i,el){
	        var nm = $(el).attr('idx');
	        if(nm)
	           colors[nm] = $(el).val();
	    });
	    
	    less.modifyVars(colors);
	},

	addFormError: function(el, msg) {
		lbl = el.closest('label');
		if(lbl.length == 0) lbl = el.parent();

		$('<div class="error">'+ msg +'</div>').fadeIn('fast').appendTo(lbl).delay(3000).fadeOut('slow');
		
	},

	removeFormErrors: function(frm) {
		frm.find('div.error').remove();
	},
	
	scrollTop: function() {
	    
	    $('#page-container').animate({
	        scrollTop: 0
	    }, 400);
	},
	
	showOverlay: function() {
	    if($('#page-overlay')) {
            $('#page-overlay').removeClass('hidden');
        }
	},
	
    hideOverlay: function(){
        if($('#page-overlay')) {
            $('#page-overlay').addClass('hidden');
        }
    },
    
    showModal: function(content, cb) {
        App.onModalClose = cb;
        if($('#gray-overlay').length) {
            if(content) {
                $('#gray-overlay').find(".contents").html("");
                content.appendTo($('#gray-overlay').find(".contents"));
            }
            $('#gray-overlay').removeClass('hidden');
        }
    },
    
    hideModal: function() {
        if($('#gray-overlay').length) {
            $('#gray-overlay').addClass('hidden');
        }
        
        if(App.onModalClose) App.onModalClose();
    },

};

var Api = {
    done: false,
	data: {},
	call: function(url, type)
	{
		var _this = this;
		var aft   = this.done;
		var _data = this.data;
		
		this.done = false;
		this.data = false;
		
		return $.ajax({
		    
		    "url": url,
		    "data": _data,
		    "type": type
		    
		}).done(function(data){
		    
		    if(data.api) {
		    
		        if(data.api.redirect) return window.location.href = data.api.redirect;
		        
		    }
		    
		    if(aft) aft(data);
			
		});
		
	}
	
};


$(function(){
	App.init();
	
	$.fn.serializeObject = function() {
      var arrayData, objectData;
      arrayData = this.serializeArray();
      objectData = {};
    
      $.each(arrayData, function() {
        var value;
    
        if (this.value != null) {
          value = this.value;
        } else {
          value = '';
        }
    
        if (objectData[this.name] != null) {
          if (!objectData[this.name].push) {
            objectData[this.name] = [objectData[this.name]];
          }
    
          objectData[this.name].push(value);
        } else {
          objectData[this.name] = value;
        }
      });
    
      return objectData;
    };

    $.fn.parseTmpl = function(obj) {

    	var html = this.html();

    	for(var i in obj) {
    		if(obj.hasOwnProperty(i)) {
    			var pregex = new RegExp("\{\#"+ i +"\}","g");
    			html = html.replace(pregex, obj[i]);
    		}
    	}

    	html = html.replace(/\{\#[a-z\_\-]+\}/g, '');
    	return $(html);
    };
    
    $(document.body).on('click','.close-modal',function(e) {
        e.preventDefault();
        App.hideModal();
    });
    
});

(function(e){var t=function(){"use strict";var e="s",n=2011,r=function(e){var t=-e.getTimezoneOffset();return t!==null?t:0},i=function(e,t,n){var r=new Date;return e!==undefined&&r.setFullYear(e),r.setDate(n),r.setMonth(t),r},s=function(e){return r(i(e,0,2))},o=function(e){return r(i(e,5,2))},u=function(e){var t=e.getMonth()>7?o(e.getFullYear()):s(e.getFullYear()),n=r(e);return t-n!==0},a=function(){var t=s(n),r=o(n),i=t-r;return i<0?t+",1":i>0?r+",1,"+e:t+",0"},f=function(){var e=a();return new t.TimeZone(t.olson.timezones[e])},l=function(e){var t=new Date(2010,6,15,1,0,0,0),n={"America/Denver":new Date(2011,2,13,3,0,0,0),"America/Mazatlan":new Date(2011,3,3,3,0,0,0),"America/Chicago":new Date(2011,2,13,3,0,0,0),"America/Mexico_City":new Date(2011,3,3,3,0,0,0),"America/Asuncion":new Date(2012,9,7,3,0,0,0),"America/Santiago":new Date(2012,9,3,3,0,0,0),"America/Campo_Grande":new Date(2012,9,21,5,0,0,0),"America/Montevideo":new Date(2011,9,2,3,0,0,0),"America/Sao_Paulo":new Date(2011,9,16,5,0,0,0),"America/Los_Angeles":new Date(2011,2,13,8,0,0,0),"America/Santa_Isabel":new Date(2011,3,5,8,0,0,0),"America/Havana":new Date(2012,2,10,2,0,0,0),"America/New_York":new Date(2012,2,10,7,0,0,0),"Asia/Beirut":new Date(2011,2,27,1,0,0,0),"Europe/Helsinki":new Date(2011,2,27,4,0,0,0),"Europe/Istanbul":new Date(2011,2,28,5,0,0,0),"Asia/Damascus":new Date(2011,3,1,2,0,0,0),"Asia/Jerusalem":new Date(2011,3,1,6,0,0,0),"Asia/Gaza":new Date(2009,2,28,0,30,0,0),"Africa/Cairo":new Date(2009,3,25,0,30,0,0),"Pacific/Auckland":new Date(2011,8,26,7,0,0,0),"Pacific/Fiji":new Date(2010,10,29,23,0,0,0),"America/Halifax":new Date(2011,2,13,6,0,0,0),"America/Goose_Bay":new Date(2011,2,13,2,1,0,0),"America/Miquelon":new Date(2011,2,13,5,0,0,0),"America/Godthab":new Date(2011,2,27,1,0,0,0),"Europe/Moscow":t,"Asia/Yekaterinburg":t,"Asia/Omsk":t,"Asia/Krasnoyarsk":t,"Asia/Irkutsk":t,"Asia/Yakutsk":t,"Asia/Vladivostok":t,"Asia/Kamchatka":t,"Europe/Minsk":t,"Pacific/Apia":new Date(2010,10,1,1,0,0,0),"Australia/Perth":new Date(2008,10,1,1,0,0,0)};return n[e]};return{determine:f,date_is_dst:u,dst_start_for:l}}();t.TimeZone=function(e){"use strict";var n={"America/Denver":["America/Denver","America/Mazatlan"],"America/Chicago":["America/Chicago","America/Mexico_City"],"America/Santiago":["America/Santiago","America/Asuncion","America/Campo_Grande"],"America/Montevideo":["America/Montevideo","America/Sao_Paulo"],"Asia/Beirut":["Asia/Beirut","Europe/Helsinki","Europe/Istanbul","Asia/Damascus","Asia/Jerusalem","Asia/Gaza"],"Pacific/Auckland":["Pacific/Auckland","Pacific/Fiji"],"America/Los_Angeles":["America/Los_Angeles","America/Santa_Isabel"],"America/New_York":["America/Havana","America/New_York"],"America/Halifax":["America/Goose_Bay","America/Halifax"],"America/Godthab":["America/Miquelon","America/Godthab"],"Asia/Dubai":["Europe/Moscow"],"Asia/Dhaka":["Asia/Yekaterinburg"],"Asia/Jakarta":["Asia/Omsk"],"Asia/Shanghai":["Asia/Krasnoyarsk","Australia/Perth"],"Asia/Tokyo":["Asia/Irkutsk"],"Australia/Brisbane":["Asia/Yakutsk"],"Pacific/Noumea":["Asia/Vladivostok"],"Pacific/Tarawa":["Asia/Kamchatka"],"Pacific/Tongatapu":["Pacific/Apia"],"Africa/Johannesburg":["Asia/Gaza","Africa/Cairo"],"Asia/Baghdad":["Europe/Minsk"]},r=e,i=function(){var e=n[r],i=e.length,s=0,o=e[0];for(;s<i;s+=1){o=e[s];if(t.date_is_dst(t.dst_start_for(o))){r=o;return}}},s=function(){return typeof n[r]!="undefined"};return s()&&i(),{name:function(){return r}}},t.olson={},t.olson.timezones={"-720,0":"Pacific/Majuro","-660,0":"Pacific/Pago_Pago","-600,1":"America/Adak","-600,0":"Pacific/Honolulu","-570,0":"Pacific/Marquesas","-540,0":"Pacific/Gambier","-540,1":"America/Anchorage","-480,1":"America/Los_Angeles","-480,0":"Pacific/Pitcairn","-420,0":"America/Phoenix","-420,1":"America/Denver","-360,0":"America/Guatemala","-360,1":"America/Chicago","-360,1,s":"Pacific/Easter","-300,0":"America/Bogota","-300,1":"America/New_York","-270,0":"America/Caracas","-240,1":"America/Halifax","-240,0":"America/Santo_Domingo","-240,1,s":"America/Santiago","-210,1":"America/St_Johns","-180,1":"America/Godthab","-180,0":"America/Argentina/Buenos_Aires","-180,1,s":"America/Montevideo","-120,0":"America/Noronha","-120,1":"America/Noronha","-60,1":"Atlantic/Azores","-60,0":"Atlantic/Cape_Verde","0,0":"UTC","0,1":"Europe/London","60,1":"Europe/Berlin","60,0":"Africa/Lagos","60,1,s":"Africa/Windhoek","120,1":"Asia/Beirut","120,0":"Africa/Johannesburg","180,0":"Asia/Baghdad","180,1":"Europe/Moscow","210,1":"Asia/Tehran","240,0":"Asia/Dubai","240,1":"Asia/Baku","270,0":"Asia/Kabul","300,1":"Asia/Yekaterinburg","300,0":"Asia/Karachi","330,0":"Asia/Kolkata","345,0":"Asia/Kathmandu","360,0":"Asia/Dhaka","360,1":"Asia/Omsk","390,0":"Asia/Rangoon","420,1":"Asia/Krasnoyarsk","420,0":"Asia/Jakarta","480,0":"Asia/Shanghai","480,1":"Asia/Irkutsk","525,0":"Australia/Eucla","525,1,s":"Australia/Eucla","540,1":"Asia/Yakutsk","540,0":"Asia/Tokyo","570,0":"Australia/Darwin","570,1,s":"Australia/Adelaide","600,0":"Australia/Brisbane","600,1":"Asia/Vladivostok","600,1,s":"Australia/Sydney","630,1,s":"Australia/Lord_Howe","660,1":"Asia/Kamchatka","660,0":"Pacific/Noumea","690,0":"Pacific/Norfolk","720,1,s":"Pacific/Auckland","720,0":"Pacific/Tarawa","765,1,s":"Pacific/Chatham","780,0":"Pacific/Tongatapu","780,1,s":"Pacific/Apia","840,0":"Pacific/Kiritimati"},typeof exports!="undefined"?exports.jstz=t:e.jstz=t})(this);

var Util = 
{
    jstz: this.jstz,

    getTimezoneOffset: function()
    {
        var d = new Date();
        var offset = (d.getTimezoneOffset() / 60) * (-1);
        var tz = this.jstz.determine().name();
        return [offset,tz];
    }
};