<?php
$extensionKey = getenv('EXTENSION_KEY');
$typo3AdminUser = getenv('TYPO3_INSTALL_ADMIN_USER');
$typo3AdminPassword = getenv('TYPO3_INSTALL_ADMIN_PASSWORD');
$supportedVersions = explode(' ', getenv('TYPO3_VERSIONS'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo($extensionKey); ?></title>
</head>
<body>
<p>Run <b>'ddev install all'</b> to install all testing instances below.</p>
<ul>
    <?php
    foreach ($supportedVersions as $version) {
        $directoryPath = "/var/www/html/.test/" . $version;
        if (is_dir($directoryPath)) {
            echo "<li><a target='_blank' href='https://{$version}.{$extensionKey}.ddev.site/typo3/'>https://{$version}.{$extensionKey}.ddev.site/typo3</a> user: <b>{$typo3AdminUser}</b>, pass: <b>{$typo3AdminPassword}</b></li>";
        } else {
            echo "<li>Version {$version} is not installed. Run <b>'ddev install {$version}'</b> or <b>'ddev ci {$version}'</b> to install.</li>";
        }
    }
    ?>
</ul>
</body>
</html>
