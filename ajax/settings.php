<?php
require_once '../apiFunctions/init.php';

switch($_POST['action']) {
    case 'updateSummonerName':
        echo updateSummonerName($_POST['summonerName'], $_POST['region'], $_POST['summonerId'], $_POST['season'], $_POST['lang']);
        break;

    case "fetchMessages" :
        fetchMessages($_POST['id']);
        break;

    case "deleteComment" :
        deleteComment($_POST['id']);
        break;

    case "addComment" :
        echo addComment($_POST['id'], $_POST['message']);
        break;

    case "deletePost" :
        deletePost($_POST['id']);
        break;

    case "lockPost" :
        echo lockPost($_POST['id'], $_POST['state']);
        break;

    case "updatePost" :
        echo updatePost($_POST['id'], $_POST['title'], $_POST['message']);
        break;

    case "updateComment" :
        echo updateComment($_POST['id'], $_POST['message']);
        break;

    case "addPatch" :
        echo addPatch($_POST['title'], $_POST['version'], $_POST['patchNotes']);
        break;

    case "editPatch" :
        echo editPatch($_POST['id'], $_POST['title'], $_POST['version'], $_POST['patchNotes']);
        break;

    case "deletePatch" :
        echo deletePatch($_POST['id']);
        break;

    case "refreshHistory" :
        commandsHistory();
        break;

    case "refreshAutomaticNightbot" :
        echo getAllFromTable("automaticNightbot")[0]['countUsed'];
        break;

    case "refreshForum" :
        echo lastForumCheck($_SESSION['uid']);
        break;

    case "unlinkNightbot" :
        echo unlinkNightbot();
        break;

    case "checkNightbotLink" :
        echo json_encode(grabCustomCommands());
        break;

    case "addNightbotCommand" :
        echo addNightbotCommand($_POST);
        break;

    case "editNightbotCommand" :
        echo editNightbotCommand($_POST);
        break;

    case "saveCommand" :
        echo saveCommand($_POST['commandName'], $_POST['response']);
        break;

    case "removeCommand" :
        echo removeCommand($_POST['commandName']);
        break;
}