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
                                <a class="bt_default blue" href="javascript:validaDados();">Login</a>
                        </div>
                        </div>

                </div>
            </div>
        </div>
<?php include('footer.php'); ?>