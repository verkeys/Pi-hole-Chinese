<?php /*
*    Pi-hole: A black hole for Internet advertisements
*    (c) 2017 Pi-hole, LLC (https://pi-hole.net)
*    Network-wide ad blocking via your own hardware.
*
*    This file is copyright under the latest version of the EUPL.
*    Please see LICENSE file for your rights under this license. */
require "scripts/pi-hole/php/header.php";
require "scripts/pi-hole/php/savesettings.php";
// Reread ini file as things might have been changed
$setupVars = parse_ini_file("/etc/pihole/setupVars.conf");

?>
<style type="text/css">
    .tooltip-inner {
        max-width: none;
        white-space: nowrap;
    }

    @-webkit-keyframes Pulse {
        from {
            color: #630030;
            -webkit-text-shadow: 0 0 9px #333;
        }
        50% {
            color: #e33100;
            -webkit-text-shadow: 0 0 18px #e33100;
        }
        to {
            color: #630030;
            -webkit-text-shadow: 0 0 9px #333;
        }
    }

    p.lookatme {
        -webkit-animation-name: Pulse;
        -webkit-animation-duration: 2s;
        -webkit-animation-iteration-count: infinite;
    }
</style>

<?php // Check if ad lists should be updated after saving ...
if (isset($_POST["submit"])) {
    if ($_POST["submit"] == "saveupdate") {
        // If that is the case -> refresh to the gravity page and start updating immediately
        ?>
        <meta http-equiv="refresh" content="1;url=gravity.php?go">
    <?php }
} ?>

<?php if (isset($debug)) { ?>
    <div id="alDebug" class="alert alert-warning alert-dismissible fade in" role="alert">
        <button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
        </button>
        <h4><i class="icon fa fa-warning"></i> Debug</h4>
        <pre><?php print_r($_POST); ?></pre>
    </div>
<?php } ?>

<?php if (strlen($success) > 0) { ?>
    <div id="alInfo" class="alert alert-info alert-dismissible fade in" role="alert">
        <button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
        </button>
        <h4><i class="icon fa fa-info"></i> Info</h4>
        <?php echo $success; ?>
    </div>
<?php } ?>

<?php if (strlen($error) > 0) { ?>
    <div id="alError" class="alert alert-danger alert-dismissible fade in" role="alert">
        <button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
        </button>
        <h4><i class="icon fa fa-ban"></i> Error</h4>
        <?php echo $error; ?>
    </div>
<?php } ?>


<?php
// Networking
if (isset($setupVars["PIHOLE_INTERFACE"])) {
    $piHoleInterface = $setupVars["PIHOLE_INTERFACE"];
} else {
    $piHoleInterface = "unknown";
}
if (isset($setupVars["IPV4_ADDRESS"])) {
    $piHoleIPv4 = $setupVars["IPV4_ADDRESS"];
} else {
    $piHoleIPv4 = "unknown";
}
$IPv6connectivity = false;
if (isset($setupVars["IPV6_ADDRESS"])) {
    $piHoleIPv6 = $setupVars["IPV6_ADDRESS"];
    sscanf($piHoleIPv6, "%2[0-9a-f]", $hexstr);
    if (strlen($hexstr) == 2) {
        // Convert HEX string to number
        $hex = hexdec($hexstr);
        // Global Unicast Address (2000::/3, RFC 4291)
        $GUA = (($hex & 0x70) === 0x20);
        // Unique Local Address   (fc00::/7, RFC 4193)
        $ULA = (($hex & 0xfe) === 0xfc);
        if ($GUA || $ULA) {
            // Scope global address detected
            $IPv6connectivity = true;
        }
    }
} else {
    $piHoleIPv6 = "unknown";
}
$hostname = trim(file_get_contents("/etc/hostname"), "\x00..\x1F");
?>

<?php
// DNS settings
$DNSservers = [];
$DNSactive = [];

$i = 1;
while (isset($setupVars["PIHOLE_DNS_" . $i])) {
    if (isinserverlist($setupVars["PIHOLE_DNS_" . $i])) {
        array_push($DNSactive, $setupVars["PIHOLE_DNS_" . $i]);
    } elseif (strpos($setupVars["PIHOLE_DNS_" . $i], ".")) {
        if (!isset($custom1)) {
            $custom1 = $setupVars["PIHOLE_DNS_" . $i];
        } else {
            $custom2 = $setupVars["PIHOLE_DNS_" . $i];
        }
    } elseif (strpos($setupVars["PIHOLE_DNS_" . $i], ":")) {
        if (!isset($custom3)) {
            $custom3 = $setupVars["PIHOLE_DNS_" . $i];
        } else {
            $custom4 = $setupVars["PIHOLE_DNS_" . $i];
        }
    }
    $i++;
}

if (isset($setupVars["DNS_FQDN_REQUIRED"])) {
    if ($setupVars["DNS_FQDN_REQUIRED"]) {
        $DNSrequiresFQDN = true;
    } else {
        $DNSrequiresFQDN = false;
    }
} else {
    $DNSrequiresFQDN = true;
}

