<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<?php include('header.php'); ?>
     	<div class="container"><!--container-->
            <div class="login_form container">
                <div class="form_bg">
                        <div class="form_content">
                            <h2>JMarcel</h2>
                                <form id="formlogin" name="formlogin" class="form-horizontal" role="form">
                                  <div class="form-group form1">
                                    <label for="inputEmail3" class="col-sm-4 control-label">Username</label>
                                    <div class="col-sm-5">
                                      <input type="text" class="form-control" id="email" name="email">
                                    </div>
                                  </div>
                                  <div class="form-group form1">
                                    <label for="inputEmail3" class="col-sm-4 control-label">Password</label>
                                    <div class="col-sm-5">
                                      <input type="password" name="password" id="password" class="form-control">
                                      <div id="login_erro" class="alert alert-danger fade in" style="display: none"></div>
                                      <span class="help-block"><a href="recuperar-password.php">Esqueceu-se da password?</a></span>
                                    </div>
                                  </div>
                                </form>
                        <div class="form_bts">
                                <input type="hidden" id="refurl" name="refurl" value="<?php echo $refurl; ?>" /> 
                                <a class="bt_default blue" href="#" onclick="validaDados()">Login</a>
                        </div>
                        </div>

                </div>
            </div>
        </div>
<!-- Start Scripting -->
	<script type="text/javascript">
		
		$(document).ready(function() {
			$('#login_erro').hide();
			$('#formlogin').submit( validaDados );
			
			
			$('#email,#password').keydown(function(event) {
			
				if (event.keyCode == 13)
				{
					event.preventDefault();
					validaDados();
				}
			});

		});
		
		
		function validaDados()
		{
			var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
			var msgErro = "";
			var username = $('#email').val();
			var password = $('#password').val();
//			if(username == "") msgErro += '- <?php //echo LOGIN_MISSING_USERNAME;?>;<br />';
//			if(password == "") msgErro += '- <?php //echo LOGIN_MISSING_PASSWORD;?>;<br />';
//			if(username != "" && !filter.test(username)) msgErro += '- <?php //echo LOGIN_INVALID_USERNAME;?>;<br />';
			
			if(msgErro == "")
			{
				$('#login_erro').hide();
				processForm(username, password);
			}
			else
			{
				$('#login_erro').show();
				$('#login_erro').html('<span>' + msgErro + '</span>');
			}
		
		}
		
		function processForm(username,password)
		{
			var timeOutLoading = setTimeout(function(){
										$("#loading").fadeIn(600)
									}, 3000);
		
			
                       
			$.ajax({
			  type: 'POST',
			  url: 'site/login',
//			  dataType: 'json',
			  data: { username:username, 
			  		  password:password,
			  		  },
                          success: function (data) {
//                              $('#popup_categorias').modal('hide');
//                              window.location ='site/index';
                              alert(data);
                          }
			});
                    }
	</script>
	<!-- End Scripting -->
<?php include('layouts/footer.php'); ?>