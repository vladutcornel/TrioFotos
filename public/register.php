<?php

class PageRegister {

    private $error = array(
        'email' => false,
        'password' => false,
        'confirm'=> false
    );
    
    private $success = false;

    public function post_request() {
        try {
            $email = TGlobal::post('email');
            if (!preg_match('/[a-z0-9\-_.]+@[a-z0-9\-_.]+/i', $email)) {
                Throw new UserInputException(PageData::translate('Invalid Email'), 'email');
            }
            $pass = TGlobal::post('password');
            if (trim($pass) == '')
                throw new UserInputException(PageData::translate('Invalid Password'), 'password');
            if (!TGlobal::post('confirm')){
                throw new UserInputException(PageData::translate('Register Confirm Error', 'Please don\'t upload porn. '), 'confirm');
            }
            
            $old = UserModel::search(array('email'=>$email));
            if (count($old) > 0){
                throw new UserInputException(PageData::translate('Email used', 'You already have an account for this e-mail Address'), 'email');
            }
            
            UserModel::create(array(
                'email'=>$email,
                'password'=>md5($pass)
            ))->save();
            $this->success = true;
        } catch (UserInputException $e) {
            $this->error[$e->getField()] = $e->getMessage();
        }
    }

    public function main() {
        if ($this->success){
            ?>
<div class="alert alert-success"><?php PageData::write('REGISTER_SUCCESS', 'Your account is registered. You may now login'); ?></div>                
                <?php
            return;
        }
        
        if ($this->error['confirm']){
            ?>
<div class="alert alert-error"><?php echo $this->error['confirm']; ?></div>
                <?php
        }
        ?>
<form class="form-horizontal" method="post" action="<?php echo PageData::site_address('register'); ?>">
            <div class="control-group<?php if ($this->error['email']) echo ' error' ?>">
                <label class="control-label" for="inputEmail"><?php PageData::write('EMail'); ?></label>
                <div class="controls">
                    <input type="text" name="email" id="inputEmail" placeholder="<?php PageData::write('EMail'); ?>">
                    <?php if ($this->error): ?>
                        <span class="help-inline"><?php echo $this->error['email']; ?></span>
        <?php endif; ?>
                </div>
            </div>
            <div class="control-group<?php if ($this->error['password']) echo ' error' ?>">
                <label class="control-label" for="inputPassword"><?php PageData::write('Password'); ?></label>
                <div class="controls">
                    <input type="password" name="password" id="inputPassword" placeholder="<?php PageData::write('Password'); ?>">
                    <?php if ($this->error): ?>
                        <span class="help-inline"><?php echo $this->error['password']; ?></span>
        <?php endif; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <label class="checkbox">
                        <input type="checkbox" name="confirm" />
                        <?php PageData::write('REGISTER_CONFIRM', 'I will not upload porn'); ?>
                    </label>
                    <button type="submit" class="btn">
        <?php PageData::write('Sign Up'); ?>
                    </button>
                </div>
            </div>
        </form><?php
    }

}