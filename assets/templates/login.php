<?php
/**
 * Created by IntelliJ IDEA.
 * User: mngibso
 * Date: 4/21/16
 * Time: 10:46 AM
 */
?>
<a name="login-form"></a>
<table id="fma-register-form" class="form-table _fma_login <?php
//REGISTER
if(!\FindMyAudience::$NewInstall) { if(\FindMyAudience\FMA_Global::isFMAUserLoggedIn() === TRUE )  echo 'fma-hidden'; }?>" style="margin-left:15px; display:none;">
    <tr>
        <th scope="row" ><label for="fma_blog_categories">Name (first, last)</label></th>
        <td width="150px" >
            <input style="width:100%;" name="Setting[fma_first_name]" value="<?php
            echo $user_data->user_firstname?>" type="text" id="fma_first_name" value="" class="regular-text" />
        </td>
        <td width="150px" >
            <input style="width:100%;" name="Setting[fma_last_name]" value="<?php echo $user_data->user_lastname?>" type="text" id="fma_last_name" value="" class="regular-text" />
        </td>
        <td></td>
    </tr>
    <!-- LOGIN -->
    <tr>
        <th scope="row"><label for="fma_login_user">FMA Login</label></th>
        <td colspan="2" width="300px"><input  value="<?php if(!\FindMyAudience::$NewInstall) { echo \FindMyAudience\FMA_Global::decryptString(get_user_option('fma_login_user')); } else { echo $user_data->user_email; } ?>" 
                                              name="fma_login_user" type="text" id="fma_login_user" class="regular-text" /></td>
        <td>
        </td>
    </tr>
    <!-- LOGIN -->
    <tr>
        <th scope="row"><label for="fma_login_pass">FMA Password</label></th>
        <td colspan="2" width="300px"><input onkeyup="verifyFMAPassword();" value="" name="fma_login_pass" type="password" id="fma_login_pass" class="regular-text" /></td>
        <td></td>
    </tr>
    <!-- REGISTER -->
    <tr>
        <th scope="row"><label for="fma_login_confirm">Confirm Password</label></th>
        <td colspan="2" width="300px"><input onkeyup="verifyFMAPassword();" value="" type="password" id="fma_login_confirm" class="regular-text" />
            <div style="margin:2px;margin-top:5px;font-size:12px;padding:2px;" class="fma-error" id="fma-login-error"></div>
        </td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td colspan="3">
            <div>
                <input type="button" value="Register with FMA" class="button-primary" onclick="registerFMAUser('<?php echo $_SERVER['REQUEST_URI']?>')"><a 
                    style="padding-left:20px;" href="#login-form" onclick="javascript:showLoginForm();">If you've already registered with FMA, click here to login</a>
            </div>
        </td>
    </tr>
</table>
<table id="fma-login-form" class="form-table _fma_login" style="margin-left:15px;display:none;">
    <tr>
        <th scope="row"><label for="fma_login_user">FMA Login</label></th>
        <td colspan="2" width="300px"><input  
                value="<?php if(!\FindMyAudience::$NewInstall) { echo \FindMyAudience\FMA_Global::decryptString(get_user_option('fma_login_user')); } else { echo $user_data->user_email; } ?>" 
                name="fma_login_user_login" type="text" id="fma_login_user_login" class="regular-text" /></td>
        <td>
        </td>
    </tr>
    <!-- LOGIN -->
    <tr>
        <th scope="row"><label for="fma_login_pass">FMA Password</label></th>
        <td colspan="2" width="300px"><input onkeyup="verifyFMAPassword();" value="" name="fma_login_pass_login" type="password" 
        id="fma_login_pass_login" class="regular-text" /></td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td colspan="3">
            <div>
                <input type="button" value="Login to FMA" class="button-primary" onclick="loginFMAUser('<?php echo $_SERVER['REQUEST_URI']?>')">&nbsp;&nbsp;<input type="button" value="Cancel" class="button" onclick="showRegisterForm();">
            </div>
        </td>
    </tr>
</table>

