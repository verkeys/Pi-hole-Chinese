<?php /*
*    Pi-hole: A black hole for Internet advertisements
*    (c) 2017 Pi-hole, LLC (https://pi-hole.net)
*    Network-wide ad blocking via your own hardware.
*
*    This file is copyright under the latest version of the EUPL.
*    Please see LICENSE file for your rights under this license. */
    $indexpage = true;
    require "scripts/pi-hole/php/header.php";
?>
<!-- Small boxes (Stat box) -->
<div class="row">
    <div class="col-lg-3 col-xs-12">
        <!-- small box -->
        <div class="small-box bg-green">
            <div class="inner">
                <p>解析次数 (<span id="unique_clients">-</span> 客户端)</p>
                <h3 class="statistic"><span id="dns_queries_today">---</span></h3>
            </div>
            <div class="icon">
                <i class="ion ion-earth"></i>
            </div>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-xs-12">
        <!-- small box -->
        <div class="small-box bg-aqua">
            <div class="inner">
                <p>拦截次数</p>
                <h3 class="statistic"><span id="ads_blocked_today">---</span></h3>
            </div>
            <div class="icon">
                <i class="ion ion-android-hand"></i>
            </div>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-xs-12">
        <!-- small box -->
        <div class="small-box bg-yellow">
            <div class="inner">
                <p>拦截比例</p>
                <h3 class="statistic"><span id="ads_percentage_today">---</span></h3>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
        </div>
    </div>
<?php
$gravitylist = "/etc/pihole/gravity.list";
if (file_exists($gravitylist))
{
    $gravitydiff = date_diff(date_create("@".filemtime($gravitylist)),date_create("now"));
    if($gravitydiff->d > 1)
        $gravitydate = $gravitydiff->format("Blocking list updated %a days, %H:%I ago");
    elseif($gravitydiff->d == 1)
        $gravitydate = $gravitydiff->format("Blocking list updated one day, %H:%I ago");
    else
        $gravitydate = $gravitydiff->format("Blocking list updated %H:%I ago");
}
else
{
    $gravitydate = "Blocking list not found";
}
?>
    <!-- ./col -->
    <div class="col-lg-3 col-xs-12">
        <!-- small box -->
        <div class="small-box bg-red" title="<?php echo $gravitydate; ?>">
            <div class="inner">
                <p>黑名单域名</p>
                <h3 class="statistic"><span id="domains_being_blocked">---</span></h3>
            </div>
            <div class="icon">
                <i class="ion ion-ios-list"></i>
            </div>
        </div>
    </div>
    <!-- ./col -->
</div>

<div class="row">
    <div class="col-md-12">
    <div class="box" id="queries-over-time">
        <div class="box-header with-border">
          <h3 class="box-title">24小时解析统计</h3>
        </div>
        <div class="box-body">
          <div class="chart">
            <canvas id="queryOverTimeChart" width="800" height="140"></canvas>
          </div>
        </div>
        <div class="overlay">
          <i class="fa fa-refresh fa-spin"></i>
        </div>
        <!-- /.box-body -->
      </div>
    </div>
