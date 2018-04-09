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
    <h1>在例表中查询广告域名</h1>
</div>
<!-- Domain Input -->
<div class="form-group input-group">
    <input id="domain" type="text" class="form-control" placeholder="要查找的域名(主域名或二级域名)">
	<input id="quiet" type="hidden" value="no">
    <span class="input-group-btn">
        <button id="btnSearch" class="btn btn-default" type="button">搜索部分匹配</button>
        <button id="btnSearchExact" class="btn btn-default" type="button">搜索精准匹配</button>
    </span>
</div>

<pre id="output" style="width: 100%; height: 100%;" hidden="true"></pre>

<?php
    require "scripts/pi-hole/php/footer.php";
?>


<script src="scripts/pi-hole/js/queryads.js"></script>
