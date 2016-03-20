<p>

  <a href="<?php echo URL_ROOT ?>admin/manager/add" class="btn btn-success"><span class="glyphicon glyphicon-plus-sign"></span>
    <b><?php echo $app->lang->get('Add new manager')?></b>
  </a>
</p>
<table class="table table-bordered table-responsive table-condensed table-striped table-hover">
  <thead>
  <tr>
    <th width="35px"><?php echo $app->lang->get('ID')?></th>
    <th><?php echo $app->lang->get('Name')?></th>
    <th><?php echo $app->lang->get('Email')?></th>
    <th width="90px"><?php echo $app->lang->get('Active')?></th>
    <th width="50px"></th>
  </tr>
  </thead>
  <tbody>
  <?php foreach ($managers as $user): ?>
  <tr>
    <td>
      <small class="text-muted"><?php echo $user['manager_id']?></small>
    </td>
    <td><?php echo $user['manager_name']?></td>
    <td><?php echo $user['manager_email']?></td>
    <td class="text-center">
      <?php if ($user['manager_active'] == 1): ?>
      <span class="label label-success"><?php echo $app->lang->get('Yes')?></span>
      <?php else: ?>
      <span class="label label-danger"><?php echo $app->lang->get('No')?></span>
      <?php endif ?>
    </td>
    <td class="text-center">
      <a href="<?php echo URL_ROOT ?>admin/manager/<?php echo $user['manager_id']?>"
         class="btn btn-sm btn-primary" title="<?php echo $app->lang->get('Edit')?>"><span
                class="glyphicon glyphicon-pencil"></span></a>
    </td>
  </tr>
  <?php endforeach ?>
  </tbody>
</table>