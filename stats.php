<?php
include 'apiFunctions/init.php';
$twitchtv = new TwitchTV;

$user = checkConnect();
$message = "";
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Stats | Trahanqc's API</title>

        <?php include 'includes/header.php'; ?>

    </head>
    <body>
        <nav class="navbar navbar-fixed-top navbar-dark bg-inverse">
            <a class="navbar-brand" href="dashboard">Trahanqc's API</a>

            <ul class="nav nav-pills nav-right" role="tablist" data-toggle="pill">
                <?php if(!empty($user)) : ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false"><?= $user[0]['twitchUsername']; ?></a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="logout">Logout</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $twitchtv->authenticate() ?>" id="login_twitch">Login with Twitch</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

        <div id="wrapper">
            <div id="sidebar">
                <?php include 'addon/main_menu.php'; ?>
            </div>

            <div id="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12">
                            <h1 class="page-header">Stats</h1>

                            <ol class="breadcrumb">
                                <li>
                                    <i class="fa fa-dashboard"></i>  <a href="dashboard">Dashboard</a>
                                </li>
                                <li class="active">
                                    <i class="fa fa-bar-chart"></i>  Stats
                                </li>
                            </ol>

                            <div id="messages"><?= $message; ?></div>

                            <?php
                            if(!empty($user)) :
                                $milestones = getMilestones();
                                $stats = getStatsAdmin();
                                $statsGlobal = getStatsGlobal();
                                $settings = getSettingsAdmin();
                                
                                if(!empty($statsGlobal)) :
                                    ?>
                                    <h1>Global Stats</h1>
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <table class="table table-hover table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Channel Name</th>
                                                        <th>Used</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $count = 0;
                                                    if(isset($statsGlobal['channels'])) :
                                                        foreach($statsGlobal['channels'] as $channel => $nb) :
                                                            if($count < 10) : ?>
                                                                <tr>
                                                                    <td><?= $channel; ?></td>
                                                                    <td><?= $nb . " (<strong>" . Round(($nb / $statsGlobal['nbCommands']) * 100, 2) . "%</strong>)"; ?></td>
                                                                </tr>
                                                                <?php
                                                            endif;
                                                            ++$count;
                                                        endforeach;
                                                    endif;
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="col-lg-6">
                                            <table class="table table-hover table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Used</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $count = 0;
                                                    if(isset($statsGlobal['commandsPerDay'])) :
                                                        foreach($statsGlobal['commandsPerDay'] as $day => $nb) :
                                                            if($count < 10) : ?>
                                                                <tr>
                                                                    <td><?= format_date($day); ?></td>
                                                                    <td><?= $nb . " (<strong>" . Round(($nb / $statsGlobal['nbCommands']) * 100, 2) . "%</strong>)"; ?></td>
                                                                </tr>
                                                                <?php
                                                            endif;
                                                            ++$count;
                                                        endforeach;
                                                    endif;
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <h1>Stats per channel</h1>
                                <?php
								endif;
                                if(!empty($stats)) : 
                                    foreach($stats as $twitch => $val) : 
                                        $string = (array_key_exists($twitch, $settings)) ? ' &#8212; ' . $settings[$twitch] : '';
                                        ?>
                                <h3 class="page-header"><?= $twitch . ' &#8212; (<strong>' . Round(($val['allCommands'] / $statsGlobal['nbCommands']) * 100, 2) . '%</strong>)' . $string; ?></h3>
                                <div class="row">
                                    <div class="col-lg-3">
                                        <h3>Most used commands</h3>
                                        <table class="table table-hover table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Command name</th>
                                                    <th>Used</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $count = 0;
                                                if(isset($val['mostUsed'])) :
                                                    foreach($val['mostUsed'] as $command => $nb) :
                                                        if($count < 10) : ?>
                                                            <tr>
                                                                <td><?= "!" . $command; ?></td>
                                                                <td><?= $nb . " (<strong>" . Round(($nb / $val['allCommands']) * 100, 2) . "%</strong>)"; ?></td>
                                                            </tr>
                                                            <?php
                                                        endif;
                                                        ++$count;
                                                    endforeach;
                                                endif;
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="col-lg-3">
                                        <h3>User most commands</h3>
                                        <table class="table table-hover table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Username</th>
                                                    <th>Used</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $count = 0;
                                                if(isset($val['mostUsername'])) :
                                                    foreach($val['mostUsername'] as $username => $nb) :
                                                        if($count < 10) : ?>
                                                            <tr>
                                                                <td><?= $username; ?></td>
                                                                <td><?= $nb . " (<strong>" . Round(($nb / $val['allCommands']) * 100, 2) . "%</strong>)"; ?></td>
                                                            </tr>
                                                            <?php
                                                        endif;
                                                        ++$count;
                                                    endforeach;
                                                endif;
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="col-lg-3">
                                        <h3>Milestones</h3>
                                        <table class="table table-hover table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Milestone</th>
                                                    <th>Description</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if(isset($val['milestones'])) :
                                                    foreach($val['milestones'] as $milestone => $username) :
                                                        ?>
                                                        <tr>
                                                            <td><?= $milestones[$milestone]; ?></td>
                                                            <td><?= $milestone; ?> command was done by <?= $username; ?></td>
                                                        </tr>
                                                    <?php
                                                    endforeach;
                                                endif;
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="col-lg-3">
                                        <h3>Most commands per day</h3>
                                        <table class="table table-hover table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Day</th>
                                                    <th>Used</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $count = 0;
                                                if(isset($val['mostPerDay'])) :
                                                    foreach($val['mostPerDay'] as $day => $nb) :
                                                        if($count < 10) : ?>
                                                            <tr>
                                                                <td><?= format_date($day); ?></td>
                                                                <td><?= $nb . " (<strong>" . Round(($nb / $val['allCommands']) * 100, 2) . "%</strong>)"; ?></td>
                                                            </tr>
                                                            <?php
                                                        endif;
                                                        ++$count;
                                                    endforeach;
                                                endif;
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                        <?php
                                    endforeach;
                                endif;
                                ?>

                                <?php for($x = 0 ; $x < 10 ; $x++) : ?>
                                <div class="blank"></div>
                            <?php endfor; ?>

                            <?php else: ?>
                                <p>The stats are currently empty because you are not logged in with your Twitch account.  To do so, click on the login button in the upper right!</p>

                                <?php for($x = 0 ; $x < 21 ; $x++) : ?>
                                    <div class="blank"></div>
                                <?php endfor; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'includes/footer.php'; ?>

    </body>
</html>