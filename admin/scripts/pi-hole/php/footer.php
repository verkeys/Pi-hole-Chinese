<?php
/* Pi-hole: A black hole for Internet advertisements
*  (c) 2017 Pi-hole, LLC (https://pi-hole.net)
*  Network-wide ad blocking via your own hardware.
*
*  This file is copyright under the latest version of the EUPL.
*  Please see LICENSE file for your rights under this license. */ ?>

        </section>
        <!-- /.content -->
    </div>
    <!-- Modal for custom disable time -->
    <div class="modal fade" id="customDisableModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">自定义停止时间</h4>
                </div>
                <div class="modal-body">
                    <div class="input-group">
                        <input id="customTimeout" class="form-control" type="number" value="60">
                            <div class="input-group-btn" data-toggle="buttons">
                                <label class="btn btn-default">
                                    <input type="radio"/> 秒
                                </label>
                                <label id="btnMins" class="btn btn-default active">
                                    <input type="radio"  /> 分
                                </label>
                            </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                    <button  id="pihole-disable-custom" type="button" class="btn btn-primary" data-dismiss="modal">提交</button>
                </div>
            </div>
        </div>
    </div>
<?php
  // Flushes the system write buffers of PHP. This attempts to push everything we have so far all the way to the client's browser.
  flush();
  // Run update checker
  //  - determines local branch each time,
  //  - determines local and remote version every 30 minutes
  require "scripts/pi-hole/php/update_checker.php";
?>
    <!-- /.content-wrapper -->
    <footer class="main-footer">
	<!-- Version Infos -->
        <div class="pull-right hidden-xs hidden-sm<?php if(isset($core_commit) || isset($web_commit) || isset($FTL_commit)) { ?> hidden-md<?php } ?>">
            <b>Pi-hole 版本 </b> <?php
            echo $core_current;
            if(isset($core_commit)) { echo " (".$core_branch.", ".$core_commit.")"; }
            if($core_update){ ?> <a class="alert-link lookatme" href="https://github.com/pi-hole/pi-hole/releases" target="_blank">(Update available!)</a><?php } ?>
            <b>Web Interface 版本 </b><?php
            echo $web_current;
            if(isset($web_commit)) { echo " (".$web_branch.", ".$web_commit.")"; }
            if($web_update){ ?> <a class="alert-link lookatme" href="https://github.com/pi-hole/AdminLTE/releases" target="_blank">(Update available!)</a><?php } ?>
            <b>FTL 版本 </b> <?php
            echo $FTL_current;
            if(isset($FTL_commit)) { echo " (".$FTL_branch.", ".$FTL_commit.")"; }
            if($FTL_update){ ?> <a class="alert-link lookatme" href="https://github.com/pi-hole/FTL/releases" target="_blank">(有新版本更新!)</a><?php } ?>
			<a href="https://github.com/verkeys/Pi-hole-Chinese target="_blank"> 汉化:Verkey</a>
        </div>
        <div style="display: inline-block"><strong><a href="https://pi-hole.net/donate" target="_blank"><i class="fa fa-heart"></i> 捐赠</a></strong> 如果你发现本项目对你有帮助的话,请点击左侧捐赠按钮,我们会非常感谢！</div>
    </footer>
</div>
<!-- ./wrapper -->
<script src="scripts/vendor/jquery.min.js"></script>
<script src="scripts/vendor/jquery-ui.min.js"></script>
<script src="style/vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="scripts/vendor/app.min.js"></script>

<script src="scripts/vendor/jquery.dataTables.min.js"></script>
<script src="scripts/vendor/dataTables.bootstrap.min.js"></script>
<script src="scripts/vendor/Chart.bundle.min.js"></script>

<script src="scripts/pi-hole/js/footer.js"></script>

</body>
</html>
