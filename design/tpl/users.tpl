<div class="row">
  <div class="col-md-9">
    <form action="<?php echo URL_ROOT ?>admin/users" method="get">
      <div class="row">
        <div class="col-md-4 col-xs-6">
          <div class="form-group">
            <input type="text" name="search" placeholder="<?php echo $app->lang->get('Find by Name/Phone/Email')?>"
                    value="<?php echo $app->request->get('search')?>" class="form-control">
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <button type="submit" class="btn btn-info"><span class="glyphicon glyphicon-filter"></span> <b><?php echo $app->
                lang->get('Apply filter')?></b></button>
          </div>
        </div>
      </div>
    </form>
  </div>
  <div class="col-md-3 text-right">
    <a href="/admin/users/0" class="btn btn-success"><span class="glyphicon glyphicon-plus-sign"></span> <b>Добавить пользователя</b></a>
  </div>
</div>
<table class="table table-bordered table-responsive table-condensed table-striped table-hover">
  <thead>
  <tr>
    <th width="35px"><?php echo $app->lang->get('ID')?></th>
    <th><?php echo $app->lang->get('Name')?></th>
    <th><?php echo $app->lang->get('Email')?></th>
    <th width="90px"><?php echo $app->lang->get('Active')?></th>
    <th width="100px"></th>
  </tr>
  </thead>
  <tbody>
  <?php foreach ($users as $user): ?>
  <tr>
    <td>
      <small class="text-muted"><?php echo $user['user_id']?></small>
    </td>
    <td><?php echo $user['user_name']?></td>
    <td><?php echo $user['user_email']?></td>
    <td class="text-center">
      <?php if ($user['user_active'] == 1): ?>
      <span class="label label-success"><?php echo $app->lang->get('Yes')?></span>
      <?php else: ?>
      <span class="label label-danger"><?php echo $app->lang->get('No')?></span>
      <?php endif ?>
    </td>
    <td class="text-center">
      <a href="<?php echo URL_ROOT ?>admin/users/<?php echo $user['user_id']?>"
              class="btn btn-sm btn-primary" title="<?php echo $app->lang->get('Edit')?>"><span
                class="glyphicon glyphicon-pencil"></span></a>
      <?php if ($user['orders_count'] > 0): ?>
      <a href="<?php echo URL_ROOT ?>admin/orders?user=<?php echo $user['user_id']?>" class="btn btn-sm btn-info"
              title="<?php echo $app->lang->get('Browse user orders')?>"><span
                class="glyphicon glyphicon-shopping-cart"></span> <?php echo $user['orders_count']?></a>
      <?php endif ?>
    </td>
  </tr>
  <?php endforeach ?>
  </tbody>
</table>

<nav>
  <ul class="pagination">
    <?php for($i = 0; $i < $pages; $i++): ?>
    <li class="<?php echo $i * LIMIT == $page ? "active" : ""?>">
    <a href="<?php echo URL_ROOT ?>admin/orders?p=<?php echo $i * LIMIT?>&<?php echo $params?>">
      <?php echo $i + 1 ?>
    </a>
    </li>
    <?php endfor ?>
  </ul>
</nav>