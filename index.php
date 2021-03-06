<?php

require 'vendor/autoload.php';

$client = new \GuzzleHttp\Client();

define("GROUP","lctt");
define("PAGESIZE",1000);
define("USERNAME",'');
define("PASSWD",'');
/**
 * 获取组织信息
 */

$organizations = $client->request('GET','https://api.github.com/orgs/'.GROUP,array('auth' => array(USERNAME, PASSWD, 'Basic')));
$orgObj = json_decode($organizations->getBody());
/**
 * 公开 Repo 数目
 */
$org['public_repo_count'] = $orgObj->public_repos;

/**
 * 获取所有Repo
 */
$repos = $client->request('GET', 'https://api.github.com/users/'.GROUP.'/repos?per_page=1000',array('auth' => array(USERNAME, PASSWD, 'Basic')));
$repoArr = json_decode($repos->getBody());

$data = [];
/**
 * Repo 循环处理
 */
foreach ($repoArr as $one) {
    /**
     * 获取 Repo 基本信息
     */
    $repoInfo = $client->request('GET','https://api.github.com/repos/'.$one->full_name,array('auth' => array(USERNAME, PASSWD, 'Basic')));
    $repoInfoObj = json_decode($repoInfo->getBody());
    try {
        $licenseObj = $client->get('https://api.github.com/repos/'.$one->full_name.'/license',array(),array('auth' => array(USERNAME, PASSWD, 'Basic')));
        $license = $licenseObj->license->name;
    } catch (\GuzzleHttp\Exception\ClientException $e) {
        $license ="No License";
    }

    /**
     * 借助API 获取单项的信息
     */
    $branchObj = json_decode($client->get('https://api.github.com/repos/'.$one->full_name.'/branches?per_page='.PAGESIZE,array(),array('auth' => array(USERNAME, PASSWD, 'Basic')))->getBody());
    $commitObj = json_decode($client->get('api.github.com/repos/'.$one->full_name.'/commits?per_page='.PAGESIZE,array(),array('auth' => array(USERNAME, PASSWD, 'Basic')))->getBody());
    $releaseObj = json_decode($client->get('api.github.com/repos/'.$one->full_name.'/releases?per_page='.PAGESIZE,array(),array('auth' => array(USERNAME, PASSWD, 'Basic')))->getBody());

    $collaboratorsObj = json_decode($client->get('api.github.com/repos/'.$one->full_name.'/collaborators?per_page='.PAGESIZE,array(),array('auth' => array(USERNAME, PASSWD, 'Basic')))->getBody());
    $outSideCollaborators = json_decode($client->get('api.github.com/repos/'.$one->full_name.'/collaborators?affiliation=outside&per_page='.PAGESIZE,array(),array('auth' => array(USERNAME, PASSWD, 'Basic')))->getBody());
    $directCollaborators = json_decode($client->get('api.github.com/repos/'.$one->full_name.'/collaborators?affiliation=outside&per_page='.PAGESIZE,array(),array('auth' => array(USERNAME, PASSWD, 'Basic')))->getBody());

    $openPR = json_decode($client->get('api.github.com/repos/'.$one->full_name.'/pulls?state=open&per_page='.PAGESIZE,array(),array('auth' => array(USERNAME, PASSWD, 'Basic')))->getBody());
    $allPR = json_decode($client->get('api.github.com/repos/'.$one->full_name.'/pulls?state=all&per_page='.PAGESIZE,array(),array('auth' => array(USERNAME, PASSWD, 'Basic')))->getBody());
    $closedPR = json_decode($client->get('api.github.com/repos/'.$one->full_name.'/pulls?state=closed&per_page='.PAGESIZE,array(),array('auth' => array(USERNAME, PASSWD, 'Basic')))->getBody());

    $allIssue = json_decode($client->get('api.github.com/repos/'.$one->full_name.'/issues?state=all&per_page='.PAGESIZE,array(),array('auth' => array(USERNAME, PASSWD, 'Basic')))->getBody());
    $closedIssue = json_decode($client->get('api.github.com/repos/'.$one->full_name.'/issues?state=closed&per_page='.PAGESIZE,array(),array('auth' => array(USERNAME, PASSWD, 'Basic')))->getBody());

    $downloadFile = json_decode($client->get('api.github.com/repos/'.$one->full_name.'/downloads?per_page='.PAGESIZE,array(),array('auth' => array(USERNAME, PASSWD, 'Basic')))->getBody());

    $data [] = [
        "name" => $one->name,
        "fullname" => $one->full_name,
        "is_private" => $one->private?"Yes":"No",

        "language" => $one->language,
        "star" => $repoInfoObj->stargazers_count,
        "watcher" => $repoInfoObj->watchers_count,
        "subscribe" => $repoInfoObj->subscribers_count,

        "Forks" => $one->forks_count,
        "Commits" => count($commitObj),
        "Branches" => count($branchObj),
        "Release" => count($releaseObj),
        "License" => $license?$license:"",
        "Download" => count($downloadFile),

        "All Collaborators" => count($collaboratorsObj),
        "OutSide Collaborators" => count($outSideCollaborators),
        "Direct Collaborators"  => count($directCollaborators),

        "AllPR" => count($allPR),
        "OpenPR" => count($openPR),
        "ClosedPR" => count($closedPR),

        "Open Issue" => $one->open_issues,
        "Closed Issue" => count($closedIssue),
        "All Issue" => count($allIssue),

        "created time" => $one->created_at,
        "pushed time" => $one->pushed_at,
        "updated time" => $one->updated_at,
     ];
}


// 3. 以 CSV 形式输出
//
$csvObj = new mnshankar\CSV\CSV();
return $csvObj->fromArray($data)->render(GROUP.'.csv');
