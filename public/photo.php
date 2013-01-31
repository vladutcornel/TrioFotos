<?php
$params = TOCore::$params;
if (!isset($params[0]))
    PageData::redirect('');
$photo = PhotoModel::load($params[0]);
if (!file_exists(SCRIPT_ROOT . '/uploads/' . $photo->path))
    PageData::redirect('404');
PageData::addScript(PageData::site_address('my/js/photo.js'));
PageData::addStyle(PageData::site_address('less/photo.css'));
?>
<div class="row">
    <div class="span9">
        <img id="main-image" src="<?php echo PageData::site_address('view/700xauto-' . $photo->path); ?>" />
    </div>
    <div class="span3">
        <section id="similars">
            <?php
            $sql = sprintf('SELECT photo2 FROM similar_photos AS s 
    INNER JOIN photos AS p  ON p.id = s.photo2
    WHERE photo1 = %d 
    ORDER BY s.score DESC, p.added DESC LIMIT 5', $photo->id);
            $ids = trio\db\Model::$db->get_col($sql);
            $similar = PhotoModel::prepare($ids);
            foreach ($similar as $foto):
                ?><div class="photo" data-photo="<?php echo $foto->id ?>">
                    <a href="<?php echo PageData::site_address('photo/' . $foto->path); ?>">
                        <img src="<?php echo PageData::site_address('view/220xauto-' . $foto->path); ?>" />
                    </a>

                    <div class="btn-group photo-actions">
                        <a class="btn vote love" href="#"><span class="icon-thumbs-up"></span></a>
                        <a class="btn vote hate" href="#"><span class="icon-thumbs-down"></span></a>
                    </div>
                </div>
                <?php
            endforeach;
            ?>
        </section>
    </div>
</div>