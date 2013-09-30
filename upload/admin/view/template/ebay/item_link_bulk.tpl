<?php echo $header; ?>

<div id="content">

    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>

    <div class="box mBottom130">

        <div class="heading">
            <h1><?php echo $lang_heading; ?></h1>
            <div class="buttons">
                <a href="<?php echo $return; ?>" class="button"><span><?php echo $lang_btn_return; ?></span></a>
            </div>
        </div>

        <div class="content">

            <?php if($validation == true) { ?>

            <?php }else{ ?>
                <div class="warning"><?php echo $lang_error_validation; ?></div>
            <?php } ?>

        </div>
    </div>
</div>

<?php echo $footer; ?>