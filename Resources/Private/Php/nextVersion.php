#!/usr/bin/env php
<?php

require('.Build/vendor/autoload.php');

use Symfony\Component\Process\Process;

const INCREASE_PATCH = 'patch';
const INCREASE_MAJOR = 'major';
const INCREASE_MINOR = 'minor';

$options = getopt('t::', ['version-type::']);
$versionType = $options['t'] ?? $options['version-type'] ?? 'patch';

$process = new Process(['git', 'tag', '-l', '--sort=v:refname']);
$process->run();

$tags = array_filter(explode("\n", $process->getOutput()));
$lastTag = array_pop($tags);
$lastTagParts = explode('.', $lastTag);
if (count($lastTagParts) !== 3) {
    throw new \RuntimeException('Last tag "' . $lastTag . '" has less then three parts!');
}

[$major, $minor, $patch] = array_map(function ($val) {
    return (int)$val;
}, explode('.', $lastTag));

switch ($versionType) {
    case INCREASE_MINOR:
        $patch = 0;
        $minor++;
        break;

    case INCREASE_MAJOR:
        $patch = 0;
        $minor = 0;
        $major++;
        break;

    case INCREASE_PATCH:
    default:
        $patch++;
}

$nextTag = implode('.', [$major, $minor, $patch]);

$emConfFile =  './ext_emconf.php';
$newEmConf = preg_replace(
    "/'version' => '(\d+\.\d+\.\d+)'/",
    "'version' => '$nextTag'",
    file_get_contents($emConfFile)
);
file_put_contents($emConfFile, $newEmConf);

$docsSettingsFile =  './Documentation/Settings.cfg';
$newDocsSettings = preg_replace(
    [
        "/version     = (\d+\.\d+)/",
        "/release     = (\d+\.\d+\.\d+)/"
    ],
    [
        "version     = $major.$minor",
        "release     = $major.$minor.$patch"
    ],
    file_get_contents($docsSettingsFile)
);
file_put_contents($docsSettingsFile, $newDocsSettings);

echo "git commit -m 'Tag new version' && git tag -a '$nextTag' -m '$nextTag' && git push origin master --tags \n";
