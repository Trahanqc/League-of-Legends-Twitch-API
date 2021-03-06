<?php
function checkConnect() {
    if(isset($_SESSION['username'])) {
        return getAllSettings($_SESSION['username']);
    }

    return array();
}

function loginUser($username, $code, $access_token) {
    $db = connect_db();
    $query = "SELECT id FROM settingsApi WHERE LOWER(twitchUsername) = ?";
    $rep = $db->prepare($query);
    $rep->execute(array($username));
    $foundUser = $rep->fetch(PDO::FETCH_NUM)[0];

    if($foundUser != 0) {
        $query = "UPDATE settingsApi SET code = ?, access_token = ?, lastUsed = ? WHERE LOWER(twitchUsername) = ?";
        $rep = $db->prepare($query);
        $rep->execute(
            array($code,
                $access_token,
                date('Y-m-d H:i:s', strtotime('now -6hours')),
                $username
                ));
        $uid = $foundUser;
    }
    else {
        $query = "INSERT INTO settingsApi VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $rep = $db->prepare($query);
        $rep->execute(
            array('',
                $username,
                $code,
                $access_token,
                '{}',
                'YOURSUMMONERID',
                'YOURSUMMONERNAME',
                'YOURREGION',
                'SEASON2016',
                'en',
                date('Y-m-d H:i:s', strtotime('now -6hours')),
                '0000-00-00',
                '',
                '',
                '',
                0,
                0
                ));
        $uid = $db->lastInsertId();
    }

    $_SESSION['uid'] = $uid;
    $_SESSION['username'] = $username;
}

function getAllSettings($username) {
    $db = connect_db();
    $query = "SELECT * FROM settingsApi WHERE LOWER(twitchUsername) = ?";
    $rep = $db->prepare($query);
    $rep->execute(array($username));

    $data = array();

    while($donnees = $rep->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $donnees;
    }

    return $data;
}

function getMilestones() {
    $db = connect_db();
    $query = "SELECT * FROM milestones";
    $rep = $db->prepare($query);
    $rep->execute();

    $data = array();

    while($donnees = $rep->fetch(PDO::FETCH_ASSOC)) {
        $data[$donnees['milestone']] = $donnees['achievement'];
    }

    return $data;
}

function getStatsUsername($username) {
    $db = connect_db();

    $stats = array();

    /**
     * Gather all commands that has been done for a channel
     */

    $query = "SELECT COUNT(*) FROM lolStats WHERE LOWER(channelName) = ?";
    $rep = $db->prepare($query);
    $rep->execute(array($username));
    $stats["allCommands"] = $rep->fetch(PDO::FETCH_NUM)[0];

    /**
     * Gather most used commands for a channel
     */
    $query = "SELECT COUNT(*) as nbCommand, command FROM lolStats WHERE LOWER(channelName) = ? GROUP BY command ORDER BY nbCommand DESC, command ASC";
    $rep = $db->prepare($query);
    $rep->execute(array($username));

    while($donnees = $rep->fetch(PDO::FETCH_ASSOC)) {
        $stats["mostUsed"][$donnees['command']] = $donnees['nbCommand'];
    }

    /**
     * Gather user who uses the most commands
     */
    $query = "SELECT COUNT(*) as nbCommand, username FROM lolStats WHERE LOWER(channelName) = ? GROUP BY username ORDER BY nbCommand DESC, username ASC";
    $rep = $db->prepare($query);
    $rep->execute(array($username));

    while($donnees = $rep->fetch(PDO::FETCH_ASSOC)) {
        $stats["mostUsername"][$donnees['username']] = $donnees['nbCommand'];
    }

    /**
     * Gather most commands per day
     */
    $query = "SELECT COUNT(*) as nbCommands, dateUsed FROM lolStats WHERE LOWER(channelName) = ? GROUP BY CAST(dateUsed AS DATE) ORDER BY nbCommands DESC, dateUsed DESC";
    $rep = $db->prepare($query);
    $rep->execute(array($username));

    while($donnees = $rep->fetch(PDO::FETCH_ASSOC)) {
        $stats["mostPerDay"][$donnees["dateUsed"]] = $donnees['nbCommands'];
    }

    /**
     * Milestones
     */
    $query = "SELECT username, dateUsed FROM lolStats WHERE LOWER(channelName) = ? ORDER BY id ASC";
    $rep = $db->prepare($query);
    $rep->execute(array($username));

    $count = 1;
    while($donnees = $rep->fetch(PDO::FETCH_ASSOC)) {
        switch($count) {
            case 1 : $stats["milestones"]["First"] = '<strong>' . $donnees['username'] . '</strong> on <strong>' . format_date($donnees['dateUsed']) . '</strong>'; break;
            case 50 : $stats["milestones"]["50th"] = '<strong>' . $donnees['username'] . '</strong> on <strong>' . format_date($donnees['dateUsed']) . '</strong>'; break;
            case 100 : $stats["milestones"]["100th"] = '<strong>' . $donnees['username'] . '</strong> on <strong>' . format_date($donnees['dateUsed']) . '</strong>'; break;
            case 200 : $stats["milestones"]["200th"] = '<strong>' . $donnees['username'] . '</strong> on <strong>' . format_date($donnees['dateUsed']) . '</strong>'; break;
            case 300 : $stats["milestones"]["300th"] = '<strong>' . $donnees['username'] . '</strong> on <strong>' . format_date($donnees['dateUsed']) . '</strong>'; break;
            case 400 : $stats["milestones"]["400th"] = '<strong>' . $donnees['username'] . '</strong> on <strong>' . format_date($donnees['dateUsed']) . '</strong>'; break;
            case 500 : $stats["milestones"]["500th"] = '<strong>' . $donnees['username'] . '</strong> on <strong>' . format_date($donnees['dateUsed']) . '</strong>'; break;
            case 1000 : $stats["milestones"]["1000th"] = '<strong>' . $donnees['username'] . '</strong> on <strong>' . format_date($donnees['dateUsed']) . '</strong>'; break;
            case 10000 : $stats["milestones"]["10000th"] = '<strong>' . $donnees['username'] . '</strong> on <strong>' . format_date($donnees['dateUsed']) . '</strong>'; break;
        }
        $count++;
    }

    return $stats;
}

