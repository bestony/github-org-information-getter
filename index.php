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
$pages = 5;
$repoArr = [];

for ($i = 0;$i<$pages;$i++){

    $repos = $client->request('GET','https://api.github.com/users/alibaba/repos?page='.$i);
    $repoData = json_decode($repos->getBody());
    $repoArr = array_merge_recursive($repoArr,$repoData);
}

dd($repoArr);

