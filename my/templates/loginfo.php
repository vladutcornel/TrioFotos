
<?php if (0 == PageData::getUser()->active): ?>

    <form method="post" action="<?php echo PageData::site_address('login'); ?>" class="navbar-form pull-right">
        <input type="text" name="email" placeholder="<?php PageData::write('E-Mail'); ?>" class="span2">
        <input type="password" name="password" placeholder="<?php PageData::write('Password'); ?>" class="span2">
        <button type="submit" class="btn"><?php PageData::write('Login'); ?></button>
        <a class="btn" href="<?php echo PageData::site_address('register') ?>"><?php PageData::write('Register'); ?></a>
    </form>
<?php else: ?>
    <ul class="nav pull-right">
        <li><a href="<?php echo PageData::site_address('home') ?>"><?php echo PageData::getUser()->email ?></a></li>
        <li><a href="<?php echo PageData::site_address('default') ?>"><?php echo PageData::write('Browse') ?></a></li>
        <li><a href="<?php echo PageData::site_address('home') ?>"><?php echo PageData::write('Upload') ?></a></li>
        <li><a href="<?php echo PageData::site_address('login/out') ?>"><?php PageData::write('Logout'); ?></a></li>
    </ul>
<?php endif; ?>
