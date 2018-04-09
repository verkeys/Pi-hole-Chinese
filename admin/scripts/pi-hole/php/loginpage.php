<?php
/* Pi-hole: A black hole for Internet advertisements
*  (c) 2017 Pi-hole, LLC (https://pi-hole.net)
*  Network-wide ad blocking via your own hardware.
*
*  This file is copyright under the latest version of the EUPL.
*  Please see LICENSE file for your rights under this license. */ ?>

<div class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2" style="float:none">
  <div class="panel panel-default">
    <div class="panel-heading">
      <div style="text-align: center;"><img src="img/logo.svg" width="<?php if ($boxedlayout) { ?>50%<?php } else { ?>30%<?php } ?>"></div><br>

        <div class="panel-title text-center"><span class="logo-lg" style="font-size: 25px;">Pi-<b>hole</b></span></div>
      <p class="login-box-msg">请登录</p>
      <div id="cookieInfo" class="panel-title text-center" style="color:#F00; font-size: 150%" hidden>Verify that cookies are allowed for <samp><?php echo $_SERVER['HTTP_HOST']; ?></samp></div>
      <?php if ($wrongpassword) { ?>
        <div class="form-group has-error login-box-msg">
          <label class="control-label"><i class="fa fa-times-circle-o"></i> 密码错误!</label>
        </div>
      <?php } ?>
    </div>

    <div class="panel-body">
      <form action="" id="loginform" method="post">
        <div class="form-group has-feedback <?php if ($wrongpassword) { ?>has-error<?php } ?> ">
          <input type="password" id="loginpw" name="pw" class="form-control" placeholder="密码" autofocus>
          <span class="fa fa-key form-control-feedback"></span>
        </div>
        <div class="row">
          <div class="col-xs-8">
          <ul>
            <li><samp>回车</samp> &rarr; 登录并转到请求页面 (<?php echo $scriptname; ?>)</li>
            <li><samp>Ctrl+回车</samp> &rarr; 登录并转到设置页面</li>
          </ul>
          </div>
          <div class="col-xs-4">
            <button type="submit" href="#" class="btn btn-primary pull-right"><i class="glyphicon glyphicon-log-in"></i>&nbsp;&nbsp;&nbsp;登录</button>
          </div>
        </div>
        <br>
        <div class="row">
          <div class="col-xs-12">
            <div class="box box-<?php if (!$wrongpassword) { ?>info<?php } else { ?>danger<?php }
            if (!$wrongpassword) { ?> collapsed-box<?php } ?> box-solid">
              <div class="box-header with-border">
                <h3 class="box-title">忘记密码</h3>

                <div class="box-tools pull-right">
                  <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                      class="fa <?php if ($wrongpassword) { ?>fa-minus<?php } else { ?>fa-plus<?php } ?>"></i>
                  </button>
                </div>
              </div>
              <div class="box-body">
               首次安装后会生成密码并显示,如果忘记密码,可通过命令行登录到主机执行一下命令设置新密码
                <pre>sudo pihole -a -p 新密码</pre>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
