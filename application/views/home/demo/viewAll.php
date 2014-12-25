<title>列表页</title>
<link href="<?php echo __CSS__ ?>page.css" type="text/css" rel="stylesheet" />
<form action="/home/demo/add" method="post">
    <input type="text" value="add it..." onclick="this.value=''" name="name">
    <input type="text" value="input code..." onclick="this.value=''" name="code">
    <img src="/home/demo/code" onclick="javascript:this.src='/home/demo/code'" />
    <input type="submit" value="add">
</form>
<br/><br/>
<?php foreach ($item as $key=>$todoitem){?>
    <a class="click" href="/home/demo/view/id/<?php echo $todoitem['id']?>">
        <span>
            <?php echo ($key+1) ?>
            <?php echo $todoitem['item_name']?>
        </span>
    </a><br/>
<?php } ?>
<?php echo $page; ?>