function getStatsAdmin() {
    $db = connect_db();
    $query = "SELECT DISTINCT(channelName) FROM lolStats ORDER BY channelName ASC";
    $rep = $db->prepare($query);
    $rep->execute();

    $stats = array();

    while($donnees = $rep->fetch(PDO::FETCH_ASSOC)) {
        $stats[$donnees['channelName']] = getStatsUsername($donnees['channelName']);
    }

    return $stats;
}

function getSettingsAdmin() {
    $db = connect_db();
    $query = "SELECT twitchUsername, summonerName, region, season, lang FROM settingsApi ORDER BY twitchUsername ASC";
    $rep = $db->prepare($query);
    $rep->execute();

    $data = array();

    while($donnees = $rep->fetch(PDO::FETCH_ASSOC)) {
        $data[$donnees['twitchUsername']] = $donnees['summonerName'] . ' &#8212; ' . $donnees['region'] . ' &#8212; ' . $donnees['season'] . ' &#8212; ' . $donnees['lang'];
    }

    return $data;
}

function getStatsGlobal() {
    $db = connect_db();

    $stats = array();

    $currentDate = date('Y-m-d', strtotime("now -6hours"));
    $lastDate = date('Y-m-d', strtotime("now -10days -6hours"));

    /**
     * Gather all the commands
     */
    $query = "SELECT COUNT(*) FROM lolStats";
    $rep = $db->prepare($query);
    $rep->execute();
    $stats["nbCommands"] = $rep->fetch(PDO::FETCH_NUM)[0];

    /**
     * Gather all the commands last 10 days
     */
    $query = "SELECT COUNT(*) FROM lolStats WHERE dateUsed BETWEEN ? AND ?";
    $rep = $db->prepare($query);
    $rep->execute(array($lastDate, $currentDate));
    $stats["nbCommandsLastDays"] = $rep->fetch(PDO::FETCH_NUM)[0];

    /**
     * Gather all the commands for each channel
     */
    $query = "SELECT COUNT(*) as nbCommands, channelName FROM lolStats GROUP BY channelName ORDER BY nbCommands DESC, channelName ASC";
    $rep = $db->prepare($query);
    $rep->execute();

    while($donnees = $rep->fetch(PDO::FETCH_ASSOC)) {
        $stats["channels"][$donnees['channelName']] = $donnees['nbCommands'];
    }

    /**
     * Gather all the commands for each channel
     */
    $query = "SELECT COUNT(*) as nbCommands, channelName FROM lolStats WHERE CAST(dateUsed AS DATE) BETWEEN ? AND ? GROUP BY channelName ORDER BY nbCommands DESC, channelName ASC";
    $rep = $db->prepare($query);
    $rep->execute(array($lastDate, $currentDate));

    while($donnees = $rep->fetch(PDO::FETCH_ASSOC)) {
        $stats["channelsLastDays"][$donnees['channelName']] = $donnees['nbCommands'];
    }

    /**
     * Gather all the commands for each day
     */
    $query = "SELECT COUNT(*) as nbCommands, dateUsed FROM lolStats WHERE CAST(dateUsed AS DATE) BETWEEN ? AND ? GROUP BY CAST(dateUsed AS DATE) ORDER BY dateUsed DESC";
    $rep = $db->prepare($query);
    $rep->execute(array($lastDate, $currentDate));

    while($donnees = $rep->fetch(PDO::FETCH_ASSOC)) {
        $stats["commandsPerDay"][$donnees['dateUsed']] = $donnees["nbCommands"];
    }

    /**
     * Gather all the commands for each day
     */
    $query = "SELECT COUNT(*) as nbCommands, bot FROM lolStats GROUP BY bot ORDER BY bot ASC";
    $rep = $db->prepare($query);
    $rep->execute();

    while($donnees = $rep->fetch(PDO::FETCH_ASSOC)) {
        $stats["bots"][$donnees['bot']] = $donnees["nbCommands"];
    }

    return $stats;
}

