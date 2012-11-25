<?php

/**
 * TrioFramework test zone
 *
 * @author cornel
 */
class index {
    public function __construct() {
        ;
    }
    
    public function main(){
        $input = new HtmlRadio('field[alfa]', 'default');
        $input2 = new HtmlRadio('field[alfa]', 'default2');
        
        ?>
<form method='post'>
    <?php $input->toHtml(TRUE); ?>
    <?php $input2->toHtml(TRUE); ?>
    <input type="submit" />
</form>
<a href="?field=get">link</a>
<?php
        var_dump(TGlobal::request('field'));
    }
}
