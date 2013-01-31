<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?php echo PageData::$title; ?></title>
        <meta name="description" content="<?php echo htmlentities(PageData::$description) ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="<?php echo PageData::site_address('lib/twitter/css/bootstrap.min.css'); ?>" />
        <link rel="stylesheet" type="text/css" href="<?php echo PageData::site_address('lib/twitter/css/bootstrap-responsive.min.css'); ?>" />
        <link rel="stylesheet" type="text/css" href="<?php echo PageData::site_address('less/global.css'); ?>" />
        <?php PageData::renderStyles() ?>
    </head>
    <body>
        <div class="container">
            <div class="span12" id="wrapper">
                <div id="main-bar" class="navbar">
                    <div class="navbar-inner">
                        <a class="brand" href="<?php echo PageData::site_address() ?>"><?php PageData::write('Trio Foto Network');?></a>
                        <!-- .btn-navbar is used as the toggle for collapsed navbar content -->
                        <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </a>
                        <div class="nav-collapse">
                            <ul class="nav">
                                <li>
                                    <a href="<?php echo PageData::lang_address('en');?>">Eng</a>
                                </li>
                                <li>
                                    <a href="<?php echo PageData::lang_address('ro');?>">Ro</a>
                                </li>
                            </ul>
                            <?php PageData::renderTemplate('loginfo.php'); ?>
                        </div>                    
                    </div>
                </div>
                <?php echo PageData::$content; ?>        
            </div>
        </div>

        <script>
            var SITE_ROOT = '<?php echo PageData::site_address(); ?>';
        </script>
        <script src="<?php echo PageData::site_address('lib/jquery.js'); ?>"></script>
        <script src="<?php echo PageData::site_address('lib/twitter/bootstrap.min.js'); ?>"></script>
        <?php PageData::renderScripts(); ?>
    </body>
</html>