function format_date($date) {
    $newDate = date('F d, Y', strtotime($date));
    $newDate = explode(' ', $newDate);
    //$newDate[1] = moisFr($newDate[1]);
    return implode(' ', $newDate);
}

function getCommands($index = "") {
    $db = connect_db();
    $query = "SELECT * FROM commands ORDER BY name ASC";
    $rep = $db->prepare($query);
    $rep->execute();

    $data = array();

    while($donnees = $rep->fetch(PDO::FETCH_ASSOC)) {
        if($index != "") {
            $data[$donnees[$index]] = $donnees;
        }
        else {
            $data[] = $donnees;
        }
    }

    return $data;
}

function getRegions() {
    $db = connect_db();
    $query = "SELECT platformId, region FROM regions";
    $rep = $db->prepare($query);
    $rep->execute();

    $data = array();

    while($donnees = $rep->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $donnees;
    }

    return $data;
}

function updateSummonerName($summonerName, $region, $summonerId, $season, $lang) {
    if(isset($_SESSION['username'])) {
        $db = connect_db();
        $query = "UPDATE settingsApi SET summonerId = ?, summonerName = ?, region = ?, season = ?, lang = ? WHERE id = ?";
        $rep = $db->prepare($query);
        $rep->execute(array($summonerId, $summonerName, $region, $season, $lang, $_SESSION['uid']));
        return true;
    }
    else {
        return "Your session has expired.  Please relog";
    }
}

function fetchMessages2($id) {
    echo $id;
}

function fetchMessages($category = "all") {
    $db = connect_db();
    $query = "SELECT COUNT(id) FROM blogPosts";
    $query .= ($category != "all") ? " WHERE categoryId = " . $category: "";
    $rep = $db->prepare($query);
    $rep->execute();
    $nb = $rep->fetch(PDO::FETCH_NUM)[0];

    $query = "SELECT p.id, p.title, p.twitchUsername, p.views, p.datePosted, p.locked, bc.category
          FROM blogPosts p
          INNER JOIN blogCategory bc ON bc.id = p.categoryId";
    $query .= ($category != "all") ? " WHERE p.categoryId = " . $category : "";
    $query .= " ORDER BY p.datePosted DESC";
    $rep = $db->prepare($query);
    $rep->execute();

    $query2 = "SELECT COUNT(c.id)
          FROM blogComments c
          INNER JOIN blogPostsComments bpc ON bpc.commentId = c.id
          INNER JOIN blogPosts p ON p.id = bpc.postId
          WHERE p.id = ?";
    $rep2 = $db->prepare($query2);

    $query3 = "SELECT c.datePosted, c.twitchUsername as commentUser
            FROM blogComments c
            INNER JOIN blogPostsComments bpc ON bpc.commentId = c.id
            INNER JOIN blogPosts p ON p.id = bpc.postId
            WHERE p.id = ?
            ORDER BY c.datePosted DESC";
    $rep3 = $db->prepare($query3);

    $max = 16;

    if($nb != 0) :
    ?>
        <table class="table table-hover table-striped">
            <thead>
                <tr class="thead-inverse">
                    <th>Title</th>
                    <th>Created by</th>
                    <th>Category</th>
                    <th>Replies</th>
                    <th>Views</th>
                    <th>Activity</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while($donnees = $rep->fetch(PDO::FETCH_ASSOC)) :
                    $rep2->execute(array($donnees['id']));
                    $nbComments = $rep2->fetch(PDO::FETCH_NUM)[0];

                    $rep3->execute(array($donnees['id']));
                    $lastActivity = $rep3->fetch(PDO::FETCH_ASSOC);

                    $lastActivityName = ($lastActivity != NULL) ? $lastActivity["commentUser"] : $donnees['twitchUsername'];
                    ?>
                    <tr>
                        <td><a href="posts-<?= $donnees['id']; ?>"><?= ($donnees['locked'] == 1) ? '<i class="fa fa-lock"></i> ' . $donnees['title'] : $donnees['title']; ?></a></td>
                        <td><?= ($donnees['twitchUsername'] == 'trahanqc') ? $donnees['twitchUsername'] . " <small class='text-success'>Admin</small>" : $donnees['twitchUsername']; ?></td>
                        <td><?= $donnees["category"]; ?></td>
                        <td><?= $nbComments; ?></td>
                        <td><?= $donnees['views']?></td>
                        <td><time class="timeago" datetime="<?= ($lastActivity != NULL) ? $lastActivity["datePosted"] : $donnees['datePosted']; ?>"><?= format_date(($lastActivity != NULL) ? $lastActivity["datePosted"] : $donnees['datePosted']); ?></time> by <?= ($lastActivityName == "trahanqc") ? $lastActivityName . " <small class='text-success'>Admin</small>" : $lastActivityName; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php
        if($nb < $max) :
            for($x = $nb ; $x < $max; $x++) :
            ?>
                <div class="blank"></div>
            <?php
            endfor;
        endif;
    else : ?>
        <p>There is currently no messages in this category</p>

        <?php for($x = 0; $x < 18; $x++): ?>
            <div class="blank"></div>
        <?php
        endfor;
    endif;
}

