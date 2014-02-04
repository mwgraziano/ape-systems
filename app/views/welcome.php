<? include("head.php"); ?>
        <div id="login-container">
            <div class="login-logo">
                <img src="/img/ape_systems_logo.gif" />
            </div>
            <form id="login-form" onsubmit="return false;">
                <input type="hidden" name="tz_offset" id="tz_offset" value="" />
                <input type="text" placeholder="Username" title="Username" name="username" id="username" class="input input-large" />
                <input type="password" placeholder="Password" title="Password" name="password" id="password" class="input input-large" />
                <input type="submit" value="Login" class="btn btn-large btn-action" id="login-submit" /> 
            </form>
        </div>
        <script type="text/javascript">
            $(document).ready(function(){
                $('#login-form').on("submit",function(e){
                    e.preventDefault();
                    
                    $('.error').remove();
                    
                    if($.trim($('#username').val()) == '' || $.trim($('#password').val()) == '') {
                        $('<div class="error error-large">Please enter both a username and password.</div>').fadeIn('fast').appendTo($('#login-form')).delay(3000).fadeOut('slow');
                        return;
                    }
                    
                    $('#login-submit').addClass('disabled');
                    $('#login-submit').val('Processing...');
                    
                    $('#tz_offset').val(Util.getTimezoneOffset());
                    
                    Api.done = function(data) {
                        if(data.index && data.index == 'OK') window.location.href = "/app";
                        else {
                            $('<div class="error error-large">Incorrect username or password.</div>').fadeIn('fast').appendTo($('#login-form')).delay(3000).fadeOut('slow');
                            $('#login-submit').removeClass('disabled');
                            $('#login-submit').val('Login');
                            return;
                        }
                    }
                    Api.data = $('#login-form').serializeObject();
                    Api.call('/login/','POST');
                });
            });
        </script>
<? include("foot.php"); ?>