</div>
<?php
  // If the user is logged in, then we show the more detailed index page.
  // Even if we would include them here anyhow, there would be nothing to
  // show since the API will respect the privacy of the user if he defines
  // a password
  if($auth){ ?>

<div class="row">
    <div class="col-md-12">
    <div class="box" id="clients">
        <div class="box-header with-border">
          <h3 class="box-title">客户端解析统计</h3>
        </div>
        <div class="box-body">
          <div class="chart">
            <canvas id="clientsChart" width="800" height="140"></canvas>
          </div>
        </div>
        <div class="overlay">
          <i class="fa fa-refresh fa-spin"></i>
        </div>
        <!-- /.box-body -->
      </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 col-lg-6">
    <div class="box" id="query-types-pie">
        <div class="box-header with-border">
          <h3 class="box-title">解析类型统计</h3>
        </div>
        <div class="box-body">
          <div class="chart">
            <canvas id="queryTypePieChart" width="400" height="150"></canvas>
          </div>
        </div>
        <div class="overlay">
          <i class="fa fa-refresh fa-spin"></i>
        </div>
        <!-- /.box-body -->
      </div>
    </div>
    <div class="col-md-12 col-lg-6">
    <div class="box" id="forward-destinations-pie">
        <div class="box-header with-border">
          <h3 class="box-title">域名服务器响应统计</h3>
        </div>
        <div class="box-body">
          <div class="chart">
            <canvas id="forwardDestinationPieChart" width="400" height="150"></canvas>
          </div>
        </div>
        <div class="overlay">
          <i class="fa fa-refresh fa-spin"></i>
        </div>
        <!-- /.box-body -->
      </div>
    </div>
</div>
<?php
  // Determine if "Query Types (over time)" should be shown
  $queryTypesOverTime = false;
  if(isset($setupVars['DASHBOARD_SHOW_QUERY_TYPES_OVER_TIME']))
  {
    if($setupVars['DASHBOARD_SHOW_QUERY_TYPES_OVER_TIME'])
    {
        $queryTypesOverTime = true;
    }
  }

  // Determine if "Forward Destinations (over time)" should be shown
  $forwardDestsOverTime = false;
  if(isset($setupVars['DASHBOARD_SHOW_FORWARD_DESTS_OVER_TIME']))
  {
    if($setupVars['DASHBOARD_SHOW_FORWARD_DESTS_OVER_TIME'])
    {
        $forwardDestsOverTime = true;
    }
  }
?>
<?php if($forwardDestsOverTime || $queryTypesOverTime) { ?>
<div class="row">
<?php if($queryTypesOverTime) { ?>
    <div class="col-md-12 col-lg-6">
    <div class="box" id="query-types">
        <div class="box-header with-border">
          <h3 class="box-title">Query Types (over time)</h3>
        </div>
        <div class="box-body">
          <div class="chart">
            <canvas id="queryTypeChart" width="400" height="150"></canvas>
          </div>
        </div>
        <div class="overlay">
          <i class="fa fa-refresh fa-spin"></i>
        </div>
        <!-- /.box-body -->
      </div>
    </div>
<?php } if($forwardDestsOverTime) { ?>
    <div class="col-md-12 col-lg-6">
    <div class="box" id="forward-destinations">
        <div class="box-header with-border">
          <h3 class="box-title">Forward Destinations (over time)</h3>
        </div>
        <div class="box-body">
          <div class="chart">
            <canvas id="forwardDestinationChart" width="400" height="150"></canvas>
          </div>
        </div>
        <div class="overlay">
          <i class="fa fa-refresh fa-spin"></i>
        </div>
        <!-- /.box-body -->
      </div>
    </div>
<?php } ?>
</div>
<?php } ?>

<?php
if($boxedlayout)
{
	$tablelayout = "col-md-6";
}
else
{
	$tablelayout = "col-md-6 col-lg-4";
}
?>
<div class="row">
    <div class="<?php echo $tablelayout; ?>">
      <div class="box" id="domain-frequency">
        <div class="box-header with-border">
          <h3 class="box-title">解析域名统计</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                  <tbody>
                    <tr>
                    <th>域名</th>
                    <th>命中缓存次数</th>
                    <th>频率</th>
                    </tr>
                  </tbody>
                </table>
            </div>
        </div>
        <div class="overlay">
          <i class="fa fa-refresh fa-spin"></i>
        </div>
        <!-- /.box-body -->
      </div>
      <!-- /.box -->
    </div>
    <!-- /.col -->
    <div class="<?php echo $tablelayout; ?>">
      <div class="box" id="ad-frequency">
        <div class="box-header with-border">
          <h3 class="box-title">黑名单排行</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                  <tbody>
                    <tr>
                    <th>域名</th>
                    <th>命中次数</th>
                    <th>频率</th>
                    </tr>
                  </tbody>
                </table>
            </div>
        </div>
        <div class="overlay">
          <i class="fa fa-refresh fa-spin"></i>
        </div>
        <!-- /.box-body -->
      </div>
      <!-- /.box -->
    </div>
    <!-- /.col -->
    <div class="<?php echo $tablelayout; ?>">
      <div class="box" id="client-frequency">
        <div class="box-header with-border">
          <h3 class="box-title">客户端统计</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                  <tbody>
                    <tr>
                    <th>客户端</th>
                    <th>请求次数</th>
                    <th>频率</th>
                    </tr>
                  </tbody>
                </table>
            </div>
        </div>
        <div class="overlay">
          <i class="fa fa-refresh fa-spin"></i>
        </div>
        <!-- /.box-body -->
      </div>
      <!-- /.box -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->
<?php } ?>
<?php
    require "scripts/pi-hole/php/footer.php";
?>

<script src="scripts/pi-hole/js/index.js"></script>