function fetchPost($id) {
    $id = (int)$id;
    $db = connect_db();
    $foundPost = false;

    $query = "UPDATE blogPosts SET views = views + 1 WHERE id = ?";
    $rep = $db->prepare($query);
    $rep->execute(array($id));

    $query = "SELECT p.*, c.category FROM blogPosts p INNER JOIN blogCategory c ON c.id = p.categoryId WHERE p.id = ?";
    $rep = $db->prepare($query);
    $rep->execute(array($id));
    $post["main"] = $rep->fetch(PDO::FETCH_ASSOC);

    if($post["main"]) {
        $post["users"][] = $post["main"]["twitchUsername"];
        $foundPost = true;
    }

    $query = "SELECT COUNT(c.id)
          FROM blogComments c
          INNER JOIN blogPostsComments bpc ON bpc.commentId = c.id
          INNER JOIN blogPosts p ON p.id = bpc.postId
          WHERE p.id = ?";
    $rep = $db->prepare($query);
    $rep->execute(array($id));
    $nbComments = $rep->fetch(PDO::FETCH_NUM)[0];

    $query = "SELECT c.id, c.twitchUsername, c.datePosted, c.message
            FROM blogComments c
            INNER JOIN blogPostsComments bpc ON bpc.commentId = c.id
            INNER JOIN blogPosts p ON p.id = bpc.postId
            WHERE p.id = ?
            ORDER BY c.datePosted ASC";
    $rep = $db->prepare($query);
    $rep->execute(array($id));

    while($donnees = $rep->fetch(PDO::FETCH_ASSOC)) {
        $post["comments"][] = $donnees;

        if(!in_array($donnees['twitchUsername'], $post["users"])) {
            $post["users"][] = $donnees['twitchUsername'];
        }
    }

    $query = "SELECT c.datePosted
            FROM blogComments c
            INNER JOIN blogPostsComments bpc ON bpc.commentId = c.id
            INNER JOIN blogPosts p ON p.id = bpc.postId
            WHERE p.id = ?
            ORDER BY c.datePosted DESC";
    $rep = $db->prepare($query);
    $rep->execute(array($id));
    $lastActivity = $rep->fetch(PDO::FETCH_ASSOC);

    if($foundPost) :
    ?>
        <div class="post">
            <div class="post-title">
                <h3><span><?= $post["main"]['title']; ?></span> &#8212; <small class="text-muted"><?= $post["main"]['category']; ?></small></h3>
            </div>

            <div class="post-stats">
                <div class="post-stats-unit">
                    <div class="post-stats-number"><?= $nbComments; ?></div>
                    <div class="post-stats-description">Replies</div>
                </div>

                <div class="post-stats-unit">
                    <div class="post-stats-number"><?= $post["main"]['views']; ?></div>
                    <div class="post-stats-description">Views</div>
                </div>

                <div class="post-stats-unit">
                    <div class="post-stats-number"><?= count($post["users"]); ?></div>
                    <div class="post-stats-description">Users</div>
                </div>

                <div class="post-stats-unit">
                    <div class="post-stats-number"><time class="timeago" datetime="<?= ($lastActivity != NULL) ? $lastActivity["datePosted"] : $post["main"]['datePosted']; ?>"><?= format_date(($lastActivity != NULL) ? $lastActivity["datePosted"] : $post["main"]['datePosted']); ?></time></div>
                    <div class="post-stats-description">Last activity</div>
                </div>
            </div>

            <div class="post-content post-main-content">
                <div class="row">
                    <div class="col-lg-2">
                        <div class="post-details">
                            <?= ($post["main"]['twitchUsername'] == "trahanqc") ? $post["main"]['twitchUsername'] . " <small class='text-success'>Admin</small>" : $post["main"]['twitchUsername']; ?> <br>
                            <time class="timeago" datetime="<?= $post["main"]['datePosted']; ?>"><?= format_date($post["main"]['datePosted']); ?></time>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <div class="col-lg-10 post-message-container">
                        <div class="post-message" id="post-<?= $post["main"]["id"]; ?>"><?= nl2br($post["main"]['message']); ?></div>

                        <div class="post-toolbar hidden">
                            <?php if(isset($_SESSION['username']) && $_SESSION['username'] == "trahanqc") : ?>
                                <div class="toolbar-unit lock-post" data-id="<?= $post['main']['id']; ?>" data-locked="<?= $post["main"]["locked"]; ?>">
                                    <i class="fa fa-<?= ($post["main"]["locked"] == "0") ? 'lock' : 'unlock'; ?>"></i>
                                </div>
                            <?php endif; ?>

                            <?php if(isset($_SESSION['username']) && ($_SESSION['username'] == $post["main"]["twitchUsername"] || $_SESSION['username'] == 'trahanqc')) : ?>
                                <div class="toolbar-unit edit-post" data-id="<?= $post["main"]['id']; ?>">
                                    <i class="fa fa-pencil"></i>
                                </div>

                                <div class="toolbar-unit delete-post" data-id="<?= $post["main"]['id']; ?>">
                                    <i class="fa fa-trash"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            if(array_key_exists("comments", $post)) :
                foreach($post["comments"] as $val) :
                    ?>
                    <div class="post-content" id="comment-<?= $val['id']; ?>">
                        <div class="row">
                            <div class="col-lg-2">
                                <div class="post-details">
                                    <?= ($val['twitchUsername'] == 'trahanqc') ? $val['twitchUsername'] . ' <small class=\'text-success\'>Admin</small>' : $val['twitchUsername']; ?> <br>
                                    <time class="timeago" datetime="<?= $val['datePosted']; ?>"><?= format_date($val['datePosted']); ?></time>
                                </div>
                            </div>

                            <div class="divider"></div>

                            <div class="col-lg-10 post-message-container">
                                <div class="post-message" id="message-<?= $val['id']; ?>"><?= nl2br($val['message']); ?></div>

                                <?php if(isset($_SESSION['username']) && ($_SESSION['username'] == $val["twitchUsername"] || $_SESSION['username'] == 'trahanqc')) : ?>
                                    <div class="post-toolbar hidden">
                                        <div class="toolbar-unit modif-comment" data-id="<?= $val['id']; ?>">
                                            <i class="fa fa-pencil"></i>
                                        </div>

                                        <div class="toolbar-unit delete-comment" data-id="<?= $val['id']; ?>">
                                            <i class="fa fa-trash"></i>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php
                endforeach;
            endif;
            ?>
        </div>
    <?php else : ?>
        <p>The post you are looking for doesn't exist.</p>
    <?php
    endif;
}


