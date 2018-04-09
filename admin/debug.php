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
    <h1>生成调试日志</h1>
</div>

<p><input type="checkbox" id="upload" checked>完成时上传调试日志并提供日志查询码</p>
<p>单击此按钮后, 将生成调试日志, 如果已链接到互联网, 会自动上传到pi-hole官网,日志保存48小时,如遇到问题请及时到官网反馈,反馈时请提供日志查询码.</p>
<button class="btn btn-lg btn-primary btn-block" id="debugBtn">生成调试日志</button>
<pre id="output" style="width: 100%; height: 100%;" hidden="true"></pre>

<?php
    require "scripts/pi-hole/php/footer.php";
?>


<script src="scripts/pi-hole/js/debug.js"></script>
