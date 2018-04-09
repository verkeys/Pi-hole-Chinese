<?php /*
*    Pi-hole: A black hole for Internet advertisements
*    (c) 2017 Pi-hole, LLC (https://pi-hole.net)
*    Network-wide ad blocking via your own hardware.
*
*    This file is copyright under the latest version of the EUPL.
*    Please see LICENSE file for your rights under this license. */
    require "scripts/pi-hole/php/header.php";
?>
<!-- Title -->
<div class="page-header">
    <h1>更新广告域名列表服务器 / 黑名单 / 黑名单</h1>
</div>

<!-- Alerts -->
<div id="alInfo" class="alert alert-info alert-dismissible fade in" role="alert" hidden="true">
    <button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    更新中...
</div>
<div id="alSuccess" class="alert alert-success alert-dismissible fade in" role="alert" hidden="true">
    <button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    成功!
</div>

<button class="btn btn-lg btn-primary btn-block" id="gravityBtn">更新列表</button>
<pre id="output" style="width: 100%; height: 100%;" hidden="true"></pre>

<?php
    require "scripts/pi-hole/php/footer.php";
?>


<script src="scripts/pi-hole/js/gravity.js"></script>