/**
 * TODO Migth want to add a parameter to determine how many we should grab
 */
function commandsHistory() {
    $db = connect_db();
    $query = "SELECT * FROM lolStats ORDER BY id DESC LIMIT 50";
    $rep = $db->prepare($query);
    $rep->execute();
    ?>
    <table class="table table-hover table-striped table-sm">
        <thead class="thead-inverse">
            <tr>
                <th>Username</th>
                <th>Channel</th>
                <th>Command</th>
                <th>Id</th>
                <th>Region</th>
                <th>Addon</th>
                <th>Bot</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while($val = $rep->fetch(PDO::FETCH_ASSOC)) : ?>
                <tr>
                    <td><?= $val['username']; ?></td>
                    <td><a href="http://twitch.tv/<?= $val['channelName']; ?>"><?= $val['channelName']; ?></a></td>
                    <td><?= $val['command']; ?></td>
                    <td><?= $val['leagueId']; ?></td>
                    <td><?= $val['region']; ?></td>
                    <td><?= $val['addon']; ?></td>
                    <td><?= $val['bot']; ?></td>
                    <td><time class="timeago" datetime="<?= $val['dateUsed']; ?>"><?= format_date($val['dateUsed']); ?></time></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php
}

function getDisabled($id) {
    $db = connect_db();
    $query = "SELECT locked FROM blogPosts WHERE id = ?";
    $rep = $db->prepare($query);
    $rep->execute(array($id));
    $locked = $rep->fetch(PDO::FETCH_ASSOC);
    return ($locked["locked"] == 0) ? false : true;
}

function foundPost($id) {
    $db = connect_db();
    $query = "SELECT COUNT(id) FROM blogPosts WHERE id = ?";
    $rep = $db->prepare($query);
    $rep->execute(array($id));
    $nb = $rep->fetch(PDO::FETCH_NUM)[0];
    return ($nb == 0) ? false : true;
}

function addBlogPost($title, $category, $message, $username) {
    $db = connect_db();
    $query = "INSERT INTO blogPosts VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
    $rep = $db->prepare($query);
    $rep->execute(
        array('',
            $username,
            $category,
            $title,
            $message,
            0,
            0,
            date('Y-m-d H:i:s', strtotime("now -6hours"))
            ));
    return $db->lastInsertId();
}

