<form action="<?php echo URL_ROOT ?>admin/time" method="get">
  <div class="row">
    <div class="col-md-2 col-xs-6">
      <div class="form-group">
        <select name="m" data-active="<?php echo (int)$month ?>" class="form-control">
          <option value="" selected disabled>Месяц</option>
          <?php for($m = 1; $m <= 12; $m++): ?>
          <option value="<?php echo $m ?>"><?php echo date("F", mktime(0,0,0,$m,1,2016)) ?></option>
          <?php endfor ?>
        </select>
      </div>
    </div>
    <div class="col-md-2 col-xs-6">
      <div class="form-group">
        <select name="y" data-active="<?php echo $year?>" class="form-control">
          <option value="" selected disabled>Год</option>
          <?php for($y = 2016; $y <= date("Y"); $y++): ?>
            <option value="<?php echo $y ?>"><?php echo $y ?></option>
          <?php endfor ?>
        </select>
      </div>
    </div>
    <div class="col-md-2">
      <div class="form-group">
        <button type="submit" class="btn btn-info"><span class="glyphicon glyphicon-filter"></span> <b><?php echo $app->lang->get('Apply filter')?></b></button>
      </div>
    </div>
  </div>
</form>
<table class="table table-bordered table-responsive table-condensed table-striped table-hover">
  <thead>
  <tr>
    <th><?php echo $app->lang->get('Name')?></th>
    <?php for($i = 1; $i <= $days; $i++):?>
      <th><?php echo $i?></th>
    <?php endfor ?>
    <th>H</th>
    <th>&sum;</th>
  </tr>
  </thead>
  <tbody>
  <?php foreach ($users as $user): ?>
  <tr>
    <td><?php echo $user['user_name']?></td>
    <?php
      $c = 0;
      $t = 0;
      for($i = 1; $i <= $days; $i++):
        $date = $year.'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-'.str_pad($i,2,'0',STR_PAD_LEFT);
        $value = $time[$user['user_id']][$date];
        $empty = isset($value);
        if (!$empty) {
          $c++;
        } else {
          $t += ($value['end'] - $value['start']);
        }
    ?>
      <th class="text-center <?php echo ($empty ? "text-success success" : "text-danger danger") ?>" title="Пришел: <?php echo $value['start']."\n".'Ушел: '.$value['end']?>">
      <?php if ($empty): ?>
        <small><?php echo $value['end'] - $value['start']?></small>
      <?php else: ?>
        H
      <?php endif ?>
      </th>
    <?php endfor ?>
    <td class="text-right"><small><?php echo $c ?></small></td>
    <td class="text-right"><small><?php echo $t ?></small></td>
  </tr>
  <?php endforeach ?>
  </tbody>
</table>