if (isset($setupVars["DNS_BOGUS_PRIV"])) {
    if ($setupVars["DNS_BOGUS_PRIV"]) {
        $DNSbogusPriv = true;
    } else {
        $DNSbogusPriv = false;
    }
} else {
    $DNSbogusPriv = true;
}

if (isset($setupVars["DNSSEC"])) {
    if ($setupVars["DNSSEC"]) {
        $DNSSEC = true;
    } else {
        $DNSSEC = false;
    }
} else {
    $DNSSEC = false;
}

if (isset($setupVars["DNSMASQ_LISTENING"])) {
    if ($setupVars["DNSMASQ_LISTENING"] === "single") {
        $DNSinterface = "single";
    } elseif ($setupVars["DNSMASQ_LISTENING"] === "all") {
        $DNSinterface = "all";
    } else {
        $DNSinterface = "local";
    }
} else {
    $DNSinterface = "single";
}
?>

<?php
// Query logging
if (isset($setupVars["QUERY_LOGGING"])) {
    if ($setupVars["QUERY_LOGGING"] == 1) {
        $piHoleLogging = true;
    } else {
        $piHoleLogging = false;
    }
} else {
    $piHoleLogging = true;
}
?>

<?php
// Excluded domains in API Query Log call
if (isset($setupVars["API_EXCLUDE_DOMAINS"])) {
    $excludedDomains = explode(",", $setupVars["API_EXCLUDE_DOMAINS"]);
} else {
    $excludedDomains = [];
}

// Exluded clients in API Query Log call
if (isset($setupVars["API_EXCLUDE_CLIENTS"])) {
    $excludedClients = explode(",", $setupVars["API_EXCLUDE_CLIENTS"]);
} else {
    $excludedClients = [];
}

// Exluded clients
if (isset($setupVars["API_QUERY_LOG_SHOW"])) {
    $queryLog = $setupVars["API_QUERY_LOG_SHOW"];
} else {
    $queryLog = "all";
}

// Privacy Mode
if (isset($setupVars["API_PRIVACY_MODE"])) {
    $privacyMode = $setupVars["API_PRIVACY_MODE"];
} else {
    $privacyMode = false;
}

?>