function deletePost($postId) {
    $db = connect_db();
    $query = "DELETE p, bpc, c FROM blogPosts p
            LEFT JOIN blogPostsComments bpc ON bpc.postId = p.id
            LEFT JOIN blogComments c ON c.id = bpc.commentId
            WHERE p.id = ?";
    $rep = $db->prepare($query);
    $rep->execute(array($postId));
}

function lockPost($postId, $state = "0") {
    $db = connect_db();

    $stateChange = ($state == "1") ? 0 : 1;

    if(isset($_SESSION['username']) && $_SESSION['username'] == "trahanqc") {
        $query = "UPDATE blogPosts SET locked = ? WHERE id = ?";
        $rep = $db->prepare($query);
        $rep->execute(array($stateChange, $postId));
        return ($state == "0") ? 1 : 2;
    }

    return 3;
}

function updatePost($postId, $title = "", $message = "") {
    $db = connect_db();

    $query = "SELECT twitchUsername, locked FROM blogPosts WHERE id = ?";
    $rep = $db->prepare($query);
    $rep->execute(array($postId));
    $data = $rep->fetch(PDO::FETCH_ASSOC);

    if($data["locked"] == "0") {
        if(isset($_SESSION['username']) && ($data['twitchUsername'] == $_SESSION['username'] || $_SESSION['username'] == "trahanqc")) {
            $query = "UPDATE blogPosts SET title = ?, message = ? WHERE id = ?";
            $rep = $db->prepare($query);
            $rep->execute(array($title, $message, $postId));
            return 1;
        }
    }
    else {
        return 2;
    }

    return 3;
}

function addComment($postId, $message) {
    $db = connect_db();

    $query = "SELECT locked FROM blogPosts WHERE id = ?";
    $rep = $db->prepare($query);
    $rep->execute(array($postId));
    $locked = $rep->fetch(PDO::FETCH_ASSOC);

    if($locked["locked"] == 0) {
        $query = "INSERT INTO blogComments VALUES(?, ?, ?, ?)";
        $rep = $db->prepare($query);
        $rep->execute(array('', $_SESSION['username'], $message, date('Y-m-d H:i:s', strtotime("now -6hours"))));
        $commentId = $db->lastInsertId();

        $query = "INSERT INTO blogPostsComments VALUES(?, ?, ?)";
        $rep = $db->prepare($query);
        $rep->execute(array('', $postId, $commentId));

        return 1;
    }

    return 2;
}

function deleteComment($commentId) {
    $db = connect_db();
    $query = "DELETE FROM blogComments WHERE id = ?";
    $rep = $db->prepare($query);
    $rep->execute(array($commentId));

    $query = "DELETE FROM blogPostsComments WHERE commentId = ?";
    $rep = $db->prepare($query);
    $rep->execute(array($commentId));
}

function updateComment($commentId, $message = "") {
    $db = connect_db();

    $query = "SELECT c.twitchUsername, p.locked
            FROM blogComments c
            INNER JOIN blogPostsComments bpc ON bpc.commentId = c.id
            INNER JOIN blogPosts p ON p.id = bpc.postId
            WHERE c.id = ?";
    $rep = $db->prepare($query);
    $rep->execute(array($commentId));
    $data = $rep->fetch(PDO::FETCH_ASSOC);

    if($data["locked"] == "0") {
        if(isset($_SESSION['username']) && ($data['twitchUsername'] == $_SESSION['username'] || $_SESSION['username'] == "trahanqc")) {
            $query = "UPDATE blogComments SET message = ? WHERE id = ?";
            $rep = $db->prepare($query);
            $rep->execute(array($message, $commentId));
            return 1;
        }
    }
    else {
        return 2;
    }

    return 3;
}

function lastForumCheck($uid) {
    $nbNotif = 0;

    $db = connect_db();
    $query = "SELECT dateForumCheck FROM settingsApi WHERE id = ?";
    $rep = $db->prepare($query);
    $rep->execute(array($uid));
    $lastChecked = $rep->fetch(PDO::FETCH_ASSOC)["dateForumCheck"];

    $query = "SELECT COUNT(id) FROM blogPosts WHERE datePosted > ?";
    $rep = $db->prepare($query);
    $rep->execute(array($lastChecked));
    $nbNotif += $rep->fetch(PDO::FETCH_NUM)[0];

    $query = "SELECT COUNT(id) FROM blogComments WHERE datePosted > ?";
    $rep = $db->prepare($query);
    $rep->execute(array($lastChecked));
    $nbNotif += $rep->fetch(PDO::FETCH_NUM)[0];

    return ($nbNotif == 0) ? '' : $nbNotif;
}

