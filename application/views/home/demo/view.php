<title>查询页</title>

<?php if(isset($error)){ ?> <h2><?php echo $error; ?></h2>
    <a class="click" href="/home/demo/viewAll">
        <span>Go Home</span>
    </a>
<?php }else{ ?>

<h2><?php echo $item[0]['item_name']; ?></h2>
    <a class="click" href="/home/demo/delete/id/<?php echo $item[0]['id']?>/item/123/AAA/111">
        <span>Delete this item</span>
    </a>
<?php } ?>