<?php
if (isset($_GET['tab']) && in_array($_GET['tab'], array("sysadmin", "blocklists", "dns", "piholedhcp", "api", "teleporter"))) {
    $tab = $_GET['tab'];
} else {
    $tab = "sysadmin";
}
?>
<div class="row justify-content-md-center">
    <div class="col-md-12">
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li<?php if($tab === "sysadmin"){ ?> class="active"<?php } ?>><a data-toggle="tab" href="#sysadmin">系统配置</a></li>
                <li<?php if($tab === "blocklists"){ ?> class="active"<?php } ?>><a data-toggle="tab" href="#blocklists">黑名单设置</a></li>
                <li<?php if($tab === "dns"){ ?> class="active"<?php } ?>><a data-toggle="tab" href="#dns">DNS服务器设置</a></li>
                <li<?php if($tab === "piholedhcp"){ ?> class="active"<?php } ?>><a data-toggle="tab" href="#piholedhcp">DHCP服务器设置</a></li>
                <li<?php if($tab === "api"){ ?> class="active"<?php } ?>><a data-toggle="tab" href="#api">API / Web界面</a></li>
                <li<?php if($tab === "teleporter"){ ?> class="active"<?php } ?>><a data-toggle="tab" href="#teleporter">Teleporter</a></li>
            </ul>
            <div class="tab-content">
                <!-- ######################################################### Blocklists ######################################################### -->
                <div id="blocklists" class="tab-pane fade<?php if($tab === "blocklists"){ ?> in active<?php } ?>">
                    <form role="form" method="post">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="box">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Lists used to generate Pi-hole's Gravity</h3>
                                    </div>
                                    <div class="box-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered dt-responsive nowrap">
                                                <thead>
                                                <tr>
                                                    <th>开启</th>
                                                    <th>列表</th>
                                                    <th style="width:1%">删除</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($adlist as $key => $value) { ?>
                                                        <tr>
                                                            <td>
                                                                <input type="checkbox" name="adlist-enable-<?php echo $key; ?>" <?php if ($value[0]){ ?>checked<?php } ?>>
                                                            </td>
                                                            <td>
                                                                <a href="<?php echo htmlentities($value[1]); ?>" target="_new" id="adlist-text-<?php echo $key; ?>"><?php echo htmlentities($value[1]); ?></a>
                                                            </td>
                                                            <td class="text-center">
                                                                <button class="btn btn-danger btn-xs" id="adlist-btn-<?php echo $key; ?>">
                                                                    <span class="glyphicon glyphicon-trash"></span>
                                                                </button>
                                                                <input type="checkbox" name="adlist-del-<?php echo $key; ?>" hidden>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="form-group">
                                            <textarea name="newuserlists" class="form-control" rows="1" placeholder="Enter one URL per line to add new ad lists"></textarea>
                                        </div>
                                        <input type="hidden" name="field" value="adlists">
                                        <input type="hidden" name="token" value="<?php echo $token ?>">
                                    </div>
                                    <div class="box-footer clearfix">
                                        <button type="submit" class="btn btn-primary" name="submit" value="save" id="blockinglistsave">保存</button>
                                        <span><strong>Important: </strong>Save and Update when you're done!</span>
                                        <button type="submit" class="btn btn-primary pull-right" name="submit" id="blockinglistsaveupdate" value="saveupdate">保存并更新</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- ######################################################### DHCP ######################################################### -->
                <div id="piholedhcp" class="tab-pane fade<?php if($tab === "piholedhcp"){ ?> in active<?php } ?>">
                    <?php
                    // Pi-hole DHCP server
                    if (isset($setupVars["DHCP_ACTIVE"])) {
                        if ($setupVars["DHCP_ACTIVE"] == 1) {
                            $DHCP = true;
                        } else {
                            $DHCP = false;
                        }
                        // Read setings from config file
                        $DHCPstart = $setupVars["DHCP_START"];
                        $DHCPend = $setupVars["DHCP_END"];
                        $DHCProuter = $setupVars["DHCP_ROUTER"];
                        // This setting has been added later, we have to check if it exists
                        if (isset($setupVars["DHCP_LEASETIME"])) {
                            $DHCPleasetime = $setupVars["DHCP_LEASETIME"];
                            if (strlen($DHCPleasetime) < 1) {
                                // Fallback if empty string
                                $DHCPleasetime = 24;
                            }
                        } else {
                            $DHCPleasetime = 24;
                        }
                        if (isset($setupVars["DHCP_IPv6"])) {
                            $DHCPIPv6 = $setupVars["DHCP_IPv6"];
                        } else {
                            $DHCPIPv6 = false;
                        }

                    } else {
                        $DHCP = false;
                        // Try to guess initial settings
                        if ($piHoleIPv4 !== "unknown") {
                            $DHCPdomain = explode(".", $piHoleIPv4);
                            $DHCPstart = $DHCPdomain[0] . "." . $DHCPdomain[1] . "." . $DHCPdomain[2] . ".201";
                            $DHCPend = $DHCPdomain[0] . "." . $DHCPdomain[1] . "." . $DHCPdomain[2] . ".251";
                            $DHCProuter = $DHCPdomain[0] . "." . $DHCPdomain[1] . "." . $DHCPdomain[2] . ".1";
                        } else {
                            $DHCPstart = "";
                            $DHCPend = "";
                            $DHCProuter = "";
                        }
                        $DHCPleasetime = 24;
                        $DHCPIPv6 = false;
                    }
                    if (isset($setupVars["PIHOLE_DOMAIN"])) {
                        $piHoleDomain = $setupVars["PIHOLE_DOMAIN"];
                    } else {
                        $piHoleDomain = "lan";
                    }
                    ?>
                    <form role="form" method="post">
                        <div class="row">
                            <!-- DHCP Settings Box -->
                            <div class="col-md-6">
                                <div class="box box-warning">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">DHCP 设置</h3>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" name="active" id="DHCPchk"
                                                                      <?php if ($DHCP){ ?>checked<?php }
                                                                      ?>>开启DHCP服务器</label>
                                                    </div>
                                                </div>
                                                <p id="dhcpnotice" <?php if (!$DHCP){ ?>hidden<?php }
                                                                   ?>>请确保网络中已关闭其他DHCP服务器，防止冲突!</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <label>IP地址池范围</label>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-12 col-lg-6">
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <div class="input-group-addon">从</div>
                                                        <input type="text" class="form-control DHCPgroup" name="from"
                                                               data-inputmask="'alias': 'ip'" data-mask
                                                               value="<?php echo $DHCPstart; ?>"
                                                               <?php if (!$DHCP){ ?>disabled<?php } ?>>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-12 col-lg-6">
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <div class="input-group-addon">到</div>
                                                        <input type="text" class="form-control DHCPgroup" name="to"
                                                               data-inputmask="'alias': 'ip'" data-mask
                                                               value="<?php echo $DHCPend; ?>"
                                                               <?php if (!$DHCP){ ?>disabled<?php } ?>>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label>默认网关</label>
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <div class="input-group-addon">网关</div>
                                                        <input type="text" class="form-control DHCPgroup" name="router"
                                                               data-inputmask="'alias': 'ip'" data-mask
                                                               value="<?php echo $DHCProuter; ?>"
                                                               <?php if (!$DHCP){ ?>disabled<?php } ?>>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Advanced DHCP Settings Box -->
                            <div class="col-md-6">
                                <div class="box box-warning">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">DHCP 高级设置</h3>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label>Pi-hole domain name</label>
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <div class="input-group-addon">Domain</div>
                                                        <input type="text" class="form-control DHCPgroup" name="domain"
                                                               value="<?php echo $piHoleDomain; ?>"
                                                               <?php if (!$DHCP){ ?>disabled<?php } ?>>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label>租约时间</label>
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <div class="input-group-addon">租约时间（小时）</div>
                                                        <input type="text" class="form-control DHCPgroup"
                                                               name="leasetime"
                                                               id="leasetime" value="<?php echo $DHCPleasetime; ?>"
                                                               data-inputmask="'mask': '9', 'repeat': 7, 'greedy' : false"
                                                               data-mask <?php if (!$DHCP){ ?>disabled<?php } ?>>
                                                    </div>
                                                </div>
                                                <p>Hint: 0 = infinite, 24 = one day, 168 = one week, 744 = one month, 8760 = one year</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" name="useIPv6" class="DHCPgroup"
                                                                      <?php if ($DHCPIPv6){ ?>checked<?php };
                                                                            if (!$DHCP){ ?> disabled<?php }
                                                                      ?>>开启IPv6 支持 (SLAAC + RA)</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- DHCP Leases Box -->
                        <div class="row">
                            <?php
                            $dhcp_leases = array();
                            if ($DHCP) {
                                // Read leases file
                                $leasesfile = true;
                                $dhcpleases = @fopen('/etc/pihole/dhcp.leases', 'r');
                                if (!is_resource($dhcpleases))
                                    $leasesfile = false;

                                function convertseconds($argument)
                                {
                                    $seconds = round($argument);
                                    if ($seconds < 60) {
                                        return sprintf('%ds', $seconds);
                                    } elseif ($seconds < 3600) {
                                        return sprintf('%dm %ds', ($seconds / 60), ($seconds % 60));
                                    } elseif ($seconds < 86400) {
                                        return sprintf('%dh %dm %ds', ($seconds / 3600 % 24), ($seconds / 60 % 60), ($seconds % 60));
                                    } else {
                                        return sprintf('%dd %dh %dm %ds', ($seconds / 86400), ($seconds / 3600 % 24), ($seconds / 60 % 60), ($seconds % 60));
                                    }
                                }

                                while (!feof($dhcpleases) && $leasesfile) {
                                    $line = explode(" ", trim(fgets($dhcpleases)));
                                    if (count($line) == 5) {
                                        $counter = intval($line[0]);
                                        if ($counter == 0) {
                                            $time = "Infinite";
                                        } elseif ($counter <= 315360000) // 10 years in seconds
                                        {
                                            $time = convertseconds($counter);
                                        } else // Assume time stamp
                                        {
                                            $time = convertseconds($counter - time());
                                        }

                                        if (strpos($line[2], ':') !== false) {
                                            // IPv6 address
                                            $type = 6;
                                        } else {
                                            // IPv4 lease
                                            $type = 4;
                                        }

                                        $host = $line[3];
                                        if ($host == "*") {
                                            $host = "<i>unknown</i>";
                                        }

                                        $clid = $line[4];
                                        if ($clid == "*") {
                                            $clid = "<i>unknown</i>";
                                        }

                                        array_push($dhcp_leases, ["TIME" => $time, "hwaddr" => strtoupper($line[1]), "IP" => $line[2], "host" => $host, "clid" => $clid, "type" => $type]);
                                    }
                                }
                            }

                            readStaticLeasesFile();
                            ?>
                            <div class="col-md-12">
                                <div class="box box-warning">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">DHCP leases</h3>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label>Currently active DHCP leases</label>
                                                <table id="DHCPLeasesTable" class="table table-striped table-bordered dt-responsive nowrap"
                                                       cellspacing="0" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th>MAC地址</th>
                                                            <th>IP地址</th>
                                                            <th>主机名</th>
                                                            <td></td>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($dhcp_leases as $lease) { ?>
                                                        <tr data-placement="auto" data-container="body" data-toggle="tooltip"
                                                            title="Lease type: IPv<?php echo $lease["type"]; ?><br/>Remaining lease time: <?php echo $lease["TIME"]; ?><br/>DHCP UID: <?php echo $lease["clid"]; ?>">
                                                            <td id="MAC"><?php echo $lease["hwaddr"]; ?></td>
                                                            <td id="IP"><?php echo $lease["IP"]; ?></td>
                                                            <td id="HOST"><?php echo $lease["host"]; ?></td>
                                                            <td>
                                                                <button class="btn btn-warning btn-xs" type="button" id="button" data-static="alert">
                                                                    <span class="glyphicon glyphicon-copy"></span>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                                <br>
                                            </div>
                                            <div class="col-md-12">
                                                <label>Static DHCP leases configuration</label>
                                                <table id="DHCPStaticLeasesTable" class="table table-striped table-bordered dt-responsive nowrap"
                                                       cellspacing="0" width="100%">
                                                    <thead>
                                                    <tr>
                                                        <th>MAC地址</th>
                                                        <th>IP地址</th>
                                                        <th>主机名</th>
                                                        <td></td>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($dhcp_static_leases as $lease) { ?>
                                                        <tr>
                                                            <td><?php echo $lease["hwaddr"]; ?></td>
                                                            <td><?php echo $lease["IP"]; ?></td>
                                                            <td><?php echo $lease["host"]; ?></td>
                                                            <td><?php if (strlen($lease["hwaddr"]) > 0) { ?>
                                                                <button class="btn btn-danger btn-xs" type="submit" name="removestatic"
                                                                        value="<?php echo $lease["hwaddr"]; ?>">
                                                                    <span class="glyphicon glyphicon-trash"></span>
                                                                </button>
                                                                <?php } ?>
                                                            </td>
                                                        </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                    <tfoot style="display: table-row-group">
                                                        <tr>
                                                            <td><input type="text" name="AddMAC"></td>
                                                            <td><input type="text" name="AddIP"></td>
                                                            <td><input type="text" name="AddHostname" value=""></td>
                                                            <td>
                                                                <button class="btn btn-success btn-xs" type="submit" name="addstatic">
                                                                    <span class="glyphicon glyphicon-plus"></span>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                                <p>Specifying the MAC address is mandatory and only one entry per MAC
                                                   address is allowed. If the IP address is omitted and a host name is
                                                   given, the IP address will still be generated dynamically and the
                                                   specified host name will be used. If the host name is omitted, only
                                                   a static lease will be added.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="field" value="DHCP">
                                <input type="hidden" name="token" value="<?php echo $token ?>">
                                <button type="submit" class="btn btn-primary pull-right">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- ######################################################### DNS ######################################################### -->
                <div id="dns" class="tab-pane fade<?php if($tab === "dns"){ ?> in active<?php } ?>">
                    <form role="form" method="post">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="box box-warning">
                                    <div class="box-header with-border">
                                        <h1 class="box-title">上游DNS服务器</h1>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <table class="table table-bordered">
                                                    <tr>
                                                        <th colspan="2">IPv4</th>
                                                        <th colspan="2">IPv6</th>
                                                        <th>名称</th>
                                                    </tr>
                                                    <?php foreach ($DNSserverslist as $key => $value) { ?>
                                                    <tr>
                                                    <?php if (isset($value["v4_1"])) { ?>
                                                        <td title="<?php echo $value["v4_1"]; ?>">
                                                            <input type="checkbox" name="DNSserver<?php echo $value["v4_1"]; ?>" value="true"
                                                                   <?php if (in_array($value["v4_1"], $DNSactive)){ ?>checked<?php } ?>>
                                                        </td>
                                                    <?php } else { ?>
                                                        <td></td>
                                                    <?php } ?>
                                                    <?php if (isset($value["v4_2"])) { ?>
                                                        <td title="<?php echo $value["v4_2"]; ?>">
                                                            <input type="checkbox" name="DNSserver<?php echo $value["v4_2"]; ?>" value="true"
                                                                   <?php if (in_array($value["v4_2"], $DNSactive)){ ?>checked<?php } ?>>
                                                        </td>
                                                    <?php } else { ?>
                                                        <td></td>
                                                    <?php } ?>
                                                    <?php if (isset($value["v6_1"])) { ?>
                                                        <td title="<?php echo $value["v6_1"]; ?>">
                                                            <input type="checkbox" name="DNSserver<?php echo $value["v6_1"]; ?>" value="true"
                                                                   <?php if (in_array($value["v6_1"], $DNSactive) && $IPv6connectivity){ ?>checked<?php }
                                                                         if (!$IPv6connectivity) { ?> disabled <?php } ?>>
                                                        </td>
                                                    <?php } else { ?>
                                                        <td></td>
                                                    <?php } ?>
                                                    <?php if (isset($value["v6_2"])) { ?>
                                                        <td title="<?php echo $value["v6_2"]; ?>">
                                                            <input type="checkbox" name="DNSserver<?php echo $value["v6_2"]; ?>" value="true"
                                                                   <?php if (in_array($value["v6_2"], $DNSactive) && $IPv6connectivity){ ?>checked<?php }
                                                                if (!$IPv6connectivity) { ?> disabled <?php } ?>>
                                                        </td>
                                                    <?php } else { ?>
                                                        <td></td>
                                                    <?php } ?>
                                                        <td><?php echo $key; ?></td>
                                                    </tr>
                                                    <?php } ?>
                                                </table>
                                            </div>
                                            <div class="col-sm-6">
                                                <label>&nbsp;</label>
                                                <div class="form-group">
                                                    <label>自定义 1 (IPv4)</label>
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <input type="checkbox" name="custom1" value="Customv4"
                                                                   <?php if (isset($custom1)){ ?>checked<?php } ?>>
                                                        </div>
                                                        <input type="text" name="custom1val" class="form-control"
                                                               data-inputmask="'alias': 'ip'" data-mask
                                                               <?php if (isset($custom1)){ ?>value="<?php echo $custom1; ?>"<?php } ?>>
                                                    </div>
                                                    <label>自定义 2 (IPv4)</label>
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <input type="checkbox" name="custom2" value="Customv4"
                                                                   <?php if (isset($custom2)){ ?>checked<?php } ?>>
                                                        </div>
                                                        <input type="text" name="custom2val" class="form-control"
                                                               data-inputmask="'alias': 'ip'" data-mask
                                                               <?php if (isset($custom2)){ ?>value="<?php echo $custom2; ?>"<?php } ?>>
                                                    </div>
                                                    <label>自定义 3 (IPv6)</label>
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <input type="checkbox" name="custom3" value="Customv6"
                                                                   <?php if (isset($custom3)){ ?>checked<?php } ?>>
                                                        </div>
                                                        <input type="text" name="custom3val" class="form-control"
                                                               data-inputmask="'alias': 'ipv6'" data-mask
                                                               <?php if (isset($custom3)){ ?>value="<?php echo $custom3; ?>"<?php } ?>>
                                                    </div>
                                                    <label>自定义 4 (IPv6)</label>
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <input type="checkbox" name="custom4" value="Customv6"
                                                                   <?php if (isset($custom4)){ ?>checked<?php } ?>>
                                                        </div>
                                                        <input type="text" name="custom4val" class="form-control"
                                                               data-inputmask="'alias': 'ipv6'" data-mask
                                                               <?php if (isset($custom4)){ ?>value="<?php echo $custom4; ?>"<?php } ?>>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="box box-warning">
                                    <div class="box-header with-border">
                                        <h1 class="box-title">监听接口状态</h1>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <div class="radio">
                                                        <label><input type="radio" name="DNSinterface" value="local"
                                                                      <?php if ($DNSinterface == "local"){ ?>checked<?php } ?>>
                                                               <strong>接口监听状态</strong>
                                                               <br>只允许最多一跳的设备的查询（本地设备）</label>
                                                    </div>
                                                    <div class="radio">
                                                        <label><input type="radio" name="DNSinterface" value="single"
                                                                      <?php if ($DNSinterface == "single"){ ?>checked<?php } ?>>
                                                               <strong>只监听接口 <?php echo $piHoleInterface; ?></strong>
                                                        </label>
                                                    </div>
                                                    <div class="radio">
                                                        <label><input type="radio" name="DNSinterface" value="all"
                                                                      <?php if ($DNSinterface == "all"){ ?>checked<?php } ?>>
                                                               <strong>监听所有接口, 允许所有来源</strong>
                                                        </label>
                                                    </div>
                                                </div>
                                                <p>请注意, 最后一个选项不适用于直接连接到互联网的设备. This option is safe if your
                                                   Pi-hole is located within your local network, i.e. protected behind
                                                   your router, and you have not forwarded port 53 to this device. In
                                                   virtually all other cases you have to make sure that your Pi-hole is
                                                   properly firewalled.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="box box-warning">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">DNS 高级设置</h3>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" name="DNSrequiresFQDN" title="domain-needed"
                                                                      <?php if ($DNSrequiresFQDN){ ?>checked<?php }
                                                                      ?>>不转发FQDN解析</label>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" name="DNSbogusPriv" title="bogus-priv"
                                                                      <?php if ($DNSbogusPriv){ ?>checked<?php }
                                                                      ?>>Never forward reverse lookups for private IP ranges</label>
                                                    </div>
                                                </div>
                                                <p>Note that enabling these two options may increase your privacy
                                                   slightly, but may also prevent you from being able to access
                                                   local hostnames if the Pi-hole is not used as DHCP server</p>
                                                <div class="form-group">
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" name="DNSSEC"
                                                                      <?php if ($DNSSEC){ ?>checked<?php }
                                                                      ?>>Use DNSSEC</label>
                                                    </div>
                                                </div>
                                                <p>Validate DNS replies and cache DNSSEC data. When forwarding DNS
                                                   queries, Pi-hole requests the DNSSEC records needed to validate
                                                   the replies. Use Google, Norton, DNS.WATCH or Quad9 DNS servers when activating
                                                   DNSSEC. Note that the size of your log might increase significantly
                                                   when enabling DNSSEC. A DNSSEC resolver test can be found
                                                   <a href="http://dnssec.vs.uni-due.de/" target="_blank">here</a>.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="field" value="DNS">
                                <input type="hidden" name="token" value="<?php echo $token ?>">
                                <button type="submit" class="btn btn-primary pull-right">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- ######################################################### API and Web ######################################################### -->
                <?php
                // CPU temperature unit
                if (isset($setupVars["TEMPERATUREUNIT"])) {
                    $temperatureunit = $setupVars["TEMPERATUREUNIT"];
                } else {
                    $temperatureunit = "C";
                }
                // Use $boxedlayout value determined in header.php
                ?>
                <div id="api" class="tab-pane fade<?php if($tab === "api"){ ?> in active<?php } ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <form role="form" method="post">
                                <div class="box box-warning">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">API 设置</h3>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4>Top Lists</h4>
                                                <p>Exclude the following domains from being shown in</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-6 col-md-12 col-lg-6">
                                                <div class="form-group">
                                                    <label>Top Domains / Top Advertisers</label>
                                                    <textarea name="domains" class="form-control" placeholder="Enter one domain per line"
                                                              rows="4"><?php foreach ($excludedDomains as $domain) {
                                                                             echo $domain . "\n"; }
                                                                       ?></textarea>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-12 col-lg-6">
                                                <div class="form-group">
                                                    <label>Top Clients</label>
                                                    <textarea name="clients" class="form-control" placeholder="Enter one IP address or host name per line"
                                                              rows="4"><?php foreach ($excludedClients as $client) {
                                                                             echo $client . "\n"; }
                                                                       ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <h4>Privacy settings (Statistics / Query Log)</h4>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" name="querylog-permitted"
                                                                      <?php if ($queryLog === "permittedonly" || $queryLog === "all"){ ?>checked<?php }
                                                                      ?>>Show permitted domain entries</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" name="querylog-blocked"
                                                                      <?php if ($queryLog === "blockedonly" || $queryLog === "all"){ ?>checked<?php }
                                                                      ?>>Show blocked domain entries</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <h4>Privacy mode</h4>
                                                <div class="form-group">
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" name="privacyMode"
                                                                      <?php if ($privacyMode){ ?>checked<?php }
                                                                      ?>>Don't show origin of DNS requests in query log</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-footer clearfix">
                                        <input type="hidden" name="field" value="API">
                                        <input type="hidden" name="token" value="<?php echo $token ?>">
                                        <button type="button" class="btn btn-primary api-token">Show API token</button>
                                        <button type="submit" class="btn btn-primary pull-right">保存</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form role="form" method="post">
                                <div class="box box-warning">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Web 界面设置</h3>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4>外观</h4>
                                                <div class="form-group">
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" name="boxedlayout" value="yes"
                                                                      <?php if ($boxedlayout){ ?>checked<?php }
                                                                      ?>>Use boxed layout (helpful when working on large screens)</label>
                                                    </div>
                                                </div>
                                                <h4>CPU 温度单位</h4>
                                                <div class="form-group">
                                                    <div class="radio">
                                                        <label><input type="radio" name="tempunit" value="C"
                                                                      <?php if ($temperatureunit === "C"){ ?>checked<?php }
                                                                      ?>>摄氏</label>
                                                    </div>
                                                    <div class="radio">
                                                        <label><input type="radio" name="tempunit" value="K"
                                                                      <?php if ($temperatureunit === "K"){ ?>checked<?php }
                                                                      ?>>开式</label>
                                                    </div>
                                                    <div class="radio">
                                                        <label><input type="radio" name="tempunit" value="F"
                                                                      <?php if ($temperatureunit === "F"){ ?>checked<?php }
                                                                      ?>>华式</label>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="field" value="webUI">
                                                <input type="hidden" name="token" value="<?php echo $token ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-footer clearfix">
                                        <button type="submit" class="btn btn-primary pull-right">保存</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- ######################################################### Teleporter ######################################################### -->
                <div id="teleporter" class="tab-pane fade<?php if($tab === "teleporter"){ ?> in active<?php } ?>">
                    <div class="row">
                        <?php if (extension_loaded('Phar')) { ?>
                        <form role="form" method="post" id="takeoutform"
                              action="scripts/pi-hole/php/teleporter.php"
                              target="_blank" enctype="multipart/form-data">
                            <input type="hidden" name="token" value="<?php echo $token ?>">
                            <div class="col-lg-6 col-md-12">
                                <div class="box box-warning">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Teleporter Export</h3>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <p>Export your Pi-hole lists as downloadable archive</p>
                                                <button type="submit" class="btn btn-default">Export</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="box box-warning">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Teleporter Import</h3>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-lg-6 col-md-12">
                                                <label>Import ...</label>
                                                <div class="form-group">
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" name="whitelist" value="true"
                                                                      checked>
                                                            Whitelist</label>
                                                    </div>
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" name="blacklist" value="true"
                                                                      checked>
                                                            Blacklist (exact)</label>
                                                    </div>
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" name="wildlist" value="true"
                                                                      checked>
                                                            Blacklist (wildcard)</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 col-md-12">
                                                <div class="form-group">
                                                    <label for="zip_file">File input</label>
                                                    <input type="file" name="zip_file" id="zip_file">
                                                    <p class="help-block">Upload only Pi-hole backup files.</p>
                                                    <button type="submit" class="btn btn-default" name="action"
                                                            value="in">Import
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <?php } else { ?>
                        <div class="col-lg-12">
                            <div class="box box-warning">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Teleporter</h3>
                                </div>
                                <div class="box-body">
                                    <p>The PHP extension <code>Phar</code> is not loaded. Please ensure it is installed and loaded if you want to use the Pi-hole teleporter.</p>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <!-- ######################################################### System admin ######################################################### -->
                <div id="sysadmin" class="tab-pane fade<?php if($tab === "sysadmin"){ ?> in active<?php } ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="box">
                                <div class="box-header with-border">
                                    <h3 class="box-title">网络信息</h3>
                                </div>
                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <table class="table table-striped table-bordered dt-responsive nowrap">
                                                <tbody>
                                                <tr>
                                                    <th scope="row">Pi-hole 网卡:</th>
                                                    <td><?php echo $piHoleInterface; ?></td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Pi-hole IPv4 地址:</th>
                                                    <td><?php echo $piHoleIPv4; ?></td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Pi-hole IPv6 地址:</th>
                                                    <td><?php echo $piHoleIPv6; ?></td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Pi-hole 主机名:</th>
                                                    <td><?php echo $hostname; ?></td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="box">
                                <div class="box-header with-border">
                                    <h3 class="box-title">FTL 信息</h3>
                                </div>
                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <?php
                                            if ($FTL) {
                                                function get_FTL_data($arg)
                                                {
                                                    global $FTLpid;
                                                    return trim(exec("ps -p " . $FTLpid . " -o " . $arg));
                                                }

                                                $FTLversion = exec("/usr/bin/pihole-FTL version");
                                            ?>
                                            <table class="table table-striped table-bordered dt-responsive nowrap">
                                                <tbody>
                                                    <tr>
                                                        <th scope="row">FTL 版本:</th>
                                                        <td><?php echo $FTLversion; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">进程ID (PID):</th>
                                                        <td><?php echo $FTLpid; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">开始运行时间:</th>
                                                        <td><?php print_r(get_FTL_data("start")); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">用户 / 组:</th>
                                                        <td><?php print_r(get_FTL_data("euser")); ?> / <?php print_r(get_FTL_data("egroup")); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">CPU 使用率:</th>
                                                        <td><?php print_r(get_FTL_data("%cpu")); ?>%</td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">内存 使用率:</th>
                                                        <td><?php print_r(get_FTL_data("%mem")); ?>%</td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">
                                                            <span title="Resident memory is the portion of memory occupied by a process that is held in main memory (RAM). The rest of the occupied memory exists in the swap space or file system.">内存使用:</span>
                                                        </th>
                                                        <td><?php echo formatSizeUnits(1e3 * floatval(get_FTL_data("rss"))); ?></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <?php } else { ?>
                                            <div>FTL服务离线</div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-warning">
                                <div class="box-header with-border">
                                    <h3 class="box-title">重要操作</h3><br/>
                                </div>
                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <?php if ($piHoleLogging) { ?>
                                                <button type="button" class="btn btn-warning confirm-disablelogging-noflush form-control">关闭查询日志记录</button>
                                            <?php } else { ?>
                                                <form role="form" method="post">
                                                    <input type="hidden" name="action" value="Enable">
                                                    <input type="hidden" name="field" value="Logging">
                                                    <input type="hidden" name="token" value="<?php echo $token ?>">
                                                    <button type="submit" class="btn btn-success form-control">启用查询日志记录</button>
                                                </form>
                                            <?php } ?>
                                        </div>
                                        <p class="hidden-md hidden-lg"></p>
                                        <div class="col-md-4">
                                            <?php if ($piHoleLogging) { ?>
                                                <button type="button" class="btn btn-danger confirm-disablelogging form-control">禁用日志刷新及解析记录</button>
                                            <?php } ?>
                                        </div>
                                        <p class="hidden-md hidden-lg"></p>
                                        <div class="col-md-4">
                                            <button type="button" class="btn btn-warning confirm-restartdns form-control">重启dnsmasq</button>
                                        </div>
                                    </div>
                                    <br/>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <button type="button" class="btn btn-danger confirm-flushlogs form-control">刷新日志</button>
                                        </div>
                                        <p class="hidden-md hidden-lg"></p>
                                        <div class="col-md-4">
                                            <button type="button" class="btn btn-danger confirm-poweroff form-control">关闭系统</button>
                                        </div>
                                        <p class="hidden-md hidden-lg"></p>
                                        <div class="col-md-4">
                                            <button type="button" class="btn btn-danger confirm-reboot form-control">重启系统</button>
                                        </div>
                                    </div>

                                    <form role="form" method="post" id="flushlogsform">
                                        <input type="hidden" name="field" value="flushlogs">
                                        <input type="hidden" name="token" value="<?php echo $token ?>">
                                    </form>
                                    <form role="form" method="post" id="disablelogsform">
                                        <input type="hidden" name="field" value="Logging">
                                        <input type="hidden" name="action" value="Disable">
                                        <input type="hidden" name="token" value="<?php echo $token ?>">
                                    </form>
                                    <form role="form" method="post" id="disablelogsform-noflush">
                                        <input type="hidden" name="field" value="Logging">
                                        <input type="hidden" name="action" value="Disable-noflush">
                                        <input type="hidden" name="token" value="<?php echo $token ?>">
                                    </form>
                                    <form role="form" method="post" id="poweroffform">
                                        <input type="hidden" name="field" value="poweroff">
                                        <input type="hidden" name="token" value="<?php echo $token ?>">
                                    </form>
                                    <form role="form" method="post" id="rebootform">
                                        <input type="hidden" name="field" value="reboot">
                                        <input type="hidden" name="token" value="<?php echo $token ?>">
                                    </form>
                                    <form role="form" method="post" id="restartdnsform">
                                        <input type="hidden" name="field" value="restartdns">
                                        <input type="hidden" name="token" value="<?php echo $token ?>">
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require "scripts/pi-hole/php/footer.php";
?>

<script src="scripts/vendor/jquery.inputmask.js"></script>
<script src="scripts/vendor/jquery.inputmask.extensions.js"></script>
<script src="scripts/vendor/jquery.confirm.min.js"></script>
<script src="scripts/pi-hole/js/settings.js"></script>