function addPatch($title = "", $version = "", $patchNotes = "") {
    $db = connect_db();

    if(isset($_SESSION['username']) && $_SESSION['username'] == "trahanqc") {
        $query = "INSERT INTO patchNotes VALUES(?, ?, ?, ?, ?)";
        $rep = $db->prepare($query);
        $rep->execute(
            array('',
                $title,
                $patchNotes,
                $version,
                date('Y-m-d H:i:s', strtotime("now -6hours"))
                ));

        return "1";
    }
    else {
        return "2";
    }

    return "3";
}

function editPatch($id, $title = "", $version = "", $patchNotes = "") {
    $db = connect_db();

    if(isset($_SESSION['username']) && $_SESSION['username'] == "trahanqc") {
        $query = "UPDATE patchNotes SET title = ?, patchNotes = ?, version = ?, dateCreated = ? WHERE id = ?";
        $rep = $db->prepare($query);
        $rep->execute(
            array(
                $title,
                $patchNotes,
                $version,
                date('Y-m-d H:i:s', strtotime("now -6hours")),
                $id
            ));

        return "1";
    }
    else {
        return "2";
    }

    return "3";
}

function deletePatch($id) {
    $db = connect_db();

    if(isset($_SESSION['username']) && $_SESSION['username'] == "trahanqc") {
        $query = "DELETE FROM patchNotes WHERE id = ?";
        $rep = $db->prepare($query);
        $rep->execute(array($id));

        return "1";
    }
    else {
        return "2";
    }

    return "3";
}

function updateDateForumCheck() {
    $db = connect_db();
    $query = "UPDATE settingsApi SET dateForumCheck = ? WHERE id = ?";
    $rep = $db->prepare($query);
    $rep->execute(array(date("Y-m-d H:i:s", strtotime("now -6hours")), $_SESSION['uid']));
}

function updateNightbotCode($code) {
    $nightbot = getAllFromTable('nightbot');

    $parameters = array(
        "client_id" => $nightbot[0]['client_id'],
        "client_secret" => $nightbot[0]['client_secret'],
        "code" => $code,
        "grant_type" => "authorization_code",
        "redirect_uri" => "https://gotme.site-meute.com/api/v1/accept-nightbot-token"
    );

    $ch = curl_init("https://api.nightbot.tv/oauth2/token");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    $jsonData = json_decode($result, true);

    if(array_key_exists("access_token", $jsonData)) {
        $db = connect_db();
        $query = "UPDATE settingsApi SET nightbotCode = ?, nightbotToken = ?, nightbotRefresh = ?, nightbotTime = ?, nightbotExpire = ? WHERE id = ?";
        $rep = $db->prepare($query);
        $rep->execute(array($code, $jsonData["access_token"], $jsonData["refresh_token"], time() - 60 * 60 * 6, $jsonData['expires_in'], $_SESSION['uid']));
        return 1;
    }

    return 2;
}

function grabNightbotSettings() {
    $db = connect_db();
    $query = "SELECT nightbotCode, nightbotToken, nightbotRefresh, nightbotTime, nightbotExpire FROM settingsApi WHERE id = ?";
    $rep = $db->prepare($query);
    $rep->execute(array($_SESSION['uid']));

    return $rep->fetch(PDO::FETCH_ASSOC);
}

function unlinkNightbot() {
    $nightbotSettings = grabNightbotSettings();

    if(!empty($nightbotSettings) && $nightbotSettings != NULL && $nightbotSettings['nightbotToken'] != "") {
        $parameters = array(
            "token" => $nightbotSettings['nightbotToken']
        );

        $ch = curl_init("https://api.nightbot.tv/oauth2/token/revoke");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        $db = connect_db();
        $query = "UPDATE settingsApi SET nightbotToken = ?, nightbotRefresh = ?, nightbotTime = ?, nightbotExpire = ? WHERE id = ?";
        $rep = $db->prepare($query);
        $rep->execute(array("", "", 0, 0, $_SESSION['uid']));

        return 1;
    }

    return 2;
}

function grabCustomCommands() {
    $nightbotSettings = grabNightbotSettings();

    if(!empty($nightbotSettings) && $nightbotSettings != NULL && $nightbotSettings['nightbotToken'] != "") {
        $ch = curl_init("https://api.nightbot.tv/1/commands");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $nightbotSettings['nightbotToken']
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $jsonData = json_decode($result, true);

        if(!empty($jsonData)) {
            if($jsonData["status"] == "200") {
                return $jsonData;
            }
            else {
                return 2;
            }
        }
    }

    return 2;
}

