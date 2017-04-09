<?php

require 'vendor/autoload.php';

$client = new \GuzzleHttp\Client();

// 1. 获取组织信息

/**
 * 获取组织信息
 */
//$organizations = $client->request('GET','https://api.github.com/orgs/alibaba');
//$data = json_decode($organizations->getBody());

/**
 * 使用 $org 存储需要保存的信息。
 */
//$org['name'] = $data->name;
//$org['description'] = $data->description;
//$org['blog'] = $data->blog;
//$org['public_repo_count'] = $data->public_repos;

// 2. 获取repos信息
$pages = 1;
$repoArr = [];

for ($i = 1; $i <= $pages; $i++) {

    $repos = $client->request('GET', 'https://api.github.com/users/alibaba/repos?page=' . $i);
    $repoData = json_decode($repos->getBody());
    $repoArr = array_merge_recursive($repoArr, $repoData);
}

$data = [];
foreach ($repoArr as $one) {
    $repoInfo = $client->request('GET','https://api.github.com/repos/'.$one->full_name);
    $repoInfoObj = json_decode($repoInfo->getBody());
    $data [] = [
        "name" => $one->name,
        "fullname" => $one->full_name,
        "is_private" => $one->private?"Yes":"No",
        "created time" => $one->created_at,
        "pushed time" => $one->pushed_at,
        "updated time" => $one->updated_at,
        "language" => $one->language,
        "star" => $repoInfoObj->stargazers_count,
        "watcher" => $repoInfoObj->watchers_count,
        "default_branch" => $one->default_branch,
        "subscribers_count" => $repoInfoObj->subscribers_count,
        "Forks" => $one->forks_count,
        "Open Issue" => $one->open_issues
     ];
}


// 3. 以 CSV 形式输出

$csvObj = new mnshankar\CSV\CSV();
return $csvObj->fromArray($data)->render('download.csv');