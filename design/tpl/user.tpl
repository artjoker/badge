<form action="<?php echo URL_ROOT ?>admin/users/<?php echo (int)$user['user_id']?>" method="post">
  <div class="row">
    <div class="col-md-12">
      <p>
        <a href="<?php echo URL_ROOT ?>admin/users" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> <?php echo $app->lang->get('Back')?></a>
      </p>
    </div>
  </div>
  <div class="row">
    <div class="col-md-6">
      <div class="form-group">
        <label><?php echo $app->lang->get('Name')?></label>
        <input type="text" name="user[name]" value="<?php echo $user['user_name']?>" class="form-control">
      </div>
      <div class="form-group">
        <div class="input-group dt">
          <input type="text" name="user[dob]" placeholder="<?php echo $app->lang->get('Birth date')?>" value="<?php echo date(" d.m.Y", strtotime($user['user_dob']))?>" class="form-control dt">
          <span class="input-group-addon">
            <span class="glyphicon glyphicon-calendar"></span>
          </span>
        </div>
      </div>
      <div class="form-group">
        <label><?php echo $app->lang->get('Email')?></label>
        <input type="text" name="user[email]" value="<?php echo $user['user_email']?>" class="form-control">
      </div>
      <div class="form-group">
        <label><?php echo $app->lang->get('Active')?></label>
        <br>
        <input type="checkbox" name="user[active]" value="yes" <?php echo $user['user_active'] == 1 ? "checked" : ""?> class="make-switch">
      </div>
    </div>
    <div class="col-md-6">
      <div class="col-md-6">
        <div class="form-group">
          <label><?php echo $app->lang->get('Password')?></label>
          <input type="password" name="user[pass]" value="" class="form-control">
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label><?php echo $app->lang->get('Password confirm')?></label>
          <input type="password" name="user[cfm]" value="" class="form-control">
        </div>
      </div>
    </div>
  </div>

  <button type="submit" class="btn btn-lg btn-primary"><span class="glyphicon glyphicon-save"></span>
    <b><?php echo $app->lang->get('Save')?></b></button>
</form>