function addNightbotCommand($data) {
    $nightbotSettings = grabNightbotSettings();

    if(!empty($nightbotSettings) && $nightbotSettings != NULL && $nightbotSettings['nightbotToken'] != "") {
        $userLevel = "everyone";
        switch($data['userLevel']) {
            case 2 : $userLevel = "regular"; break;
            case 3 : $userLevel = "subscriber"; break;
            case 4 : $userLevel = "moderator"; break;
            case 5 : $userLevel = "owner"; break;
        }

        $parameters = array(
            "message" => $data["commandMessage"],
            "userLevel" => $userLevel,
            "name" => $data["commandName"],
            "coolDown" => 30
        );

        $ch = curl_init("https://api.nightbot.tv/1/commands");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $nightbotSettings['nightbotToken']
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $jsonData = json_decode($result, true);

        if(!empty($jsonData)) {
            if($jsonData["status"] == "200") {
                $db = connect_db();
                $query = "UPDATE automaticNightbot SET countUsed = countUsed + 1";
                $rep = $db->prepare($query);
                $rep->execute();

                return 1;
            }
            else {
                return 2;
            }
        }
    }

    return 2;
}

function editNightbotCommand($data) {
    $nightbotSettings = grabNightbotSettings();

    if(!empty($nightbotSettings) && $nightbotSettings != NULL && $nightbotSettings['nightbotToken'] != "") {
        $userLevel = "everyone";
        switch($data['userLevel']) {
            case 2 : $userLevel = "regular"; break;
            case 3 : $userLevel = "subscriber"; break;
            case 4 : $userLevel = "moderator"; break;
            case 5 : $userLevel = "owner"; break;
        }

        $parameters = array(
            "message" => $data["commandMessage"],
            "userLevel" => $userLevel,
            "name" => $data["commandName"]
        );

        $ch = curl_init("https://api.nightbot.tv/1/commands/" . $data['commandId']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $nightbotSettings['nightbotToken']
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        $result = curl_exec($ch);
        curl_close($ch);
        $jsonData = json_decode($result, true);

        if(!empty($jsonData)) {
            if($jsonData["status"] == "200") {
                $db = connect_db();
                $query = "UPDATE automaticNightbot SET countUsed = countUsed + 1";
                $rep = $db->prepare($query);
                $rep->execute();

                return 1;
            }
            else {
                return 2;
            }
        }
    }

    return 2;
}

function saveCommand($commandName, $response) {
    if($commandName != "" && $response != "") {
        $db = connect_db();
        $query = "SELECT data FROM settingsApi WHERE id = ?";
        $rep = $db->prepare($query);
        $rep->execute(array($_SESSION['uid']));
        $data = $rep->fetch(PDO::FETCH_ASSOC);

        $data = json_decode($data["data"]);

        $data->$commandName = htmlspecialchars($response);

        $query = "UPDATE settingsApi SET data = ? WHERE id = ?";
        $rep = $db->prepare($query);
        $rep->execute(array(json_encode($data), $_SESSION['uid']));

        return 1;
    }

    return 2;
}

function removeCommand($commandName) {
    if($commandName != "") {
        $db = connect_db();
        $query = "SELECT data FROM settingsApi WHERE id = ?";
        $rep = $db->prepare($query);
        $rep->execute(array($_SESSION['uid']));
        $data = $rep->fetch(PDO::FETCH_ASSOC);

        $data = json_decode($data["data"]);

        unset($data->$commandName);

        $query = "UPDATE settingsApi SET data = ? WHERE id = ?";
        $rep = $db->prepare($query);
        $rep->execute(array(json_encode($data), $_SESSION['uid']));

        return 1;
    }

    return 2;
}

function getCommandsResponse() {
    $db = connect_db();
    $query = "SELECT data FROM settingsApi WHERE id = ?";
    $rep = $db->prepare($query);
    $rep->execute(array($_SESSION['uid']));
    $data = $rep->fetch(PDO::FETCH_ASSOC);

    return json_decode($data["data"]);
}

function getDateCommandNumber() {
    $db = connect_db();
    $query = "SELECT * FROM milestonesGlobal ORDER BY id DESC LIMIT 1";
    $rep = $db->prepare($query);
    $rep->execute();
    $data = $rep->fetch(PDO::FETCH_ASSOC);

    $now = strtotime("2016-02-25");
    $your_date = strtotime($data['dateAchieved']);
    $datediff = abs($now - $your_date);
    return array("days" => floor($datediff/(60*60*24)), "channel" => $data['channel'], "number" => $data['number']);
}

function quicksort($array, $search = "") {
    if( count( $array ) < 2 ) {
        return $array;
    }
    $left = $right = array( );
    reset( $array );
    $pivot_key  = key( $array );
    $pivot  = array_shift( $array );
    foreach( $array as $k => $v ) {
        if($search != "") {
            if( $v[$search] < $pivot[$search] )
                $left[$k] = $v;
            else
                $right[$k] = $v;
        }
        else {
            if( $v < $pivot )
                $left[$k] = $v;
            else
                $right[$k] = $v;
        }
    }
    return array_merge(quicksort($left, $search), array($pivot_key => $pivot), quicksort($right, $search));
}
