<?php
PageData::addScript(PageData::site_address('lib/jquery/imagesloaded.js'));
PageData::addScript(PageData::site_address('lib/jquery/masonry.js'));
PageData::addScript(PageData::site_address('lib/jquery/infinitescroll.js'));
PageData::addScript(PageData::site_address('my/js/default.js'));

PageData::addStyle(PageData::site_address('less/default.css'));

$page = TGlobal::get('page', 1);
if (!is_numeric($page) || $page < 1)
    $page = 1;
if (!defined('PHOTOS_PER_PAGE'))
    define ('PHOTOS_PER_PAGE', 20);
$start = PHOTOS_PER_PAGE * ($page - 1);
$count = trio\db\Model::$db->get_var('SELECT COUNT(*) FROM photos');
$query = 'SELECT * FROM photos ORDER BY added DESC LIMIT '.$start.','.PHOTOS_PER_PAGE;
$photos = PhotoModel::loadByQuery($query, false);
?>
<div id="photo-browser">
    <?php foreach ($photos as $foto): ?>
        <div class="photo" data-photo="<?php echo $foto->id ?>">
            <a href="<?php echo PageData::site_address('photo/'.$foto->path); ?>">
                <img src="<?php echo PageData::site_address('view/230xauto-' . $foto->path); ?>" />
            </a>
            
            <div class="btn-group photo-actions">
                <a class="btn vote love" href="#"><span class="icon-thumbs-up"></span></a>
                <a class="btn vote hate" href="#"><span class="icon-thumbs-down"></span></a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php if ($start + PHOTOS_PER_PAGE < $count):?>
<div id="next-photo-page">
<a href="<?php echo PageData::site_address('default?page='.($page+1)) ?>">
    <?php PageData::write('MORE IMAGES', 'More') ?>
</a>
</div>
<?php endif; ?>
